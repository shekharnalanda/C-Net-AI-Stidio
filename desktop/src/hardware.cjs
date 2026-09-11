const os = require('node:os');
const {execFile} = require('node:child_process');
const {promisify} = require('node:util');
const execFileAsync = promisify(execFile);

async function scanHardware() {
  const profile = {ramGb: Math.round(os.totalmem() / 1073741824), vramGb: 0, gpuVendor: 'unknown', gpuName: 'Unknown', freeDiskGb: 0};
  if (process.platform !== 'win32') return profile;
  try {
    const command = "$g=Get-CimInstance Win32_VideoController|Sort-Object AdapterRAM -Descending|Select-Object -First 1; $d=Get-CimInstance Win32_LogicalDisk -Filter \"DeviceID='C:'\"; [pscustomobject]@{gpuName=$g.Name;vramGb=[math]::Round($g.AdapterRAM/1GB,1);freeDiskGb=[math]::Round($d.FreeSpace/1GB,1)}|ConvertTo-Json -Compress";
    const {stdout} = await execFileAsync('powershell.exe', ['-NoProfile', '-NonInteractive', '-Command', command]);
    const result = JSON.parse(stdout.trim());
    profile.gpuName = result.gpuName || profile.gpuName;
    profile.gpuVendor = /nvidia/i.test(profile.gpuName) ? 'nvidia' : /amd|radeon/i.test(profile.gpuName) ? 'amd' : /intel/i.test(profile.gpuName) ? 'intel' : 'unknown';
    profile.vramGb = Number(result.vramGb || 0);
    profile.freeDiskGb = Number(result.freeDiskGb || 0);
  } catch (error) {
    profile.scanWarning = error.message;
  }
  return profile;
}

module.exports = {scanHardware};
