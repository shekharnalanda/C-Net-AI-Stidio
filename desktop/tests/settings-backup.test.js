import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {createBackup,readBackup,SettingsStore} from '../src/core/settings-backup.js';
test('settings accept only supported modes and languages',()=>{const root=fs.mkdtempSync(path.join(os.tmpdir(),'cnet-settings-')),store=new SettingsStore(root);assert.deepEqual(store.save({selectionMode:'bad',language:'xx'}),{selectionMode:'auto',language:'auto'});fs.rmSync(root,{recursive:true,force:true})});
test('workspace backup detects modification',()=>{const root=fs.mkdtempSync(path.join(os.tmpdir(),'cnet-backup-')),file=path.join(root,'backup.cnetbackup');const backup=createBackup({settings:{selectionMode:'auto'},projects:{projects:[]}});backup.settings.selectionMode='manual';fs.writeFileSync(file,JSON.stringify(backup));assert.throws(()=>readBackup(file),/verification failed/);fs.rmSync(root,{recursive:true,force:true})});
