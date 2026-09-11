const {app, BrowserWindow, ipcMain, dialog} = require('electron');
const path = require('node:path');
const fs = require('node:fs');
const {scanHardware} = require('./hardware.cjs');

let manager;

function createWindow() {
  const window = new BrowserWindow({width: 1180, height: 760, minWidth: 920, minHeight: 620, webPreferences: {preload: path.join(__dirname, 'preload.cjs'), contextIsolation: true, nodeIntegration: false, sandbox: true}});
  window.loadFile(path.join(__dirname, 'renderer/index.html'));
}

ipcMain.handle('studio:scan-hardware', () => scanHardware());
ipcMain.handle('studio:registry', () => JSON.parse(fs.readFileSync(path.join(__dirname, '../registry/engines.json'), 'utf8')));
ipcMain.handle('studio:models', () => manager.list());
ipcMain.handle('studio:model-import', async (_event, model) => {
  const result = await dialog.showOpenDialog({title: `Import ${model.name}`, properties: ['openFile'], filters: [{name: 'C-Net AI Model Package', extensions: ['cnetmodel', 'bin', 'gguf', 'zip']}]});
  if (result.canceled) return null;
  return manager.importPackage({...model, sourceFile: result.filePaths[0]});
});
ipcMain.handle('studio:model-download', (_event, model) => manager.downloadPackage(model));
ipcMain.handle('studio:model-activate', (_event, {task, id}) => manager.activate(task, id));
ipcMain.handle('studio:model-remove', (_event, id) => manager.remove(id));
app.whenReady().then(async () => {
  const {ModelManager} = await import('./core/model-manager.js');
  manager = new ModelManager(path.join(app.getPath('userData'), 'models'));
  createWindow();
});
app.on('window-all-closed', () => { if (process.platform !== 'darwin') app.quit(); });
