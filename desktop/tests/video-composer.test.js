import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {createConcatManifest,videoArguments} from '../src/core/video-composer.js';

test('video manifest requires existing images and clamps duration',()=>{const root=fs.mkdtempSync(path.join(os.tmpdir(),'cnet-video-'));const image=path.join(root,'frame one.png'),manifest=path.join(root,'list.txt');fs.writeFileSync(image,'image');createConcatManifest([image],99,manifest);const text=fs.readFileSync(manifest,'utf8');assert.match(text,/duration 30/);assert.equal((text.match(/file '/g)||[]).length,2);fs.rmSync(root,{recursive:true,force:true})});
test('video arguments stay shell-free and select hardware codec',()=>{const args=videoArguments({videoCodec:'h264_nvenc'},{width:9999,height:1,fps:100,audioFile:'music & voice.mp3'},'frames.txt','video.mp4');assert.deepEqual(args.slice(0,7),['-y','-f','concat','-safe','0','-i','frames.txt']);assert.equal(args[args.indexOf('-c:v')+1],'h264_nvenc');assert.match(args[args.indexOf('-vf')+1],/scale=1920:360/);assert.equal(args.at(-1),'video.mp4')});
test('video manifest rejects missing input',()=>{assert.throws(()=>createConcatManifest(['/missing.png'],3,'unused.txt'),/not found/)});
