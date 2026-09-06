import argparse
import json
import os
import platform
import shutil
import socket
import subprocess
import sys
import time
import uuid
from pathlib import Path

import psutil
import requests


ROOT = Path(__file__).resolve().parent
CONFIG_PATH = ROOT / "config.json"
UUID_PATH = ROOT / "worker-id.txt"


def load_config():
    if not CONFIG_PATH.exists():
        raise RuntimeError("config.json not found. Run setup-worker.cmd first.")

    return json.loads(CONFIG_PATH.read_text(encoding="utf-8"))


CONFIG = load_config()

BASE_URL = CONFIG["studio_url"].rstrip("/")
TOKEN = CONFIG["worker_token"]
POLL_SECONDS = max(5, int(CONFIG.get("poll_seconds", 10)))

WORKSPACE = ROOT / CONFIG.get("workspace", "workspace")
WORKSPACE.mkdir(parents=True, exist_ok=True)


def worker_uuid():
    if UUID_PATH.exists():
        value = UUID_PATH.read_text(encoding="utf-8").strip()
        if value:
            return value

    value = str(uuid.uuid4())
    UUID_PATH.write_text(value, encoding="utf-8")
    return value


WORKER_UUID = worker_uuid()


def headers():
    return {
        "Authorization": f"Bearer {TOKEN}",
        "Accept": "application/json",
        "Content-Type": "application/json",
        "User-Agent": "C-Net-AI-Worker/4.0",
    }


def api_post(path, payload, timeout=60):
    response = requests.post(
        BASE_URL + path,
        headers=headers(),
        json=payload,
        timeout=timeout,
    )

    response.raise_for_status()

    return response.json()


def command_output(command):
    try:
        return subprocess.check_output(
            command,
            stderr=subprocess.STDOUT,
            text=True,
            timeout=20,
        ).strip()
    except Exception:
        return ""


def detect_gpu():
    if platform.system().lower() == "windows":
        result = command_output([
            "powershell",
            "-NoProfile",
            "-Command",
            "(Get-CimInstance Win32_VideoController | "
            "Select-Object -ExpandProperty Name) -join '; '"
        ])

        return result or "Unknown GPU"

    return command_output(["sh", "-c", "lspci | grep -Ei 'vga|3d'"]) or "Unknown GPU"


def ffmpeg_version():
    executable = shutil.which("ffmpeg")

    if not executable:
        return ""

    first = command_output([executable, "-version"]).splitlines()

    return first[0] if first else ""


def system_capabilities():
    ram = psutil.virtual_memory()
    disk = psutil.disk_usage(str(WORKSPACE))

    cpu = platform.processor() or platform.machine()

    return {
        "worker_uuid": WORKER_UUID,
        "name": CONFIG.get("worker_name", "C-Net AI Worker"),
        "hostname": socket.gethostname(),
        "platform": f"{platform.system()} {platform.release()}",
        "cpu": cpu,
        "gpu": detect_gpu(),
        "ram_mb": int(ram.total / 1024 / 1024),
        "disk_free_mb": int(disk.free / 1024 / 1024),
        "ffmpeg_version": ffmpeg_version(),
        "capabilities": {
            "ffmpeg": bool(shutil.which("ffmpeg")),
            "video_render": bool(shutil.which("ffmpeg")),
            "image_processing": True,
            "audio_processing": bool(shutil.which("ffmpeg")),
            "text_to_video": True,
            "image_to_video": True,
            "business_ads": True,
            "reels": True
        }
    }


def register():
    result = api_post(
        "/api/v1/workers/register",
        system_capabilities()
    )

    print(
        f"[REGISTERED] worker={result.get('worker_uuid')} "
        f"id={result.get('worker_id')}"
    )


def heartbeat():
    disk = psutil.disk_usage(str(WORKSPACE))

    api_post(
        "/api/v1/workers/heartbeat",
        {
            "worker_uuid": WORKER_UUID,
            "disk_free_mb": int(disk.free / 1024 / 1024),
        },
        timeout=30
    )


def report_progress(job_id, progress, message=""):
    api_post(
        f"/api/v1/workers/jobs/{job_id}/progress",
        {
            "worker_uuid": WORKER_UUID,
            "progress": int(progress),
            "message": message,
        },
        timeout=30
    )


def complete_job(job_id, result=None, output_path=None):
    api_post(
        f"/api/v1/workers/jobs/{job_id}/complete",
        {
            "worker_uuid": WORKER_UUID,
            "result": result or {},
            "output_path": output_path,
        }
    )


def fail_job(job_id, error):
    try:
        api_post(
            f"/api/v1/workers/jobs/{job_id}/fail",
            {
                "worker_uuid": WORKER_UUID,
                "error": str(error)[:5000],
            }
        )
    except Exception as secondary:
        print(f"[FAIL-REPORT-ERROR] {secondary}")


def ffmpeg_demo_render(job):
    ffmpeg = shutil.which("ffmpeg")

    if not ffmpeg:
        raise RuntimeError(
            "FFmpeg is not installed or not available in PATH."
        )

    payload = job.get("payload") or {}

    duration = int(payload.get("duration") or 10)
    duration = max(5, min(duration, 300))

    project_name = payload.get("project_name") or "C-Net AI Studio"

    job_dir = WORKSPACE / str(job["uuid"])
    job_dir.mkdir(parents=True, exist_ok=True)

    output = job_dir / "output.mp4"

    report_progress(job["id"], 10, "Preparing render")

    command = [
        ffmpeg,
        "-y",
        "-f", "lavfi",
        "-i", f"color=c=0x111827:s=1280x720:d={duration}",
        "-f", "lavfi",
        "-i", f"anullsrc=r=44100:cl=stereo",
        "-shortest",
        "-vf",
        (
            "drawtext="
            "text='C-Net AI Studio':"
            "fontcolor=white:"
            "fontsize=54:"
            "x=(w-text_w)/2:"
            "y=(h-text_h)/2-40,"
            "drawtext="
            f"text='{project_name[:60]}':"
            "fontcolor=white:"
            "fontsize=28:"
            "x=(w-text_w)/2:"
            "y=(h-text_h)/2+40"
        ),
        "-c:v", "libx264",
        "-pix_fmt", "yuv420p",
        "-c:a", "aac",
        "-movflags", "+faststart",
        str(output),
    ]

    report_progress(job["id"], 25, "FFmpeg rendering started")

    subprocess.run(
        command,
        check=True,
        stdout=subprocess.DEVNULL,
        stderr=subprocess.PIPE,
        text=True,
    )

    report_progress(job["id"], 90, "Render completed")

    return {
        "engine": "ffmpeg",
        "output_file": str(output),
        "size_bytes": output.stat().st_size,
    }, str(output)


def process_job(job):
    job_type = job.get("type")
    engine = job.get("engine")

    print(
        f"[JOB] id={job.get('id')} "
        f"type={job_type} engine={engine}"
    )

    #
    # Batch 4 foundation:
    # all video-family jobs currently pass through a safe FFmpeg render.
    #
    # Future batches will replace/extend this with:
    # local LLM
    # Stable Diffusion / Flux
    # image animation
    # Whisper
    # Piper/Coqui TTS
    # background removal
    # upscaling
    # scene generation
    #

    if job_type in {
        "video",
        "text-to-video",
        "image-to-video",
        "business-ad",
        "reel",
    }:
        return ffmpeg_demo_render(job)

    raise RuntimeError(
        f"Unsupported worker job type: {job_type}"
    )


def request_next_job():
    result = api_post(
        "/api/v1/workers/next-job",
        {
            "worker_uuid": WORKER_UUID
        },
        timeout=60
    )

    return result.get("job")


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "--once",
        action="store_true",
        help="Register, heartbeat and check one job, then exit."
    )
    args = parser.parse_args()

    print("======================================================")
    print(" C-Net AI Studio Dedicated Worker V4")
    print("======================================================")
    print(f"Studio : {BASE_URL}")
    print(f"Worker : {WORKER_UUID}")
    print(f"Host   : {socket.gethostname()}")
    print("======================================================")

    register()

    while True:
        try:
            heartbeat()

            job = request_next_job()

            if not job:
                print("[IDLE] No AI jobs available.")

                if args.once:
                    print("[SELF-TEST] Worker API connection is healthy.")
                    return

                time.sleep(POLL_SECONDS)
                continue

            try:
                result, output_path = process_job(job)

                complete_job(
                    job["id"],
                    result=result,
                    output_path=output_path
                )

                print(f"[COMPLETE] job={job['id']}")

                if args.once:
                    return

            except Exception as exc:
                print(f"[FAILED] job={job.get('id')} error={exc}")
                fail_job(job["id"], exc)

        except KeyboardInterrupt:
            print("\nWorker stopped.")
            return

        except Exception as exc:
            print(f"[WORKER ERROR] {exc}")
            time.sleep(POLL_SECONDS)


if __name__ == "__main__":
    main()
