import json
import platform
import shutil
import socket
import subprocess
from pathlib import Path

import psutil

ROOT = Path(__file__).resolve().parent
CONFIG = json.loads(
    (ROOT / "config.json").read_text(encoding="utf-8")
)

def cmd(args):
    try:
        return subprocess.check_output(
            args,
            stderr=subprocess.STDOUT,
            text=True,
            timeout=20,
        ).strip()
    except Exception:
        return ""

def gpu():
    if platform.system().lower() == "windows":
        return cmd([
            "powershell",
            "-NoProfile",
            "-Command",
            "(Get-CimInstance Win32_VideoController | "
            "Select-Object -ExpandProperty Name) -join '; '"
        ]) or "Unknown"

    return "Unknown"

ram = psutil.virtual_memory()

print("======================================================")
print(" C-Net AI Worker - Hardware Check")
print("======================================================")
print("Hostname :", socket.gethostname())
print("OS       :", platform.platform())
print("CPU      :", platform.processor() or platform.machine())
print("GPU      :", gpu())
print("RAM GB   :", round(ram.total / 1024 / 1024 / 1024, 2))
print("Python   :", platform.python_version())
print("FFmpeg   :", "YES" if shutil.which("ffmpeg") else "NO")
print("Studio   :", CONFIG["studio_url"])
print("======================================================")
