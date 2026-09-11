import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import {detectAdapter, generateWithEngine, llamaCliArgs, runTextProcess, stableDiffusionArgs} from '../src/core/engine-adapters.js';

test('llama CLI runs once and cannot wait for interactive input',()=>{
  const args=llamaCliArgs({modelFile:'model.gguf'},'Namaste');
  assert.deepEqual(args,['-m','model.gguf','-p','Namaste','-n','256','--no-display-prompt','-st','--simple-io','--log-disable']);
  assert.equal(args.includes('--no-conversation'),false);
});

test('keeps valid llama output when Windows runtime closes with code 130',async()=>{
  const answer=await runTextProcess(process.execPath,['-e',"process.stdout.write('Offline answer');process.exit(130)"],5000);
  assert.equal(answer,'Offline answer');
});
test('unknown adapter remains safely unavailable',async()=>{assert.equal((await detectAdapter({})).ready,false)});
test('generation rejects an unconfigured adapter',async()=>{await assert.rejects(generateWithEngine({adapter:'unknown'},{prompt:'test'}),/not configured/)});
test('Windows voice adapter reports platform compatibility',async()=>{const status=await detectAdapter({adapter:'windows-sapi'});assert.equal(status.ready,process.platform==='win32')});
test('Windows voice adapter creates a real WAV file',{skip:process.platform!=='win32'},async()=>{const result=await generateWithEngine({id:'windows-sapi',adapter:'windows-sapi'},{prompt:'C Net AI Studio voice test'});assert.equal(result.type,'audio');assert.equal(fs.readFileSync(result.content).subarray(0,4).toString(),'RIFF');fs.rmSync(result.content,{force:true})});
test('image adapter clamps controls and keeps arguments separated',()=>{const args=stableDiffusionArgs({modelFile:'model.safetensors'},{prompt:'hello & goodbye',width:9999,height:1,steps:500,cfgScale:0,seed:-99,negativePrompt:'blur'},'output.png');assert.deepEqual(args.slice(0,6),['-m','model.safetensors','-p','hello & goodbye','-o','output.png']);assert.equal(args[args.indexOf('-W')+1],'1024');assert.equal(args[args.indexOf('-H')+1],'256');assert.equal(args[args.indexOf('--steps')+1],'50');assert.equal(args[args.indexOf('-n')+1],'blur')});
test('image adapter requires verified runtime files',async()=>{assert.equal((await detectAdapter({adapter:'stable-diffusion-cpp',executable:'missing',modelFile:'missing'})).ready,false)});
