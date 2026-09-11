import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import {detectAdapter, generateWithEngine} from '../src/core/engine-adapters.js';
test('unknown adapter remains safely unavailable',async()=>{assert.equal((await detectAdapter({})).ready,false)});
test('generation rejects an unconfigured adapter',async()=>{await assert.rejects(generateWithEngine({adapter:'unknown'},{prompt:'test'}),/not configured/)});
test('Windows voice adapter reports platform compatibility',async()=>{const status=await detectAdapter({adapter:'windows-sapi'});assert.equal(status.ready,process.platform==='win32')});
test('Windows voice adapter creates a real WAV file',{skip:process.platform!=='win32'},async()=>{const result=await generateWithEngine({id:'windows-sapi',adapter:'windows-sapi'},{prompt:'C Net AI Studio voice test'});assert.equal(result.type,'audio');assert.equal(fs.readFileSync(result.content).subarray(0,4).toString(),'RIFF');fs.rmSync(result.content,{force:true})});
