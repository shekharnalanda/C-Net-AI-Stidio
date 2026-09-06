@echo off
cd /d "%~dp0"
title C-Net AI Studio Dedicated Worker
python worker.py
pause
