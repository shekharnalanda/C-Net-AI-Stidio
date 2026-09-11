import {spawn} from 'node:child_process';

export function runAdapter({executable, args = [], input = {}, timeoutMs = 120000}) {
  if (!executable) return Promise.reject(new Error('Engine executable is not configured.'));
  return new Promise((resolve, reject) => {
    const child = spawn(executable, args, {shell: false, windowsHide: true, stdio: ['pipe', 'pipe', 'pipe']});
    let output = '', errorOutput = '', settled = false;
    const timer = setTimeout(() => { child.kill(); reject(new Error('Generation timed out.')); }, timeoutMs);
    child.stdout.on('data', chunk => output += chunk);
    child.stderr.on('data', chunk => errorOutput += chunk);
    child.on('error', error => { clearTimeout(timer); if (!settled) { settled = true; reject(error); } });
    child.on('close', code => {
      clearTimeout(timer); if (settled) return; settled = true;
      if (code !== 0) return reject(new Error(errorOutput.trim() || `Engine exited with code ${code}.`));
      try { resolve(JSON.parse(output)); } catch { reject(new Error('Engine returned an invalid response.')); }
    });
    child.stdin.end(JSON.stringify(input));
  });
}
