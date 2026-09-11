import test from 'node:test';
import assert from 'node:assert/strict';
import {detectAdapter, generateWithEngine} from '../src/core/engine-adapters.js';
test('unknown adapter remains safely unavailable',async()=>{assert.equal((await detectAdapter({})).ready,false)});
test('generation rejects an unconfigured adapter',async()=>{await assert.rejects(generateWithEngine({adapter:'unknown'},{prompt:'test'}),/not configured/)});
