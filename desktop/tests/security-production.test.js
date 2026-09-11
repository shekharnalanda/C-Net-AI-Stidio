import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {validateGenerationInput} from '../src/core/input-validator.js';
import {SupportLogger} from '../src/core/support-logger.js';
import {validateRegistry} from '../src/core/diagnostics.js';
import {safeZipEntry} from '../src/core/secure-zip.js';

test('generation input rejects unauthorized tasks and invalid files',()=>{assert.throws(()=>validateGenerationInput({tasks:['text']},{task:'video'}),/not authorized/);assert.throws(()=>validateGenerationInput({tasks:['caption']},{task:'caption',mediaFile:'relative.mp3'}),/Invalid media/)});
test('generation input limits prompt size',()=>{const value=validateGenerationInput({tasks:['text']},{task:'text',prompt:'x'.repeat(25000)});assert.equal(value.prompt.length,20000)});
test('support logs redact secrets and home directory',()=>{const root=fs.mkdtempSync(path.join(os.tmpdir(),'cnet-log-')),logger=new SupportLogger(root);logger.log('error','test',{token:'private',message:`${os.homedir()} password=hunter2`});const row=logger.recent()[0];assert.equal(row.details.token,'[REDACTED]');assert.doesNotMatch(JSON.stringify(row),/hunter2/);assert.equal(JSON.stringify(row).includes(os.homedir()),false);fs.rmSync(root,{recursive:true,force:true})});
test('registry security check blocks non-HTTPS and missing digest',()=>{const errors=validateRegistry({engines:[{id:'bad',packages:{runtime:{url:'http://invalid',sha256:''}}}]});assert.equal(errors.length,2)});
test('ZIP extraction rejects traversal, absolute paths and symbolic links',()=>{assert.throws(()=>safeZipEntry('/safe','../escape.exe'),/Unsafe/);assert.throws(()=>safeZipEntry('/safe','C:/escape.exe'),/Unsafe/);assert.throws(()=>safeZipEntry('/safe','link',0o120777<<16),/symbolic links/);assert.match(safeZipEntry('/safe','bin/tool.exe').target,/bin/)});
