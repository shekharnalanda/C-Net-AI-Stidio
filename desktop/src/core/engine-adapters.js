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
  if (engine.adapter === 'llama-cli') return engine.executable && engine.modelFile && fs.existsSync(engine.executable) && fs.existsSync(engine.modelFile) ? {ready:true,runtimeReady:true} : {ready:false,reason:'Local text runtime and model are not installed.'};
  if (engine.adapter === 'ollama') {
    try {
      const result = await ollamaRequest('/api/tags', null, 3000);
      const models = (result.models || []).map(item => item.name);
      return {ready: models.includes(engine.model), runtimeReady: true, models, reason: models.includes(engine.model) ? null : `Required model ${engine.model} is not installed.`};
    }
    catch (error) { return {ready: false, reason: 'Local text runtime is not running.'}; }
  }
  if (engine.adapter === 'windows-sapi') return process.platform === 'win32' ? {ready:true, runtimeReady:true} : {ready:false, reason:'Windows offline voice is available only on Windows.'};
  if (engine.adapter === 'whisper-cpp') return engine.executable && engine.modelFile && fs.existsSync(engine.executable) && fs.existsSync(engine.modelFile) ? {ready:true,runtimeReady:true} : {ready:false,reason:'Whisper runtime and model are not installed.'};
  if (engine.executable) return fs.existsSync(engine.executable) ? {ready:true} : {ready:false, reason:'Engine executable was not found.'};
  return {ready:false, reason:'Engine runtime is not installed.'};
}

export async function generateWithEngine(engine, input) {
  if (engine.adapter === 'llama-cli') {
    if (!engine.executable || !engine.modelFile) throw new Error('Local text engine is not installed.');
    const language=input.language==='hi'?'उत्तर हिन्दी में दीजिए।':input.language==='en'?'Answer in English.':'Reply in the language used by the user.';
    const {spawn}=await import('node:child_process');
    const prompt=`${language}\n\nUser: ${input.prompt}\nAssistant:`;
    return new Promise((resolve,reject)=>{const child=spawn(engine.executable,['-m',engine.modelFile,'-p',prompt,'-n','512','--no-display-prompt'],{shell:false,windowsHide:true});let output='',stderr='';child.stdout.on('data',d=>output+=d);child.stderr.on('data',d=>stderr+=d);child.on('error',reject);child.on('close',code=>code===0?resolve({type:'text',content:output.trim(),engine:engine.id}):reject(new Error(stderr||`Text engine exited with code ${code}.`)))});
  }
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
  if (engine.adapter === 'whisper-cpp') {
    if (!input.mediaFile) throw new Error('Select an audio or video file first.');
    const outputDirectory = path.join(os.homedir(), 'Documents', 'C-Net AI Studio', 'Outputs');
    fs.mkdirSync(outputDirectory,{recursive:true});
    const prefix = path.join(outputDirectory, `captions-${Date.now()}`);
    const {spawn} = await import('node:child_process');
    const run=(executable,args)=>new Promise((resolve,reject)=>{const child=spawn(executable,args,{shell:false,windowsHide:true});let stderr='';child.stderr.on('data',d=>stderr+=d);child.on('error',reject);child.on('close',code=>code===0?resolve():reject(new Error(stderr||`Process exited with code ${code}.`)))});
    let audio=input.mediaFile,temporary=null;
    if(path.extname(input.mediaFile).toLowerCase()!=='.wav'){if(!engine.mediaExecutable)throw new Error('Verified FFmpeg runtime is not installed.');temporary=path.join(os.tmpdir(),`cnet-audio-${Date.now()}.wav`);await run(engine.mediaExecutable,['-y','-i',input.mediaFile,'-ar','16000','-ac','1','-c:a','pcm_s16le',temporary]);audio=temporary;}
    const language=/^(auto|hi|en)$/.test(input.language)?input.language:'auto';
    try{await run(engine.executable,['-m',engine.modelFile,'-f',audio,'-l',language,'-osrt','-of',prefix]);}finally{if(temporary)fs.rmSync(temporary,{force:true});}
    const subtitle = `${prefix}.srt`;
    if (!fs.existsSync(subtitle)) throw new Error('Subtitle file was not created.');
    return {type:'subtitle',content:subtitle,subtitleText:fs.readFileSync(subtitle,'utf8'),engine:engine.id};
  }
  if (engine.adapter === 'command-json') return runAdapter({executable:engine.executable,args:engine.args || [],input});
  throw new Error('This engine adapter is not configured.');
}
