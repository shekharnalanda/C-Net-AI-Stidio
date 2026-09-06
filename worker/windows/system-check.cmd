@echo off
setlocal

echo ==========================================================
echo C-Net AI Studio Worker - System Check
echo ==========================================================
echo.

echo --- Windows ---
ver

echo.
echo --- CPU ---
wmic cpu get name 2>nul

echo.
echo --- RAM ---
wmic computersystem get TotalPhysicalMemory 2>nul

echo.
echo --- GPU ---
wmic path win32_VideoController get name 2>nul

echo.
echo --- Python ---
python --version 2>nul

echo.
echo --- FFmpeg ---
ffmpeg -version 2>nul | findstr /B "ffmpeg version"

echo.
echo ==========================================================
pause
