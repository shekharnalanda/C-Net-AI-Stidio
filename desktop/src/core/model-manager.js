import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';

export class ModelManager {
  constructor(rootDirectory) {
    this.rootDirectory = rootDirectory;
    fs.mkdirSync(rootDirectory, {recursive: true});
  }

  list() {
    return fs.readdirSync(this.rootDirectory, {withFileTypes: true})
      .filter(entry => entry.isDirectory())
      .map(entry => entry.name);
  }

  importPackage({id, sourceFile, sha256}) {
    if (!/^[a-z0-9][a-z0-9-]{1,63}$/.test(id)) throw new Error('Invalid model id.');
    const bytes = fs.readFileSync(sourceFile);
    const actual = crypto.createHash('sha256').update(bytes).digest('hex');
    if (actual !== sha256.toLowerCase()) throw new Error('Model package verification failed.');
    const target = path.join(this.rootDirectory, id);
    fs.mkdirSync(target, {recursive: true});
    fs.copyFileSync(sourceFile, path.join(target, path.basename(sourceFile)));
    fs.writeFileSync(path.join(target, 'install.json'), JSON.stringify({id, sha256: actual, installedAt: new Date().toISOString()}, null, 2));
    return {id, verified: true};
  }

  remove(id, activeIds = []) {
    if (activeIds.includes(id)) throw new Error('Active model cannot be removed.');
    const target = path.join(this.rootDirectory, id);
    if (!fs.existsSync(target)) return false;
    fs.rmSync(target, {recursive: true, force: true});
    return true;
  }
}
