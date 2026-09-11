import fs from 'node:fs';
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
  if (engine.executable) return fs.existsSync(engine.executable) ? {ready:true} : {ready:false, reason:'Engine executable was not found.'};
  return {ready:false, reason:'Engine runtime is not installed.'};
}

export async function generateWithEngine(engine, input) {
  if (engine.adapter === 'ollama') {
    const result = await ollamaRequest('/api/generate', {model: engine.model, prompt: input.prompt, stream: false});
    return {type:'text', content:result.response, engine:engine.id};
  }
  if (engine.adapter === 'command-json') return runAdapter({executable:engine.executable,args:engine.args || [],input});
  throw new Error('This engine adapter is not configured.');
}
