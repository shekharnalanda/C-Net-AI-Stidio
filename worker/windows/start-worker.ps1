$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $Root

$Host.UI.RawUI.WindowTitle = "C-Net AI Studio Dedicated Worker"

Write-Host ""
Write-Host "============================================================"
Write-Host " C-Net AI Studio Dedicated Worker"
Write-Host "============================================================"

if (Get-Command py -ErrorAction SilentlyContinue) {

    & py -3 -c "import sys" 2>$null

    if ($LASTEXITCODE -eq 0) {
        & py -3 worker.py
        exit
    }
}

$cmd = Get-Command python -ErrorAction SilentlyContinue

if ($cmd) {
    & $cmd.Source worker.py
    exit
}

$found = Get-ChildItem `
    "$env:LOCALAPPDATA\Programs\Python" `
    -Filter python.exe `
    -Recurse `
    -ErrorAction SilentlyContinue |
    Select-Object -First 1

if ($found) {
    & $found.FullName worker.py
    exit
}

throw "Verified Python installation could not be found."
