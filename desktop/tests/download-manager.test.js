import test from 'node:test';
import assert from 'node:assert/strict';
import {downloadResumable} from '../src/core/download-manager.js';
test('rejects non-HTTPS model sources',async()=>{await assert.rejects(downloadResumable({url:'http://example.test/model',destination:'unused'}),/require HTTPS/)});
