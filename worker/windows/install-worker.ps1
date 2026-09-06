$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $Root

Write-Host ""
Write-Host "========================================================="
Write-Host " C-Net AI Studio Dedicated Worker V5"
Write-Host " Automatic Windows Installer"
Write-Host "========================================================="
Write-Host ""

function Command-Exists($cmd) {
    return [bool](Get-Command $cmd -ErrorAction SilentlyContinue)
}

Write-Host "[1/7] Checking Python..."

if (-not (Command-Exists "python")) {
    Write-Host "Python not found."

    if (Command-Exists "winget") {
        Write-Host "Installing Python automatically..."
        winget install `
          --id Python.Python.3.12 `
          -e `
          --accept-package-agreements `
          --accept-source-agreements

        $env:Path = [System.Environment]::GetEnvironmentVariable(
            "Path",
            "Machine"
        ) + ";" + [System.Environment]::GetEnvironmentVariable(
            "Path",
            "User"
        )
    }
}

if (-not (Command-Exists "python")) {
    throw "Python could not be installed automatically."
}

python --version

Write-Host ""
Write-Host "[2/7] Installing Python packages..."

python -m pip install --upgrade pip
python -m pip install -r requirements.txt

Write-Host ""
Write-Host "[3/7] Checking FFmpeg..."

if (-not (Command-Exists "ffmpeg")) {

    if (Command-Exists "winget") {
        Write-Host "Installing FFmpeg automatically..."

        winget install `
          --id Gyan.FFmpeg `
          -e `
          --accept-package-agreements `
          --accept-source-agreements

        $env:Path = [System.Environment]::GetEnvironmentVariable(
            "Path",
            "Machine"
        ) + ";" + [System.Environment]::GetEnvironmentVariable(
            "Path",
            "User"
        )
    }
}

if (-not (Command-Exists "ffmpeg")) {
    Write-Warning "FFmpeg was not detected after automatic setup."
    Write-Warning "Worker can register but video rendering may fail."
}
else {
    ffmpeg -version | Select-Object -First 1
}

Write-Host ""
Write-Host "[4/7] Creating workspace..."

New-Item `
  -ItemType Directory `
  -Force `
  -Path (Join-Path $Root "workspace") `
  | Out-Null

Write-Host ""
Write-Host "[5/7] Running system check..."

python worker-check.py

Write-Host ""
Write-Host "[6/7] Creating desktop shortcuts..."

$Desktop = [Environment]::GetFolderPath("Desktop")

$StartShortcut = Join-Path $Desktop "C-Net AI Worker.lnk"

$Shell = New-Object -ComObject WScript.Shell
$Shortcut = $Shell.CreateShortcut($StartShortcut)
$Shortcut.TargetPath = "powershell.exe"
$Shortcut.Arguments = "-ExecutionPolicy Bypass -File `"$Root\start-worker.ps1`""
$Shortcut.WorkingDirectory = $Root
$Shortcut.Save()

Write-Host ""
Write-Host "[7/7] Installation complete."

Write-Host ""
Write-Host "========================================================="
Write-Host " C-NET AI WORKER INSTALLATION COMPLETE"
Write-Host "========================================================="
Write-Host ""
Write-Host "Desktop shortcut created: C-Net AI Worker"
Write-Host ""
Write-Host "Starting worker connectivity test..."
Write-Host ""

python worker.py --once

Write-Host ""
Write-Host "If registration succeeded, setup is complete."
Write-Host ""

Read-Host "Press Enter to finish"
