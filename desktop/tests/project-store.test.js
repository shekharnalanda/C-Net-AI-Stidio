import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {ProjectStore} from '../src/core/project-store.js';
test('stores project output history persistently',()=>{const root=fs.mkdtempSync(path.join(os.tmpdir(),'cnet-project-'));const store=new ProjectStore(root),project=store.create('Demo');store.addOutput(project.id,{task:'text',engine:'llama-cpu',content:'Namaste'});const restored=new ProjectStore(root).list()[0];assert.equal(restored.name,'Demo');assert.equal(restored.outputs[0].content,'Namaste');fs.rmSync(root,{recursive:true,force:true})});
test('recovers project workspace from last valid snapshot',()=>{const root=fs.mkdtempSync(path.join(os.tmpdir(),'cnet-project-')),store=new ProjectStore(root);store.create('First');store.create('Second');fs.writeFileSync(store.file,'corrupt');const recovered=new ProjectStore(root).list();assert.equal(recovered[0].name,'First');fs.rmSync(root,{recursive:true,force:true})});
