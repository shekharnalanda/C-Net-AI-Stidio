const {app, BrowserWindow, ipcMain} = require('electron');
const path = require('node:path');
const fs = require('node:fs');
const {scanHardware} = require('./hardware.cjs');

function createWindow() {
  const window = new BrowserWindow({width: 1180, height: 760, minWidth: 920, minHeight: 620, webPreferences: {preload: path.join(__dirname, 'preload.cjs'), contextIsolation: true, nodeIntegration: false, sandbox: true}});
  window.loadFile(path.join(__dirname, 'renderer/index.html'));
}

ipcMain.handle('studio:scan-hardware', () => scanHardware());
ipcMain.handle('studio:registry', () => JSON.parse(fs.readFileSync(path.join(__dirname, '../registry/engines.json'), 'utf8')));
app.whenReady().then(createWindow);
app.on('window-all-closed', () => { if (process.platform !== 'darwin') app.quit(); });
