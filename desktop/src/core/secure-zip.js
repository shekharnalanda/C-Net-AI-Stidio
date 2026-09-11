import fs from 'node:fs';
import path from 'node:path';
import {pipeline} from 'node:stream/promises';
import yauzl from 'yauzl';

export function safeZipEntry(directory,name,externalAttributes=0){
  const normalized=String(name).replaceAll('\\','/');
  if(!normalized||normalized.includes('\0')||normalized.startsWith('/')||/^[a-z]:/i.test(normalized)||normalized.split('/').includes('..'))throw new Error('Unsafe ZIP entry path.');
  const mode=(externalAttributes>>>16)&0xffff;if((mode&0o170000)===0o120000)throw new Error('ZIP symbolic links are not allowed.');
  const root=path.resolve(directory),target=path.resolve(root,...normalized.split('/'));if(target!==root&&!target.startsWith(`${root}${path.sep}`))throw new Error('Unsafe ZIP extraction target.');return{target,directory:normalized.endsWith('/')};
}

export function secureExtract(archive,directory){return new Promise((resolve,reject)=>{yauzl.open(archive,{lazyEntries:true,autoClose:true,decodeStrings:true,validateEntrySizes:true},(openError,zip)=>{if(openError)return reject(openError);let total=0,settled=false;const fail=error=>{if(settled)return;settled=true;zip.close();reject(error)};zip.on('error',fail);zip.on('end',()=>{if(!settled){settled=true;resolve()}});zip.on('entry',entry=>{(async()=>{if(entry.isEncrypted())throw new Error('Encrypted ZIP entries are not supported.');total+=entry.uncompressedSize;if(entry.uncompressedSize>2*1024**3||total>5*1024**3)throw new Error('ZIP extraction size limit exceeded.');const safe=safeZipEntry(directory,entry.fileName,entry.externalFileAttributes);if(safe.directory){fs.mkdirSync(safe.target,{recursive:true});zip.readEntry();return}fs.mkdirSync(path.dirname(safe.target),{recursive:true});const stream=await new Promise((ok,no)=>zip.openReadStream(entry,(error,value)=>error?no(error):ok(value)));await pipeline(stream,fs.createWriteStream(safe.target,{flags:'wx',mode:0o600}));zip.readEntry()})().catch(fail)});zip.readEntry()})})}
