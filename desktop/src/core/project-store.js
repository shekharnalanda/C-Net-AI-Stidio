import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';

export class ProjectStore {
  constructor(directory){this.directory=directory;this.file=path.join(directory,'projects.json');fs.mkdirSync(directory,{recursive:true});if(!fs.existsSync(this.file))this.write({projects:[]});}
  read(){return JSON.parse(fs.readFileSync(this.file,'utf8'))}
  write(data){const temporary=`${this.file}.tmp`;fs.writeFileSync(temporary,JSON.stringify(data,null,2),{mode:0o600});fs.renameSync(temporary,this.file)}
  list(){return this.read().projects}
  create(name){const data=this.read();const project={id:crypto.randomUUID(),name:String(name||'Untitled Project').slice(0,100),createdAt:new Date().toISOString(),outputs:[]};data.projects.unshift(project);this.write(data);return project}
  addOutput(projectId,output){const data=this.read(),project=data.projects.find(item=>item.id===projectId);if(!project)throw new Error('Project was not found.');const record={id:crypto.randomUUID(),task:output.task,engine:output.engine,content:output.content,createdAt:new Date().toISOString()};project.outputs.unshift(record);project.outputs=project.outputs.slice(0,500);this.write(data);return record}
}
