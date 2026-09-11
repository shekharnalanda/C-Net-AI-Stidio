import test from 'node:test';
import assert from 'node:assert/strict';
import {recommendEngines, selectEngine} from '../src/core/recommender.js';
const registry={engines:[{id:'lite',tasks:['image'],minRamGb:8,minVramGb:0,gpu:'any',diskGb:4,quality:2,speed:2},{id:'gpu',tasks:['image'],minRamGb:16,minVramGb:8,gpu:'nvidia',diskGb:20,quality:5,speed:4}]};
test('auto selects a compatible engine for current hardware',()=>{assert.equal(selectEngine(registry,{ramGb:8,vramGb:0,gpuVendor:'intel',freeDiskGb:30},'image').id,'lite')});
test('future upgraded hardware automatically receives stronger recommendation',()=>{assert.equal(selectEngine(registry,{ramGb:32,vramGb:12,gpuVendor:'nvidia',freeDiskGb:100},'image').id,'gpu')});
test('manual selection blocks incompatible engine',()=>{assert.throws(()=>selectEngine(registry,{ramGb:8,vramGb:0,gpuVendor:'intel',freeDiskGb:30},'image','manual','gpu'),/RAM/)});
test('suggested results explain incompatibility',()=>{assert.ok(recommendEngines(registry,{ramGb:8,vramGb:0,gpuVendor:'intel',freeDiskGb:10},'image').find(e=>e.id==='gpu').reasons.length>=3)});
