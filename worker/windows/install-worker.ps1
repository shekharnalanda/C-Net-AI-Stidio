$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $Root

function Refresh-Path {
    $machine = [Environment]::GetEnvironmentVariable("Path","Machine")
    $user = [Environment]::GetEnvironmentVariable("Path","User")
    $env:Path = "$machine;$user"
}

$script:PythonMode = $null
$script:PythonExe = $null

function Test-Python {
    $script:PythonMode = $null
    $script:PythonExe = $null

    # 1. Python Launcher
    if (Get-Command py -ErrorAction SilentlyContinue) {
        & py -3 -c "import sys; print(sys.executable)" 2>$null

        if ($LASTEXITCODE -eq 0) {
            $script:PythonMode = "py"
            return $true
        }
    }

    # 2. Direct python.exe from PATH
    $cmd = Get-Command python -ErrorAction SilentlyContinue

    if ($cmd) {
        & $cmd.Source -c "import sys; print(sys.executable)" 2>$null

        if ($LASTEXITCODE -eq 0) {
            $script:PythonMode = "exe"
            $script:PythonExe = $cmd.Source
            return $true
        }
    }

    # 3. Search normal user installations
    $roots = @(
        "$env:LOCALAPPDATA\Programs\Python",
        "C:\Program Files\Python312",
        "C:\Program Files\Python311",
        "C:\Python312",
        "C:\Python311"
    )

    foreach ($root in $roots) {

        if (-not (Test-Path $root)) {
            continue
        }

        $found = Get-ChildItem `
            -Path $root `
            -Filter python.exe `
            -Recurse `
            -ErrorAction SilentlyContinue |
            Select-Object -First 1

        if ($found) {

            & $found.FullName -c "import sys; print(sys.executable)" 2>$null

            if ($LASTEXITCODE -eq 0) {
                $script:PythonMode = "exe"
                $script:PythonExe = $found.FullName
                return $true
            }
        }
    }

    return $false
}

function Invoke-Python {
    param(
        [Parameter(ValueFromRemainingArguments=$true)]
        [string[]]$Arguments
    )

    if ($script:PythonMode -eq "py") {
        & py -3 @Arguments
    }
    elseif ($script:PythonMode -eq "exe") {
        & $script:PythonExe @Arguments
    }
    else {
        throw "Verified Python runtime is not available."
    }
}

function Find-FFmpeg {

    $cmd = Get-Command ffmpeg -ErrorAction SilentlyContinue

    if ($cmd) {
        return $cmd.Source
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
Write-Host "============================================================"
Write-Host " C-Net AI Studio Dedicated Worker V5.2"
Write-Host " VERIFIED WINDOWS INSTALLER"
Write-Host "============================================================"

$ConfigPath = Join-Path $Root "config.json"
$ExamplePath = Join-Path $Root "config.example.json"

if (-not (Test-Path $ConfigPath)) {
    Copy-Item $ExamplePath $ConfigPath -Force
}

$Config = Get-Content $ConfigPath -Raw | ConvertFrom-Json

if ([string]::IsNullOrWhiteSpace($Config.worker_token)) {

    Write-Host ""
    Write-Host "[Activation] New device activation required."

    $Activation = Read-Host "Enter NEW Activation Code from Master Admin"

    if ([string]::IsNullOrWhiteSpace($Activation)) {
        throw "Activation code cannot be blank."
    }

    $Config.activation_code = $Activation.Trim()

    $Config |
        ConvertTo-Json -Depth 10 |
        Set-Content $ConfigPath -Encoding UTF8
}

Write-Host ""
Write-Host "[1/6] Checking verified Python runtime..."

if (-not (Test-Python)) {

    Write-Host "Usable Python not found."

    if (-not (Get-Command winget -ErrorAction SilentlyContinue)) {
        throw "Python is unavailable and winget is not installed."
    }

    Write-Host "Installing Python 3.12 automatically..."

    winget install `
        --id Python.Python.3.12 `
        -e `
        --accept-package-agreements `
        --accept-source-agreements

    Refresh-Path

    Start-Sleep -Seconds 5

    if (-not (Test-Python)) {
        throw "Python was installed but could not be verified."
    }
}

Write-Host "Verified Python:"
Invoke-Python -c "import sys; print(sys.version); print(sys.executable)"

if ($LASTEXITCODE -ne 0) {
    throw "Python verification failed."
}

Write-Host ""
Write-Host "[2/6] Installing Python dependencies..."

Invoke-Python -m pip install --upgrade pip

if ($LASTEXITCODE -ne 0) {
    throw "pip upgrade failed."
}

Invoke-Python -m pip install -r requirements.txt

if ($LASTEXITCODE -ne 0) {
    throw "Worker dependency installation failed."
}

Write-Host ""
Write-Host "[3/6] Checking FFmpeg..."

$FFmpeg = Find-FFmpeg

if (-not $FFmpeg) {

    if (Get-Command winget -ErrorAction SilentlyContinue) {

        Write-Host "Installing FFmpeg automatically..."

        winget source update | Out-Null

        winget install `
            --id Gyan.FFmpeg `
            -e `
            --accept-package-agreements `
            --accept-source-agreements

        Refresh-Path
        Start-Sleep -Seconds 4

        $FFmpeg = Find-FFmpeg
    }
}

if ($FFmpeg) {
    Write-Host "FFmpeg detected:"
    Write-Host $FFmpeg
    & $FFmpeg -version | Select-Object -First 1
}
else {
    Write-Warning "FFmpeg is still not available."
}

Write-Host ""
Write-Host "[4/6] Creating workspace..."

New-Item `
    -ItemType Directory `
    -Force `
    -Path (Join-Path $Root "workspace") |
    Out-Null

Write-Host ""
Write-Host "[5/6] Hardware check..."

Invoke-Python worker-check.py

if ($LASTEXITCODE -ne 0) {
    throw "Hardware check failed."
}

Write-Host ""
Write-Host "[6/6] Activation + authentication + heartbeat test..."
Write-Host ""

Invoke-Python worker.py --self-test

if ($LASTEXITCODE -ne 0) {
    throw "Worker activation/self-test failed."
}

Write-Host ""
Write-Host "Creating Desktop shortcut..."

$Desktop = [Environment]::GetFolderPath("Desktop")
$ShortcutPath = Join-Path $Desktop "C-Net AI Worker.lnk"

$Shell = New-Object -ComObject WScript.Shell
$Shortcut = $Shell.CreateShortcut($ShortcutPath)

$Shortcut.TargetPath = "powershell.exe"
$Shortcut.Arguments = "-NoProfile -ExecutionPolicy Bypass -File `"$Root\start-worker.ps1`""
$Shortcut.WorkingDirectory = $Root
$Shortcut.Save()

Write-Host ""
Write-Host "============================================================"
Write-Host " C-NET AI WORKER V5.2 INSTALLATION VERIFIED SUCCESS"
Write-Host "============================================================"
Write-Host " Python             = PASS"
Write-Host " Device Activation  = PASS"
Write-Host " Credential         = PASS"
Write-Host " API Authentication = PASS"
Write-Host " Heartbeat           = PASS"

if ($FFmpeg) {
    Write-Host " FFmpeg              = READY"
}
else {
    Write-Host " FFmpeg              = NOT READY"
}

Write-Host " Desktop Shortcut    = CREATED"
Write-Host "============================================================"
Write-Host ""
Write-Host "Check Master Admin > AI Worker Management."
Write-Host ""

Read-Host "Press Enter to finish"
