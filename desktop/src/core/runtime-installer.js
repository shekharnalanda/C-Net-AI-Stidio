import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import {downloadResumable} from './download-manager.js';
import {secureExtract} from './secure-zip.js';

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
  if (!engine.packages?.runtime) throw new Error('Verified runtime manifest is incomplete.');
  const target = path.join(rootDirectory, engine.id);
  const staging = `${target}.staging`;
  fs.rmSync(staging, {recursive:true, force:true}); fs.mkdirSync(staging, {recursive:true});
  try {
    const runtimeArchive = path.join(staging, 'runtime.zip');
    const modelFile = engine.packages.model ? path.join(staging, engine.packages.model.fileName) : null;
    await downloadResumable({url:engine.packages.runtime.url,destination:runtimeArchive});
    if (hash(runtimeArchive) !== engine.packages.runtime.sha256) throw new Error('Runtime verification failed.');
    if (engine.packages.model) {
      await downloadResumable({url:engine.packages.model.url,destination:modelFile});
      if (hash(modelFile) !== engine.packages.model.sha256) throw new Error('Model verification failed.');
    }
    const bin = path.join(staging, 'bin'); await secureExtract(runtimeArchive,bin);
    const executable = findFile(bin, engine.executableName);
    if (!executable) throw new Error('Runtime executable was not found after extraction.');
    let mediaExecutable = null;
    if (engine.packages.media) {
      const mediaArchive=path.join(staging,'media.zip');
      await downloadResumable({url:engine.packages.media.url,destination:mediaArchive});
      if(hash(mediaArchive)!==engine.packages.media.sha256)throw new Error('Media runtime verification failed.');
      const mediaDirectory=path.join(staging,'media');await secureExtract(mediaArchive,mediaDirectory);fs.rmSync(mediaArchive,{force:true});
      mediaExecutable=findFile(mediaDirectory,engine.mediaExecutableName);
      if(!mediaExecutable)throw new Error('Media runtime executable was not found.');
    }
    fs.rmSync(runtimeArchive, {force:true});
    const relativeExecutable = path.relative(staging, executable);
    const relativeMediaExecutable = mediaExecutable ? path.relative(staging,mediaExecutable) : null;
    fs.rmSync(target, {recursive:true, force:true}); fs.renameSync(staging, target);
    const state = {id:engine.id,runtimeVersion:engine.packages.runtime.version,runtimeSha256:engine.packages.runtime.sha256,modelSha256:engine.packages.model?.sha256||null,executable:path.join(target,relativeExecutable),model:engine.packages.model?path.join(target,engine.packages.model.fileName):null,mediaExecutable:relativeMediaExecutable?path.join(target,relativeMediaExecutable):null,installedAt:new Date().toISOString()};
    fs.writeFileSync(path.join(target,'runtime.json'),JSON.stringify(state,null,2),{mode:0o600});
    return state;
  } catch (error) { fs.rmSync(staging,{recursive:true,force:true}); throw error; }
}

export function runtimeState(engine, rootDirectory) {
  const file = path.join(rootDirectory, engine.id, 'runtime.json');
  if (!fs.existsSync(file)) return null;
  try { const state=JSON.parse(fs.readFileSync(file,'utf8'));const modelReady=!engine.packages?.model||(state.model&&fs.existsSync(state.model));const ready=fs.existsSync(state.executable)&&modelReady&&(!engine.packages?.media||fs.existsSync(state.mediaExecutable));if(!ready)return null;return {...state,updateAvailable:Boolean(engine.packages?.runtime?.sha256&&state.runtimeSha256!==engine.packages.runtime.sha256)||Boolean(engine.packages?.model?.sha256&&state.modelSha256!==engine.packages.model.sha256)}; }
  catch { return null; }
}
