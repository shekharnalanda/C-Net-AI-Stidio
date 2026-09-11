import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';

const clamp = (value, fallback, minimum, maximum) => Math.min(maximum, Math.max(minimum, Number(value) || fallback));
const concatPath = file => String(file).replaceAll('\\','/').replaceAll("'", "'\\''");
const filterPath = file => String(file).replaceAll('\\','/').replaceAll(':','\\:').replaceAll("'","\\'").replaceAll(',','\\,').replaceAll('[','\\[').replaceAll(']','\\]');

export function createConcatManifest(files, secondsPerImage, destination) {
  if (!Array.isArray(files) || !files.length) throw new Error('Select at least one image.');
  const duration=clamp(secondsPerImage,3,1,30);
  const lines=[];
  for(const file of files){if(!fs.existsSync(file)||!fs.statSync(file).isFile())throw new Error('A selected image was not found.');lines.push(`file '${concatPath(file)}'`,`duration ${duration}`)}
  lines.push(`file '${concatPath(files.at(-1))}'`);
  fs.writeFileSync(destination,`${lines.join('\n')}\n`,{mode:0o600});
  return destination;
}

export function videoArguments(engine,input,manifest,outputFile){
  const width=Math.round(clamp(input.width,1280,640,1920)/2)*2;
  const height=Math.round(clamp(input.height,720,360,1080)/2)*2;
  const fps=Math.round(clamp(input.fps,30,15,60));
  const filters=[`scale=${width}:${height}:force_original_aspect_ratio=decrease`,`pad=${width}:${height}:(ow-iw)/2:(oh-ih)/2`,`fps=${fps}`];
  if(input.subtitleFile)filters.push(`subtitles=filename='${filterPath(input.subtitleFile)}'`);
  const args=['-y','-f','concat','-safe','0','-i',manifest];
  if(input.audioFile)args.push('-stream_loop','-1','-i',input.audioFile);
  args.push('-vf',filters.join(','),'-c:v',engine.videoCodec||'mpeg4');
  if((engine.videoCodec||'mpeg4')==='mpeg4')args.push('-q:v','4');else args.push('-preset','p4','-cq','23');
  if(input.audioFile)args.push('-map','0:v:0','-map','1:a:0','-c:a','aac','-b:a','192k','-shortest');else args.push('-an');
  args.push('-movflags','+faststart','-pix_fmt','yuv420p',outputFile);
  return args;
}

export function temporaryManifest(){return path.join(os.tmpdir(),`cnet-video-${Date.now()}-${process.pid}.txt`)}
