import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import extract from 'extract-zip';
import {downloadResumable} from './download-manager.js';

const hash = file => crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex');
const findFile = (directory, name) => {
  for (const entry of fs.readdirSync(directory, {withFileTypes:true})) {
    const file = path.join(directory, entry.name);
    if (entry.isDirectory()) { const found = findFile(file, name); if (found) return found; }
    else if (entry.name.toLowerCase() === name.toLowerCase()) return file;
  }
  return null;
};

export async function installRuntime(engine, rootDirectory) {
  if (!engine.packages?.runtime || !engine.packages?.model) throw new Error('Verified runtime manifest is incomplete.');
  const target = path.join(rootDirectory, engine.id);
  const staging = `${target}.staging`;
  fs.rmSync(staging, {recursive:true, force:true}); fs.mkdirSync(staging, {recursive:true});
  try {
    const runtimeArchive = path.join(staging, 'runtime.zip');
    const modelFile = path.join(staging, engine.packages.model.fileName);
    await downloadResumable({url:engine.packages.runtime.url,destination:runtimeArchive});
    if (hash(runtimeArchive) !== engine.packages.runtime.sha256) throw new Error('Runtime verification failed.');
    await downloadResumable({url:engine.packages.model.url,destination:modelFile});
    if (hash(modelFile) !== engine.packages.model.sha256) throw new Error('Model verification failed.');
    const bin = path.join(staging, 'bin'); await extract(runtimeArchive, {dir:bin});
    const executable = findFile(bin, engine.executableName);
    if (!executable) throw new Error('Runtime executable was not found after extraction.');
    fs.rmSync(runtimeArchive, {force:true});
    const relativeExecutable = path.relative(staging, executable);
    fs.rmSync(target, {recursive:true, force:true}); fs.renameSync(staging, target);
    const state = {id:engine.id,executable:path.join(target,relativeExecutable),model:path.join(target,engine.packages.model.fileName),installedAt:new Date().toISOString()};
    fs.writeFileSync(path.join(target,'runtime.json'),JSON.stringify(state,null,2),{mode:0o600});
    return state;
  } catch (error) { fs.rmSync(staging,{recursive:true,force:true}); throw error; }
}

export function runtimeState(engine, rootDirectory) {
  const file = path.join(rootDirectory, engine.id, 'runtime.json');
  if (!fs.existsSync(file)) return null;
  try { const state=JSON.parse(fs.readFileSync(file,'utf8')); return fs.existsSync(state.executable)&&fs.existsSync(state.model)?state:null; }
  catch { return null; }
}
