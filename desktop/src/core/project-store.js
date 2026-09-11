import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';

export class ProjectStore {
  constructor(directory){this.directory=directory;this.file=path.join(directory,'projects.json');this.backupFile=`${this.file}.bak`;fs.mkdirSync(directory,{recursive:true});if(!fs.existsSync(this.file))this.write({projects:[]});}
  valid(data){if(!data||!Array.isArray(data.projects))throw new Error('Project workspace format is invalid.');return data}
  read(){try{return this.valid(JSON.parse(fs.readFileSync(this.file,'utf8')))}catch(error){if(!fs.existsSync(this.backupFile))throw error;const recovered=this.valid(JSON.parse(fs.readFileSync(this.backupFile,'utf8'))),temporary=`${this.file}.recovery`;fs.writeFileSync(temporary,JSON.stringify(recovered,null,2),{mode:0o600});fs.renameSync(temporary,this.file);return recovered}}
  write(data){this.valid(data);if(fs.existsSync(this.file)){try{this.valid(JSON.parse(fs.readFileSync(this.file,'utf8')));fs.copyFileSync(this.file,this.backupFile)}catch{}}const temporary=`${this.file}.tmp`;fs.writeFileSync(temporary,JSON.stringify(data,null,2),{mode:0o600});fs.renameSync(temporary,this.file)}
  list(){return this.read().projects}
  create(name){const data=this.read();const project={id:crypto.randomUUID(),name:String(name||'Untitled Project').slice(0,100),createdAt:new Date().toISOString(),outputs:[]};data.projects.unshift(project);this.write(data);return project}
  addOutput(projectId,output){const data=this.read(),project=data.projects.find(item=>item.id===projectId);if(!project)throw new Error('Project was not found.');const record={id:crypto.randomUUID(),task:output.task,engine:output.engine,content:output.content,createdAt:new Date().toISOString()};project.outputs.unshift(record);project.outputs=project.outputs.slice(0,500);this.write(data);return record}
}
