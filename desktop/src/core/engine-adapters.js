import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {runAdapter} from './local-runner.js';
import {createConcatManifest, temporaryManifest, videoArguments} from './video-composer.js';

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
  if (engine.adapter === 'stable-diffusion-cpp') return engine.executable && engine.modelFile && fs.existsSync(engine.executable) && fs.existsSync(engine.modelFile) ? {ready:true,runtimeReady:true} : {ready:false,reason:'Verified image runtime and model are not installed.'};
  if (engine.adapter === 'ffmpeg-video') return engine.executable && fs.existsSync(engine.executable) ? {ready:true,runtimeReady:true} : {ready:false,reason:'Verified video runtime is not installed.'};
  if (engine.executable) return fs.existsSync(engine.executable) ? {ready:true} : {ready:false, reason:'Engine executable was not found.'};
  return {ready:false, reason:'Engine runtime is not installed.'};
}

export function stableDiffusionArgs(engine, input, outputFile) {
  const number = (value, fallback, min, max) => Math.min(max, Math.max(min, Number(value) || fallback));
  const width = Math.round(number(input.width, 512, 256, 1024) / 64) * 64;
  const height = Math.round(number(input.height, 512, 256, 1024) / 64) * 64;
  const steps = Math.round(number(input.steps, 20, 1, 50));
  const cfg = number(input.cfgScale, 7, 1, 20);
  const seed = Math.round(number(input.seed, -1, -1, 2147483647));
  const args = ['-m', engine.modelFile, '-p', String(input.prompt), '-o', outputFile, '-W', String(width), '-H', String(height), '--steps', String(steps), '--cfg-scale', String(cfg), '-s', String(seed)];
  if (input.negativePrompt) args.push('-n', String(input.negativePrompt));
  return args;
}

export function llamaCliArgs(engine, prompt) {
  return ['-m', engine.modelFile, '-p', prompt, '-n', '256', '--no-display-prompt', '--no-conversation', '--simple-io'];
}

function runTextProcess(executable, args, timeoutMs = 360000) {
  return new Promise(async (resolve, reject) => {
    const {spawn} = await import('node:child_process');
    const child = spawn(executable, args, {shell:false, windowsHide:true, stdio:['ignore','pipe','pipe']});
    let output = '', stderr = '', settled = false;
    const finish = callback => value => { if (settled) return; settled = true; clearTimeout(timer); callback(value); };
    const timer = setTimeout(() => {
      if (settled) return;
      child.kill();
      finish(reject)(new Error('Text generation timed out. Please use a shorter prompt or a faster model.'));
    }, timeoutMs);
    child.stdout.on('data', data => output += data);
    child.stderr.on('data', data => stderr += data);
    child.on('error', finish(reject));
    child.on('close', code => {
      if (code !== 0) return finish(reject)(new Error(stderr.trim() || `Text engine exited with code ${code}.`));
      const content = output.trim();
      if (!content) return finish(reject)(new Error('Text engine completed without producing an answer.'));
      finish(resolve)(content);
    });
  });
}

export async function generateWithEngine(engine, input) {
  if (engine.adapter === 'llama-cli') {
    if (!engine.executable || !engine.modelFile) throw new Error('Local text engine is not installed.');
    const language=input.language==='hi'?'उत्तर हिन्दी में दीजिए।':input.language==='en'?'Answer in English.':'Reply in the language used by the user.';
    const prompt=`${language}\n\nUser: ${input.prompt}\nAssistant:`;
    const content = await runTextProcess(engine.executable, llamaCliArgs(engine, prompt));
    return {type:'text', content, engine:engine.id};
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
  if (engine.adapter === 'stable-diffusion-cpp') {
    if (!engine.executable || !engine.modelFile) throw new Error('Verified image engine is not installed.');
    const outputDirectory = path.join(os.homedir(), 'Documents', 'C-Net AI Studio', 'Outputs');
    fs.mkdirSync(outputDirectory,{recursive:true});
    const outputFile = path.join(outputDirectory, `image-${Date.now()}.png`);
    const {spawn}=await import('node:child_process');
    await new Promise((resolve,reject)=>{const child=spawn(engine.executable,stableDiffusionArgs(engine,input,outputFile),{shell:false,windowsHide:true});let stderr='';child.stderr.on('data',d=>stderr+=d);child.on('error',reject);child.on('close',code=>code===0?resolve():reject(new Error(stderr||`Image engine exited with code ${code}.`)))});
    if (!fs.existsSync(outputFile) || fs.statSync(outputFile).size < 8) throw new Error('Image engine did not create a valid output.');
    return {type:'image',content:outputFile,engine:engine.id};
  }
  if (engine.adapter === 'ffmpeg-video') {
    if (!engine.executable) throw new Error('Verified video engine is not installed.');
    const outputDirectory=path.join(os.homedir(),'Documents','C-Net AI Studio','Outputs');fs.mkdirSync(outputDirectory,{recursive:true});
    const outputFile=path.join(outputDirectory,`video-${Date.now()}.mp4`),manifest=temporaryManifest();
    const {spawn}=await import('node:child_process');
    try{createConcatManifest(input.imageFiles,input.secondsPerImage,manifest);await new Promise((resolve,reject)=>{const child=spawn(engine.executable,videoArguments(engine,input,manifest,outputFile),{shell:false,windowsHide:true});let stderr='';child.stderr.on('data',d=>stderr+=d);child.on('error',reject);child.on('close',code=>code===0?resolve():reject(new Error(stderr||`Video engine exited with code ${code}.`)))});}finally{fs.rmSync(manifest,{force:true})}
    if(!fs.existsSync(outputFile)||fs.statSync(outputFile).size<12)throw new Error('Video engine did not create a valid output.');
    return {type:'video',content:outputFile,engine:engine.id};
  }
  if (engine.adapter === 'command-json') return runAdapter({executable:engine.executable,args:engine.args || [],input});
  throw new Error('This engine adapter is not configured.');
}
