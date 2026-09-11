import fs from 'node:fs';
import https from 'node:https';

export function downloadResumable({url, destination, timeoutMs = 30000, onProgress = () => {}, redirects = 0}) {
  const parsed = new URL(url);
  if (parsed.protocol !== 'https:') return Promise.reject(new Error('Model downloads require HTTPS.'));
  if (redirects > 5) return Promise.reject(new Error('Too many model download redirects.'));
  const existing = fs.existsSync(destination) ? fs.statSync(destination).size : 0;
  return new Promise((resolve, reject) => {
    const request = https.get(parsed, {timeout: timeoutMs, headers: existing ? {Range: `bytes=${existing}-`} : {}}, response => {
      if ([301,302,303,307,308].includes(response.statusCode) && response.headers.location) {
        response.resume();
        const next = new URL(response.headers.location, parsed).toString();
        return resolve(downloadResumable({url: next, destination, timeoutMs, onProgress, redirects: redirects + 1}));
      }
      if (![200,206].includes(response.statusCode)) { response.resume(); return reject(new Error(`Model download failed (${response.statusCode}).`)); }
      const resume = response.statusCode === 206 && existing > 0;
      const output = fs.createWriteStream(destination, {flags: resume ? 'a' : 'w', mode: 0o600});
      const total = Number(response.headers['content-length'] || 0) + (resume ? existing : 0);
      let received = resume ? existing : 0;
      response.on('data', chunk => { received += chunk.length; onProgress({received, total}); });
      response.pipe(output);
      output.on('finish', () => output.close(() => resolve({bytes: received, resumed: resume})));
      output.on('error', reject); response.on('error', reject);
    });
    request.on('timeout', () => request.destroy(new Error('Model download timed out.')));
    request.on('error', reject);
  });
}
