import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import https from 'node:https';

const ID_PATTERN = /^[a-z0-9][a-z0-9-]{1,63}$/;

function atomicJson(file, value) {
  const temporary = `${file}.tmp`;
  fs.writeFileSync(temporary, JSON.stringify(value, null, 2), {mode: 0o600});
  fs.renameSync(temporary, file);
}

function checksum(file) {
  return crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex');
}

export class ModelManager {
  constructor(rootDirectory) {
    this.rootDirectory = rootDirectory;
    this.stateFile = path.join(rootDirectory, 'state.json');
    fs.mkdirSync(rootDirectory, {recursive: true});
    if (!fs.existsSync(this.stateFile)) atomicJson(this.stateFile, {installed: {}, activeByTask: {}});
  }

  state() {
    try { return JSON.parse(fs.readFileSync(this.stateFile, 'utf8')); }
    catch { throw new Error('Model Manager state is unreadable.'); }
  }

  list() {
    const state = this.state();
    return Object.values(state.installed).map(model => ({...model, activeFor: Object.entries(state.activeByTask).filter(([, id]) => id === model.id).map(([task]) => task)}));
  }

  importPackage({id, sourceFile, sha256, name = id}) {
    if (!ID_PATTERN.test(id)) throw new Error('Invalid model id.');
    if (!fs.statSync(sourceFile).isFile()) throw new Error('Model package was not found.');
    const actual = checksum(sourceFile);
    if (!sha256 || actual !== sha256.toLowerCase()) throw new Error('Model package verification failed.');
    const target = path.join(this.rootDirectory, id);
    fs.mkdirSync(target, {recursive: true});
    const packageFile = path.join(target, 'model.package');
    fs.copyFileSync(sourceFile, packageFile);
    const state = this.state();
    state.installed[id] = {id, name, sha256: actual, packageFile, bytes: fs.statSync(packageFile).size, installedAt: new Date().toISOString()};
    atomicJson(this.stateFile, state);
    return state.installed[id];
  }

  async downloadPackage({id, url, sha256, name = id}) {
    if (!ID_PATTERN.test(id)) throw new Error('Invalid model id.');
    const parsed = new URL(url);
    if (parsed.protocol !== 'https:') throw new Error('Model downloads require HTTPS.');
    const temporary = path.join(this.rootDirectory, `${id}.download`);
    await new Promise((resolve, reject) => {
      const request = https.get(parsed, {timeout: 30000}, response => {
        if (response.statusCode !== 200) return reject(new Error(`Model download failed (${response.statusCode}).`));
        const output = fs.createWriteStream(temporary, {mode: 0o600});
        response.pipe(output); output.on('finish', () => output.close(resolve)); output.on('error', reject);
      });
      request.on('timeout', () => request.destroy(new Error('Model download timed out.'))); request.on('error', reject);
    });
    try { return this.importPackage({id, sourceFile: temporary, sha256, name}); }
    finally { fs.rmSync(temporary, {force: true}); }
  }

  activate(task, id) {
    const state = this.state();
    if (!state.installed[id]) throw new Error('Install this model before selecting it.');
    state.activeByTask[task] = id;
    atomicJson(this.stateFile, state);
    return {task, id};
  }

  remove(id) {
    const state = this.state();
    if (Object.values(state.activeByTask).includes(id)) throw new Error('Active model cannot be removed. Select another model first.');
    if (!state.installed[id]) return false;
    fs.rmSync(path.join(this.rootDirectory, id), {recursive: true, force: true});
    delete state.installed[id];
    atomicJson(this.stateFile, state);
    return true;
  }
}
