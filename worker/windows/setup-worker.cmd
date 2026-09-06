@echo off
setlocal EnableExtensions

title C-Net AI Studio Worker Setup

echo ==========================================================
echo  C-Net AI Studio Dedicated Worker V4
echo  Windows Automatic Setup
echo ==========================================================
echo.

where python >nul 2>&1
if errorlevel 1 (
    echo ERROR: Python is not installed or not available in PATH.
    echo Install Python 3.11+ first.
    pause
    exit /b 1
)

python -m pip install --upgrade pip
python -m pip install -r requirements.txt

where ffmpeg >nul 2>&1
if errorlevel 1 (
    echo.
    echo WARNING: FFmpeg was not detected in PATH.
    echo Worker can register, but video rendering requires FFmpeg.
    echo.
)

if not exist config.json (
    copy /Y config.example.json config.json >nul
)

echo.
echo ==========================================================
echo  SETUP COMPLETE
echo ==========================================================
echo.
echo Edit config.json once and insert the worker token.
echo Then run start-worker.cmd
echo.
pause
