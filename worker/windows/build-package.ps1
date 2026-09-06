$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
$Parent = Split-Path -Parent $Root

$Out = Join-Path $Parent "C-Net-AI-Worker-V4-Windows.zip"

if (Test-Path $Out) {
    Remove-Item $Out -Force
}

Compress-Archive `
    -Path "$Root\*" `
    -DestinationPath $Out `
    -CompressionLevel Optimal

Write-Host ""
Write-Host "C-Net AI Worker package created successfully."
Write-Host "ZIP: $Out"
