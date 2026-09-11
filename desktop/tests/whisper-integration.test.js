import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import registry from '../registry/engines.json' with {type:'json'};
import {installRuntime} from '../src/core/runtime-installer.js';
import {generateWithEngine} from '../src/core/engine-adapters.js';

test('Windows verified Whisper creates a real SRT subtitle',{skip:process.platform!=='win32'||process.env.CNET_RUNTIME_INTEGRATION!=='1',timeout:300000},async()=>{
  const root=fs.mkdtempSync(path.join(os.tmpdir(),'cnet-whisper-'));
  const voice=await generateWithEngine({id:'windows-sapi',adapter:'windows-sapi'},{prompt:'Welcome to C Net AI Studio'});
  const engine=registry.engines.find(item=>item.id==='whisper-cpu');
  const runtime=await installRuntime(engine,root);
  const result=await generateWithEngine({...engine,executable:runtime.executable,modelFile:runtime.model},{mediaFile:voice.content});
  assert.equal(result.type,'subtitle'); assert.ok(fs.statSync(result.content).size>0);
  fs.rmSync(voice.content,{force:true}); fs.rmSync(result.content,{force:true}); fs.rmSync(root,{recursive:true,force:true});
});
