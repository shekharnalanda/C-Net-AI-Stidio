const {app, BrowserWindow, ipcMain, dialog, nativeImage, shell} = require('electron');
const path = require('node:path');
const fs = require('node:fs');
const {scanHardware} = require('./hardware.cjs');

let manager;
let runtimeRoot;
let projectStore;
let licenseManager;
let settingsStore;
const registry = () => JSON.parse(fs.readFileSync(path.join(__dirname, '../registry/engines.json'), 'utf8'));
const engineById = id => {
  const engine = registry().engines.find(item => item.id === id);
  if (!engine) throw new Error('Unknown engine.');
  return engine;
};

function createWindow() {
  const window = new BrowserWindow({width: 1180, height: 760, minWidth: 920, minHeight: 620, webPreferences: {preload: path.join(__dirname, 'preload.cjs'), contextIsolation: true, nodeIntegration: false, sandbox: true}});
  window.loadFile(path.join(__dirname, 'renderer/index.html'));
}

ipcMain.handle('studio:scan-hardware', () => scanHardware());
ipcMain.handle('studio:registry', () => registry());
ipcMain.handle('studio:models', () => manager.list());
ipcMain.handle('studio:model-import', async (_event, id) => {
  const model = engineById(id);
  const result = await dialog.showOpenDialog({title: `Import ${model.name}`, properties: ['openFile'], filters: [{name: 'C-Net AI Model Package', extensions: ['cnetmodel', 'bin', 'gguf', 'zip']}]});
  if (result.canceled) return null;
  return manager.importPackage({...model, sourceFile: result.filePaths[0]});
});
ipcMain.handle('studio:model-download', (_event, id) => {
  const model = engineById(id);
  return manager.downloadPackage({id:model.id,name:model.name,url:model.downloadUrl,sha256:model.sha256});
});
ipcMain.handle('studio:model-activate', (_event, {task, id}) => manager.activate(task, id));
ipcMain.handle('studio:model-remove', (_event, id) => manager.remove(id));
ipcMain.handle('studio:runtime-install', async (_event,id) => {
  const {installRuntime} = await import('./core/runtime-installer.js');
  return installRuntime(engineById(id),runtimeRoot);
});
ipcMain.handle('studio:select-media', async () => {
  const result=await dialog.showOpenDialog({title:'Select audio or video',properties:['openFile'],filters:[{name:'Audio and Video',extensions:['wav','mp3','m4a','aac','flac','ogg','mp4','mov','mkv','webm','avi']}]});
  return result.canceled?null:result.filePaths[0];
});
ipcMain.handle('studio:select-video-images',async()=>{const result=await dialog.showOpenDialog({title:'Select slideshow images',properties:['openFile','multiSelections'],filters:[{name:'Images',extensions:['png','jpg','jpeg','webp','bmp']}]});return result.canceled?[]:result.filePaths;});
ipcMain.handle('studio:select-video-audio',async()=>{const result=await dialog.showOpenDialog({title:'Select soundtrack',properties:['openFile'],filters:[{name:'Audio',extensions:['wav','mp3','m4a','aac','flac','ogg']}]});return result.canceled?null:result.filePaths[0];});
ipcMain.handle('studio:select-video-subtitle',async()=>{const result=await dialog.showOpenDialog({title:'Select subtitles',properties:['openFile'],filters:[{name:'SubRip subtitles',extensions:['srt']}]});return result.canceled?null:result.filePaths[0];});
ipcMain.handle('studio:engine-status', async (_event, id) => {
  const {detectAdapter} = await import('./core/engine-adapters.js');
  const engine=engineById(id);
  let state=null;if(['whisper-cpp','llama-cli','stable-diffusion-cpp','ffmpeg-video'].includes(engine.adapter)){const module=await import('./core/runtime-installer.js');state=module.runtimeState(engine,runtimeRoot);if(state){engine.executable=state.executable;engine.modelFile=state.model;engine.mediaExecutable=state.mediaExecutable;}}
  return {...await detectAdapter(engine),updateAvailable:Boolean(state?.updateAvailable)};
});
ipcMain.handle('studio:generate', async (_event, {engineId, input}) => {
  const {generateWithEngine} = await import('./core/engine-adapters.js');
  const engine=engineById(engineId);
  if(['whisper-cpp','llama-cli','stable-diffusion-cpp','ffmpeg-video'].includes(engine.adapter)){const {runtimeState}=await import('./core/runtime-installer.js');const state=runtimeState(engine,runtimeRoot);if(state){engine.executable=state.executable;engine.modelFile=state.model;engine.mediaExecutable=state.mediaExecutable;}}
  const result=await generateWithEngine(engine,input);
  if(input.projectId)projectStore.addOutput(input.projectId,{task:input.task,engine:engine.id,content:result.content});
  licenseManager.record(input.task);
  return result;
});
ipcMain.handle('studio:projects',()=>projectStore.list());
ipcMain.handle('studio:project-create',(_event,name)=>projectStore.create(name));
ipcMain.handle('studio:subtitle-save',(_event,{file,text})=>{const outputRoot=path.resolve(require('node:os').homedir(),'Documents','C-Net AI Studio','Outputs');const target=path.resolve(file);if(!target.startsWith(`${outputRoot}${path.sep}`))throw new Error('Invalid subtitle output path.');fs.writeFileSync(target,text,'utf8');return target;});
const safeOutput = file => {const root=path.resolve(require('node:os').homedir(),'Documents','C-Net AI Studio','Outputs');const target=path.resolve(file);if(!target.startsWith(`${root}${path.sep}`))throw new Error('Invalid output path.');return target;};
ipcMain.handle('studio:image-data',(_event,file)=>{const target=safeOutput(file);if(!/\.(png|jpe?g)$/i.test(target))throw new Error('Invalid image type.');return `data:image/${path.extname(target).toLowerCase()==='.png'?'png':'jpeg'};base64,${fs.readFileSync(target).toString('base64')}`;});
ipcMain.handle('studio:image-export',async(_event,file)=>{const source=safeOutput(file);const result=await dialog.showSaveDialog({title:'Export image',defaultPath:path.basename(source),filters:[{name:'PNG image',extensions:['png']},{name:'JPEG image',extensions:['jpg','jpeg']}]});if(result.canceled)return null;if(/\.jpe?g$/i.test(result.filePath)){const jpeg=nativeImage.createFromPath(source).toJPEG(90);if(!jpeg.length)throw new Error('Image conversion failed.');fs.writeFileSync(result.filePath,jpeg)}else fs.copyFileSync(source,result.filePath);return result.filePath;});
ipcMain.handle('studio:video-open',async(_event,file)=>{const target=safeOutput(file);if(!/\.mp4$/i.test(target))throw new Error('Invalid video type.');const error=await shell.openPath(target);if(error)throw new Error(error);return true;});
ipcMain.handle('studio:video-export',async(_event,file)=>{const source=safeOutput(file);if(!/\.mp4$/i.test(source))throw new Error('Invalid video type.');const result=await dialog.showSaveDialog({title:'Export video',defaultPath:path.basename(source),filters:[{name:'MP4 video',extensions:['mp4']}]});if(result.canceled)return null;fs.copyFileSync(source,result.filePath);return result.filePath;});
ipcMain.handle('studio:license-status',()=>licenseManager.status());
ipcMain.handle('studio:license-import',async()=>{const result=await dialog.showOpenDialog({title:'Import signed C-Net license',properties:['openFile'],filters:[{name:'C-Net license',extensions:['cnetlicense','json']}]});return result.canceled?null:licenseManager.import(result.filePaths[0])});
ipcMain.handle('studio:settings-get',()=>settingsStore.get());
ipcMain.handle('studio:settings-save',(_event,value)=>settingsStore.save(value));
ipcMain.handle('studio:backup-export',async()=>{const result=await dialog.showSaveDialog({title:'Export workspace backup',defaultPath:`C-Net-AI-Studio-Backup-${new Date().toISOString().slice(0,10)}.cnetbackup`,filters:[{name:'C-Net backup',extensions:['cnetbackup']}]});if(result.canceled)return null;const {writeBackup}=await import('./core/settings-backup.js');return writeBackup(result.filePath,{settings:settingsStore.get(),projects:{projects:projectStore.list()}})});
ipcMain.handle('studio:backup-import',async()=>{const result=await dialog.showOpenDialog({title:'Restore workspace backup',properties:['openFile'],filters:[{name:'C-Net backup',extensions:['cnetbackup']}]});if(result.canceled)return null;const {readBackup}=await import('./core/settings-backup.js');const backup=readBackup(result.filePaths[0]);projectStore.write(backup.projects);settingsStore.save(backup.settings);return {projects:projectStore.list(),settings:settingsStore.get()}});
app.whenReady().then(async () => {
  const {ModelManager} = await import('./core/model-manager.js');
  manager = new ModelManager(path.join(app.getPath('userData'), 'models'));
  runtimeRoot = path.join(app.getPath('userData'),'runtimes');
  const {ProjectStore}=await import('./core/project-store.js');projectStore=new ProjectStore(path.join(app.getPath('userData'),'workspace'));
  const {LicenseManager}=await import('./core/license-manager.js');const keyFile=path.join(__dirname,'../registry/license-public-key.pem');const publicKey=process.env.CNET_LICENSE_PUBLIC_KEY||(fs.existsSync(keyFile)?fs.readFileSync(keyFile,'utf8'):'');licenseManager=new LicenseManager(path.join(app.getPath('userData'),'commercial'),publicKey);
  const {SettingsStore}=await import('./core/settings-backup.js');settingsStore=new SettingsStore(path.join(app.getPath('userData'),'workspace'));
  createWindow();
});
app.on('window-all-closed', () => { if (process.platform !== 'darwin') app.quit(); });
