import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';

const redact=value=>{if(typeof value==='string')return value.replaceAll(os.homedir(),'%USERPROFILE%').replace(/(bearer|token|secret|password|api[_-]?key)[=: ]+[^\s,;]+/gi,'$1=[REDACTED]');if(Array.isArray(value))return value.map(redact);if(value&&typeof value==='object')return Object.fromEntries(Object.entries(value).map(([key,item])=>[/token|secret|password|key/i.test(key)?key:key,/token|secret|password|key/i.test(key)?'[REDACTED]':redact(item)]));return value};
export class SupportLogger{
  constructor(directory){this.directory=directory;this.file=path.join(directory,'studio.log');fs.mkdirSync(directory,{recursive:true})}
  rotate(){if(fs.existsSync(this.file)&&fs.statSync(this.file).size>1024*1024){fs.rmSync(`${this.file}.2`,{force:true});if(fs.existsSync(`${this.file}.1`))fs.renameSync(`${this.file}.1`,`${this.file}.2`);fs.renameSync(this.file,`${this.file}.1`)}}
  log(level,event,details={}){this.rotate();fs.appendFileSync(this.file,`${JSON.stringify({time:new Date().toISOString(),level,event,details:redact(details)})}\n`,{mode:0o600})}
  recent(limit=100){if(!fs.existsSync(this.file))return[];return fs.readFileSync(this.file,'utf8').trim().split('\n').filter(Boolean).slice(-Math.min(500,Math.max(1,limit))).map(line=>JSON.parse(line))}
}
