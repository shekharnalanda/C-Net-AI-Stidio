import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import crypto from 'node:crypto';
import {ModelManager} from '../src/core/model-manager.js';

function fixture(){const root=fs.mkdtempSync(path.join(os.tmpdir(),'cnet-model-'));const source=path.join(root,'fixture.cnetmodel');fs.writeFileSync(source,'verified-model');return {root,source,sha256:crypto.createHash('sha256').update('verified-model').digest('hex')}}
test('imports only checksum-verified model packages',()=>{const f=fixture(),manager=new ModelManager(path.join(f.root,'models'));assert.equal(manager.importPackage({id:'test-model',sourceFile:f.source,sha256:f.sha256}).id,'test-model');assert.equal(manager.list().length,1);fs.rmSync(f.root,{recursive:true,force:true})});
test('rejects modified model package',()=>{const f=fixture(),manager=new ModelManager(path.join(f.root,'models'));assert.throws(()=>manager.importPackage({id:'test-model',sourceFile:f.source,sha256:'0'.repeat(64)}),/verification failed/);fs.rmSync(f.root,{recursive:true,force:true})});
test('persists active model and protects it from removal',()=>{const f=fixture(),manager=new ModelManager(path.join(f.root,'models'));manager.importPackage({id:'test-model',sourceFile:f.source,sha256:f.sha256});manager.activate('text','test-model');assert.deepEqual(manager.list()[0].activeFor,['text']);assert.throws(()=>manager.remove('test-model'),/Active model/);fs.rmSync(f.root,{recursive:true,force:true})});
