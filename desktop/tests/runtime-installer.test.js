import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {runtimeState} from '../src/core/runtime-installer.js';
test('runtime state requires both executable and model',()=>{const root=fs.mkdtempSync(path.join(os.tmpdir(),'cnet-runtime-'));const target=path.join(root,'whisper-cpu');fs.mkdirSync(target);fs.writeFileSync(path.join(target,'runtime.json'),JSON.stringify({executable:path.join(target,'missing.exe'),model:path.join(target,'missing.bin')}));assert.equal(runtimeState({id:'whisper-cpu'},root),null);fs.rmSync(root,{recursive:true,force:true})});
