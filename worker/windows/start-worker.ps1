$ErrorActionPreference = "Continue"

$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $Root

$Host.UI.RawUI.WindowTitle = "C-Net AI Studio Dedicated Worker V5.1"

Write-Host ""
Write-Host "=============================================================="
Write-Host " C-Net AI Studio Dedicated Worker V5.1"
Write-Host "=============================================================="
Write-Host "Keep this window open while AI processing is required."
Write-Host "=============================================================="
Write-Host ""

python worker.py

Write-Host ""
Write-Host "Worker stopped."
Read-Host "Press Enter to close"
