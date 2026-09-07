import platform
import shutil
import socket
import subprocess
from pathlib import Path

import psutil

def output(command):
    try:
        return subprocess.check_output(
            command,
            stderr=subprocess.STDOUT,
            text=True,
            timeout=20
        ).strip()
    except Exception:
        return ""

def gpu():
    if platform.system().lower() != "windows":
        return "Unknown"

    return output([
        "powershell",
        "-NoProfile",
        "-Command",
        "(Get-CimInstance Win32_VideoController | "
        "Select-Object -ExpandProperty Name) -join '; '"
    ]) or "Unknown"

def find_ffmpeg():
    found = shutil.which("ffmpeg")

    if found:
        return found

    candidates = [
        Path.home() / "AppData/Local/Microsoft/WinGet/Packages",
        Path("C:/ffmpeg"),
        Path("C:/Program Files/ffmpeg"),
    ]

    for root in candidates:
        if not root.exists():
            continue

        try:
            for exe in root.rglob("ffmpeg.exe"):
                return str(exe)
        except Exception:
            pass

    return None

ram = psutil.virtual_memory()
disk = psutil.disk_usage(str(Path.cwd()))

ffmpeg = find_ffmpeg()

print("======================================================")
print(" C-Net AI Worker V5.1 - SYSTEM CHECK")
print("======================================================")
print("Hostname :", socket.gethostname())
print("OS       :", platform.platform())
print("CPU      :", platform.processor() or platform.machine())
print("GPU      :", gpu())
print("RAM GB   :", round(ram.total / 1024**3, 2))
print("Disk GB  :", round(disk.free / 1024**3, 2))
print("Python   :", platform.python_version())
print("FFmpeg   :", ffmpeg if ffmpeg else "NOT DETECTED")
print("======================================================")
