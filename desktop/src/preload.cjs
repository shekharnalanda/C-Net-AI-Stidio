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
  selectVideoImages: () => ipcRenderer.invoke('studio:select-video-images'),
  selectVideoAudio: () => ipcRenderer.invoke('studio:select-video-audio'),
  selectVideoSubtitle: () => ipcRenderer.invoke('studio:select-video-subtitle'),
  saveSubtitle: (file,text) => ipcRenderer.invoke('studio:subtitle-save',{file,text}),
  imageData: file => ipcRenderer.invoke('studio:image-data',file),
  exportImage: file => ipcRenderer.invoke('studio:image-export',file),
  openVideo: file => ipcRenderer.invoke('studio:video-open',file),
  exportVideo: file => ipcRenderer.invoke('studio:video-export',file),
  listProjects: () => ipcRenderer.invoke('studio:projects'),
  createProject: name => ipcRenderer.invoke('studio:project-create',name)
});
