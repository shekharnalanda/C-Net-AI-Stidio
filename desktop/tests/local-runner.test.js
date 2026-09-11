import test from 'node:test';
import assert from 'node:assert/strict';
import {runAdapter} from '../src/core/local-runner.js';
test('passes structured input without invoking a shell',async()=>{const result=await runAdapter({executable:process.execPath,args:['-e',"let s='';process.stdin.on('data',d=>s+=d);process.stdin.on('end',()=>process.stdout.write(JSON.stringify({received:JSON.parse(s).prompt})))"],input:{prompt:'hello; safe'}});assert.equal(result.received,'hello; safe')});
test('rejects invalid engine output',async()=>{await assert.rejects(runAdapter({executable:process.execPath,args:['-e',"process.stdout.write('invalid')"]}),/invalid response/) });
