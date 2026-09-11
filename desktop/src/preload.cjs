const {contextBridge, ipcRenderer} = require('electron');
contextBridge.exposeInMainWorld('studio', {
  scanHardware: () => ipcRenderer.invoke('studio:scan-hardware'),
  getRegistry: () => ipcRenderer.invoke('studio:registry'),
  listModels: () => ipcRenderer.invoke('studio:models'),
  importModel: id => ipcRenderer.invoke('studio:model-import', id),
  downloadModel: id => ipcRenderer.invoke('studio:model-download', id),
  activateModel: (task, id) => ipcRenderer.invoke('studio:model-activate', {task, id}),
  removeModel: id => ipcRenderer.invoke('studio:model-remove', id),
  engineStatus: id => ipcRenderer.invoke('studio:engine-status', id),
  generate: (engineId, input) => ipcRenderer.invoke('studio:generate', {engineId, input}),
  installRuntime: id => ipcRenderer.invoke('studio:runtime-install',id),
  selectMedia: () => ipcRenderer.invoke('studio:select-media'),
  saveSubtitle: (file,text) => ipcRenderer.invoke('studio:subtitle-save',{file,text})
});
