import fs from 'node:fs';
import path from 'node:path';

const types={caption:/\.(wav|mp3|m4a|aac|flac|ogg|mp4|mov|mkv|webm|avi)$/i,image:/\.(png|jpe?g|webp|bmp)$/i,audio:/\.(wav|mp3|m4a|aac|flac|ogg)$/i,subtitle:/\.srt$/i};
const verifiedFile=(file,pattern,label)=>{if(typeof file!=='string'||!path.isAbsolute(file)||!pattern.test(file)||!fs.existsSync(file)||!fs.statSync(file).isFile())throw new Error(`Invalid ${label} file.`);return file};
export function validateGenerationInput(engine,input){
  if(!input||typeof input!=='object'||!engine.tasks?.includes(input.task))throw new Error('Engine is not authorized for this task.');
  const value={...input,prompt:String(input.prompt||'').slice(0,20000),projectId:String(input.projectId||'').slice(0,100)};
  if(['text','script','prompt','image','voice','tts'].includes(input.task)&&!value.prompt.trim())throw new Error('Generation instructions are required.');
  if(input.task==='caption')value.mediaFile=verifiedFile(input.mediaFile,types.caption,'media');
  if(input.task==='video'){
    if(!Array.isArray(input.imageFiles)||!input.imageFiles.length||input.imageFiles.length>100)throw new Error('Select between 1 and 100 images.');
    value.imageFiles=input.imageFiles.map(file=>verifiedFile(file,types.image,'image'));
    value.audioFile=input.audioFile?verifiedFile(input.audioFile,types.audio,'audio'):null;
    value.subtitleFile=input.subtitleFile?verifiedFile(input.subtitleFile,types.subtitle,'subtitle'):null;
  }
  return value;
}
