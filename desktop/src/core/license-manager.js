import crypto from 'node:crypto';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';

const atomicJson=(file,value)=>{const temporary=`${file}.tmp`;fs.writeFileSync(temporary,JSON.stringify(value,null,2),{mode:0o600});fs.renameSync(temporary,file)};
export const deviceFingerprint=()=>crypto.createHash('sha256').update([os.hostname(),os.arch(),os.cpus()[0]?.model||'',Math.round(os.totalmem()/1073741824)].join('|')).digest('hex');

export class LicenseManager{
  constructor(directory,publicKeyPem=''){this.directory=directory;this.file=path.join(directory,'license.json');this.usageFile=path.join(directory,'usage.json');this.secretFile=path.join(directory,'usage.key');this.publicKeyPem=publicKeyPem;fs.mkdirSync(directory,{recursive:true});if(!fs.existsSync(this.secretFile))fs.writeFileSync(this.secretFile,crypto.randomBytes(32),{mode:0o600});if(!fs.existsSync(this.usageFile))this.writeUsage({events:{}})}
  signUsage(value){return crypto.createHmac('sha256',fs.readFileSync(this.secretFile)).update(JSON.stringify(value)).digest('hex')}
  writeUsage(value){atomicJson(this.usageFile,{value,mac:this.signUsage(value)})}
  usage(){const stored=JSON.parse(fs.readFileSync(this.usageFile,'utf8'));if(!crypto.timingSafeEqual(Buffer.from(stored.mac,'hex'),Buffer.from(this.signUsage(stored.value),'hex')))throw new Error('Local usage record verification failed.');return stored.value}
  record(task){const usage=this.usage(),month=new Date().toISOString().slice(0,7);usage.events[month]??={};usage.events[month][task]=(usage.events[month][task]||0)+1;this.writeUsage(usage);return usage.events[month]}
  status(){const base={edition:'Community',licensed:false,deviceId:deviceFingerprint(),usage:this.usage().events[new Date().toISOString().slice(0,7)]||{}};if(!fs.existsSync(this.file))return base;try{const document=JSON.parse(fs.readFileSync(this.file,'utf8')),payload=document.payload;if(!this.publicKeyPem||!crypto.verify(null,Buffer.from(JSON.stringify(payload)),this.publicKeyPem,Buffer.from(document.signature,'base64')))throw new Error('Signature verification failed.');if(payload.deviceId&&payload.deviceId!==base.deviceId)throw new Error('License belongs to another device.');if(payload.expiresAt&&Date.parse(payload.expiresAt)<Date.now())throw new Error('License has expired.');return {...base,...payload,licensed:true}}catch(error){return {...base,licenseError:error.message}}}
  import(file){if(!this.publicKeyPem)throw new Error('Commercial license verification key is not configured in this build.');if(fs.statSync(file).size>65536)throw new Error('License file is too large.');const document=JSON.parse(fs.readFileSync(file,'utf8'));if(!document.payload||!document.signature)throw new Error('License file format is invalid.');const valid=crypto.verify(null,Buffer.from(JSON.stringify(document.payload)),this.publicKeyPem,Buffer.from(document.signature,'base64'));if(!valid)throw new Error('License signature verification failed.');if(document.payload.deviceId&&document.payload.deviceId!==deviceFingerprint())throw new Error('License belongs to another device.');atomicJson(this.file,document);return this.status()}
}
