const {contextBridge, ipcRenderer} = require('electron');
contextBridge.exposeInMainWorld('studio', {
  scanHardware: () => ipcRenderer.invoke('studio:scan-hardware'),
  getRegistry: () => ipcRenderer.invoke('studio:registry')
});
