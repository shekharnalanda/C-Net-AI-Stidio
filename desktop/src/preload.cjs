const {contextBridge, ipcRenderer} = require('electron');
contextBridge.exposeInMainWorld('studio', {
  scanHardware: () => ipcRenderer.invoke('studio:scan-hardware'),
  getRegistry: () => ipcRenderer.invoke('studio:registry'),
  listModels: () => ipcRenderer.invoke('studio:models'),
  importModel: model => ipcRenderer.invoke('studio:model-import', model),
  downloadModel: model => ipcRenderer.invoke('studio:model-download', model),
  activateModel: (task, id) => ipcRenderer.invoke('studio:model-activate', {task, id}),
  removeModel: id => ipcRenderer.invoke('studio:model-remove', id)
});
