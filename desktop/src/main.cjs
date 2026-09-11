const {app, BrowserWindow, ipcMain, dialog} = require('electron');
const path = require('node:path');
const fs = require('node:fs');
const {scanHardware} = require('./hardware.cjs');

let manager;
let runtimeRoot;
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
ipcMain.handle('studio:engine-status', async (_event, id) => {
  const {detectAdapter} = await import('./core/engine-adapters.js');
  const engine=engineById(id);
  if(engine.adapter==='whisper-cpp'){const {runtimeState}=await import('./core/runtime-installer.js');const state=runtimeState(engine,runtimeRoot);if(state){engine.executable=state.executable;engine.modelFile=state.model;engine.mediaExecutable=state.mediaExecutable;}}
  return detectAdapter(engine);
});
ipcMain.handle('studio:generate', async (_event, {engineId, input}) => {
  const {generateWithEngine} = await import('./core/engine-adapters.js');
  const engine=engineById(engineId);
  if(engine.adapter==='whisper-cpp'){const {runtimeState}=await import('./core/runtime-installer.js');const state=runtimeState(engine,runtimeRoot);if(state){engine.executable=state.executable;engine.modelFile=state.model;engine.mediaExecutable=state.mediaExecutable;}}
  return generateWithEngine(engine,input);
});
ipcMain.handle('studio:subtitle-save',(_event,{file,text})=>{const outputRoot=path.resolve(require('node:os').homedir(),'Documents','C-Net AI Studio','Outputs');const target=path.resolve(file);if(!target.startsWith(`${outputRoot}${path.sep}`))throw new Error('Invalid subtitle output path.');fs.writeFileSync(target,text,'utf8');return target;});
app.whenReady().then(async () => {
  const {ModelManager} = await import('./core/model-manager.js');
  manager = new ModelManager(path.join(app.getPath('userData'), 'models'));
  runtimeRoot = path.join(app.getPath('userData'),'runtimes');
  createWindow();
});
app.on('window-all-closed', () => { if (process.platform !== 'darwin') app.quit(); });
