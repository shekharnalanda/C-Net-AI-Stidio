@echo off
cd /d "%~dp0"
title C-Net AI Studio Worker V5.1 Installer
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0install-worker.ps1"
if errorlevel 1 (
    echo.
    echo ==============================================================
    echo  C-NET AI WORKER INSTALLATION DID NOT COMPLETE SUCCESSFULLY
    echo ==============================================================
    pause
    exit /b 1
)
