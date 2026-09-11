import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {spawn} from 'node:child_process';
import {installRuntime} from '../src/core/runtime-installer.js';
import {generateWithEngine} from '../src/core/engine-adapters.js';

const enabled=process.platform==='win32'&&process.env.CNET_RUNTIME_INTEGRATION==='1';
const run=(executable,args)=>new Promise((resolve,reject)=>{const child=spawn(executable,args,{shell:false,windowsHide:true});let stderr='';child.stderr.on('data',d=>stderr+=d);child.on('error',reject);child.on('close',code=>code===0?resolve():reject(new Error(stderr||`Exited ${code}`)))});

test('Windows verified FFmpeg creates a real MP4',{skip:!enabled,timeout:600000},async()=>{const registry=JSON.parse(fs.readFileSync(new URL('../registry/engines.json',import.meta.url),'utf8'));const engine=registry.engines.find(item=>item.id==='video-cpu');const root=fs.mkdtempSync(path.join(os.tmpdir(),'cnet-video-e2e-'));try{const state=await installRuntime(engine,root);const image=path.join(root,'frame.bmp');await run(state.executable,['-y','-f','lavfi','-i','color=c=blue:s=640x360','-frames:v','1',image]);const result=await generateWithEngine({...engine,executable:state.executable},{imageFiles:[image],secondsPerImage:1,width:640,height:360,fps:15});assert.equal(result.type,'video');assert.equal(fs.readFileSync(result.content).subarray(4,8).toString(),'ftyp');fs.rmSync(result.content,{force:true})}finally{fs.rmSync(root,{recursive:true,force:true})}});
