import test from 'node:test';
import assert from 'node:assert/strict';
import crypto from 'node:crypto';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {deviceFingerprint,LicenseManager} from '../src/core/license-manager.js';

test('signed offline license activates only on its device',()=>{const root=fs.mkdtempSync(path.join(os.tmpdir(),'cnet-license-'));const {publicKey,privateKey}=crypto.generateKeyPairSync('ed25519');const manager=new LicenseManager(root,publicKey.export({type:'spki',format:'pem'}));const payload={edition:'Professional',deviceId:deviceFingerprint(),expiresAt:'2099-01-01T00:00:00Z'};const document={payload,signature:crypto.sign(null,Buffer.from(JSON.stringify(payload)),privateKey).toString('base64')};const file=path.join(root,'license.json.input');fs.writeFileSync(file,JSON.stringify(document));assert.equal(manager.import(file).edition,'Professional');fs.rmSync(root,{recursive:true,force:true})});
test('modified license signature is rejected',()=>{const root=fs.mkdtempSync(path.join(os.tmpdir(),'cnet-license-'));const {publicKey}=crypto.generateKeyPairSync('ed25519');const manager=new LicenseManager(root,publicKey.export({type:'spki',format:'pem'}));const file=path.join(root,'bad.json');fs.writeFileSync(file,JSON.stringify({payload:{edition:'Enterprise'},signature:Buffer.alloc(64).toString('base64')}));assert.throws(()=>manager.import(file),/signature verification failed/i);fs.rmSync(root,{recursive:true,force:true})});
test('usage ledger records generation and detects tampering',()=>{const root=fs.mkdtempSync(path.join(os.tmpdir(),'cnet-license-')),manager=new LicenseManager(root);assert.equal(manager.record('image').image,1);const file=path.join(root,'usage.json'),data=JSON.parse(fs.readFileSync(file));data.value.events.fake={text:999};fs.writeFileSync(file,JSON.stringify(data));assert.throws(()=>manager.usage(),/verification failed/);fs.rmSync(root,{recursive:true,force:true})});
