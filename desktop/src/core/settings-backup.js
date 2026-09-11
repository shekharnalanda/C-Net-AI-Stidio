import crypto from 'node:crypto';
import fs from 'node:fs';
import path from 'node:path';

const atomic=(file,value)=>{const temporary=`${file}.tmp`;fs.writeFileSync(temporary,JSON.stringify(value,null,2),{mode:0o600});fs.renameSync(temporary,file)};
const digest=value=>crypto.createHash('sha256').update(JSON.stringify(value)).digest('hex');
export class SettingsStore{
  constructor(directory){this.file=path.join(directory,'settings.json');fs.mkdirSync(directory,{recursive:true});if(!fs.existsSync(this.file))atomic(this.file,{selectionMode:'auto',language:'auto'})}
  get(){return JSON.parse(fs.readFileSync(this.file,'utf8'))}
  save(input){const value={selectionMode:['auto','suggested','manual'].includes(input.selectionMode)?input.selectionMode:'auto',language:['auto','hi','en'].includes(input.language)?input.language:'auto'};atomic(this.file,value);return value}
}
export function createBackup({settings,projects}){const payload={schemaVersion:1,createdAt:new Date().toISOString(),settings,projects};return {...payload,checksum:digest(payload)}}
export function verifyBackup(document){const {checksum,...payload}=document;if(document.schemaVersion!==1||!Array.isArray(document.projects?.projects)||digest(payload)!==checksum)throw new Error('Backup verification failed.');return payload}
export function writeBackup(file,data){atomic(file,createBackup(data));return file}
export function readBackup(file){if(fs.statSync(file).size>10*1024*1024)throw new Error('Backup file is too large.');return verifyBackup(JSON.parse(fs.readFileSync(file,'utf8')))}
