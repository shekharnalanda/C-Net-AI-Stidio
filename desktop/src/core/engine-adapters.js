import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {runAdapter} from './local-runner.js';

async function ollamaRequest(path, body, timeoutMs = 120000) {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), timeoutMs);
  try {
    const response = await fetch(`http://127.0.0.1:11434${path}`, {method: body ? 'POST' : 'GET', headers: {'content-type':'application/json'}, body: body ? JSON.stringify(body) : undefined, signal: controller.signal});
    if (!response.ok) throw new Error(`Local text engine returned ${response.status}.`);
    return await response.json();
  } finally { clearTimeout(timer); }
}

export async function detectAdapter(engine) {
  if (engine.adapter === 'ollama') {
    try {
      const result = await ollamaRequest('/api/tags', null, 3000);
      const models = (result.models || []).map(item => item.name);
      return {ready: models.includes(engine.model), runtimeReady: true, models, reason: models.includes(engine.model) ? null : `Required model ${engine.model} is not installed.`};
    }
    catch (error) { return {ready: false, reason: 'Local text runtime is not running.'}; }
  }
  if (engine.adapter === 'windows-sapi') return process.platform === 'win32' ? {ready:true, runtimeReady:true} : {ready:false, reason:'Windows offline voice is available only on Windows.'};
  if (engine.executable) return fs.existsSync(engine.executable) ? {ready:true} : {ready:false, reason:'Engine executable was not found.'};
  return {ready:false, reason:'Engine runtime is not installed.'};
}

export async function generateWithEngine(engine, input) {
  if (engine.adapter === 'ollama') {
    const result = await ollamaRequest('/api/generate', {model: engine.model, prompt: input.prompt, stream: false});
    return {type:'text', content:result.response, engine:engine.id};
  }
  if (engine.adapter === 'windows-sapi') {
    if (process.platform !== 'win32') throw new Error('Windows offline voice is not available on this system.');
    const outputDirectory = path.join(os.homedir(), 'Documents', 'C-Net AI Studio', 'Outputs');
    fs.mkdirSync(outputDirectory, {recursive:true});
    const outputFile = path.join(outputDirectory, `voice-${Date.now()}.wav`);
    const script = "$ErrorActionPreference='Stop';Add-Type -AssemblyName System.Speech;$i=[Console]::In.ReadToEnd()|ConvertFrom-Json;$s=New-Object System.Speech.Synthesis.SpeechSynthesizer;$s.SetOutputToWaveFile($i.output);$s.Speak($i.text);$s.Dispose();[pscustomobject]@{type='audio';content=$i.output;engine='windows-sapi'}|ConvertTo-Json -Compress";
    return runAdapter({executable:'powershell.exe',args:['-NoProfile','-NonInteractive','-Command',script],input:{text:input.prompt,output:outputFile}});
  }
  if (engine.adapter === 'command-json') return runAdapter({executable:engine.executable,args:engine.args || [],input});
  throw new Error('This engine adapter is not configured.');
}
