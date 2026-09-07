$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $Root

function Refresh-Path {
    $machine = [Environment]::GetEnvironmentVariable("Path", "Machine")
    $user = [Environment]::GetEnvironmentVariable("Path", "User")
    $env:Path = "$machine;$user"
}

function Command-Exists($Name) {
    return [bool](Get-Command $Name -ErrorAction SilentlyContinue)
}

function Find-FFmpeg {
    $direct = Get-Command ffmpeg -ErrorAction SilentlyContinue

    if ($direct) {
        return $direct.Source
    }

    $roots = @(
        "$env:LOCALAPPDATA\Microsoft\WinGet\Packages",
        "C:\ffmpeg",
        "C:\Program Files\ffmpeg"
    )

    foreach ($root in $roots) {
        if (-not (Test-Path $root)) {
            continue
        }

        $found = Get-ChildItem `
            -Path $root `
            -Filter ffmpeg.exe `
            -Recurse `
            -ErrorAction SilentlyContinue |
            Select-Object -First 1

        if ($found) {
            return $found.FullName
        }
    }

    return $null
}

Write-Host ""
Write-Host "=============================================================="
Write-Host " C-Net AI Studio Dedicated Worker V5.1"
Write-Host " VERIFIED WINDOWS INSTALLER"
Write-Host "=============================================================="

$ConfigPath = Join-Path $Root "config.json"
$ExamplePath = Join-Path $Root "config.example.json"

if (-not (Test-Path $ConfigPath)) {
    Copy-Item $ExamplePath $ConfigPath -Force
}

$Config = Get-Content $ConfigPath -Raw | ConvertFrom-Json

if ([string]::IsNullOrWhiteSpace($Config.worker_token)) {

    Write-Host ""
    Write-Host "[Activation] This computer needs one-time activation."

    $Activation = Read-Host `
        "Enter Activation Code from Master Admin"

    if ([string]::IsNullOrWhiteSpace($Activation)) {
        throw "Activation code cannot be blank."
    }

    $Config.activation_code = $Activation.Trim()

    $Config |
        ConvertTo-Json -Depth 10 |
        Set-Content `
            -Path $ConfigPath `
            -Encoding UTF8
}

Write-Host ""
Write-Host "[1/6] Checking Python..."

if (-not (Command-Exists "python")) {

    if (-not (Command-Exists "winget")) {
        throw "Python is missing and Windows Package Manager (winget) is unavailable."
    }

    Write-Host "Installing Python 3.12..."

    winget install `
        --id Python.Python.3.12 `
        -e `
        --silent `
        --accept-package-agreements `
        --accept-source-agreements

    Refresh-Path
}

if (-not (Command-Exists "python")) {
    throw "Python installation failed or Python is not available in PATH."
}

python --version

if ($LASTEXITCODE -ne 0) {
    throw "Python verification failed."
}

Write-Host ""
Write-Host "[2/6] Installing Python dependencies..."

python -m pip install --upgrade pip

if ($LASTEXITCODE -ne 0) {
    throw "pip upgrade failed."
}

python -m pip install -r requirements.txt

if ($LASTEXITCODE -ne 0) {
    throw "Worker dependency installation failed."
}

Write-Host ""
Write-Host "[3/6] Checking FFmpeg..."

$FFmpeg = Find-FFmpeg

if (-not $FFmpeg) {

    if (Command-Exists "winget") {

        Write-Host "FFmpeg not found. Installing automatically..."

        winget source update | Out-Null

        winget install `
            --id Gyan.FFmpeg `
            -e `
            --silent `
            --accept-package-agreements `
            --accept-source-agreements

        Refresh-Path

        Start-Sleep -Seconds 3

        $FFmpeg = Find-FFmpeg
    }
}

if ($FFmpeg) {
    Write-Host "FFmpeg detected:"
    Write-Host $FFmpeg

    & $FFmpeg -version |
        Select-Object -First 1
}
else {
    Write-Warning "FFmpeg could not be installed/detected."
    Write-Warning "Activation can continue, but video rendering will remain unavailable."
}

Write-Host ""
Write-Host "[4/6] Creating workspace..."

New-Item `
    -ItemType Directory `
    -Force `
    -Path (Join-Path $Root "workspace") |
    Out-Null

Write-Host ""
Write-Host "[5/6] Running hardware check..."

python worker-check.py

if ($LASTEXITCODE -ne 0) {
    throw "Hardware/system check failed."
}

Write-Host ""
Write-Host "[6/6] Running REAL activation and API verification..."
Write-Host ""

python worker.py --self-test

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "=============================================================="
    Write-Host " C-NET AI WORKER INSTALLATION FAILED"
    Write-Host "=============================================================="
    Write-Host "The worker was NOT verified."
    Write-Host "Do not treat this installation as complete."
    Write-Host "=============================================================="
    throw "Worker activation/self-test failed."
}

Write-Host ""
Write-Host "Creating Desktop shortcut..."

$Desktop = [Environment]::GetFolderPath("Desktop")
$ShortcutPath = Join-Path $Desktop "C-Net AI Worker.lnk"

$Shell = New-Object -ComObject WScript.Shell
$Shortcut = $Shell.CreateShortcut($ShortcutPath)

$Shortcut.TargetPath = "powershell.exe"
$Shortcut.Arguments = `
    "-NoProfile -ExecutionPolicy Bypass -File `"$Root\start-worker.ps1`""

$Shortcut.WorkingDirectory = $Root
$Shortcut.Save()

Write-Host ""
Write-Host "=============================================================="
Write-Host " C-NET AI WORKER V5.1 INSTALLATION VERIFIED SUCCESS"
Write-Host "=============================================================="
Write-Host " Activation       = PASS"
Write-Host " Device Credential= PASS"
Write-Host " API Authentication= PASS"
Write-Host " Heartbeat        = PASS"

if ($FFmpeg) {
    Write-Host " FFmpeg           = READY"
}
else {
    Write-Host " FFmpeg           = NOT READY"
}

Write-Host " Desktop Shortcut = CREATED"
Write-Host "=============================================================="
Write-Host ""
Write-Host "The computer should now appear in Master Admin > AI Workers."
Write-Host ""

Read-Host "Press Enter to finish"
