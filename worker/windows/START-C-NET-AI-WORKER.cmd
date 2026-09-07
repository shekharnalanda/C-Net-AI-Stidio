@echo off
cd /d "%~dp0"
title C-Net AI Studio Dedicated Worker V5.1
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0start-worker.ps1"
