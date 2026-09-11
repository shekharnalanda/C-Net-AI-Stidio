let registry, hardware, installed = [], task = 'text';
const tasks = [['text','Text & Script'],['image','Image'],['voice','Voice'],['caption','Captions'],['video','Video']];
const $ = selector => document.querySelector(selector);
const mode = () => document.querySelector('input[name=mode]:checked').value;
const installedModel = id => installed.find(model => model.id === id);
function compatibility(engine){const reasons=[];if(hardware.ramGb<engine.minRamGb)reasons.push(`${engine.minRamGb} GB RAM`);if(hardware.vramGb<engine.minVramGb)reasons.push(`${engine.minVramGb} GB VRAM`);if(hardware.freeDiskGb<engine.diskGb)reasons.push(`${engine.diskGb} GB disk`);if(engine.gpu!=='any'&&!hardware.gpuVendor.includes(engine.gpu))reasons.push(`${engine.gpu.toUpperCase()} GPU`);return reasons}
function message(text, error=false){$('#notice').textContent=text;$('#notice').classList.toggle('error',error)}
function render(){
 $('#hardware').innerHTML=`<strong>${hardware.ramGb} GB RAM</strong><span>${hardware.gpuName}</span><span>${hardware.vramGb} GB VRAM · ${hardware.freeDiskGb} GB free</span>`;
 $('#tools').innerHTML=tasks.map(([id,name])=>`<button class="${task===id?'selected':''}" data-task="${id}">${name}</button>`).join('');
 const choices=registry.engines.filter(e=>e.tasks.includes(task)).map(e=>({...e,reasons:compatibility(e)})).sort((a,b)=>(a.reasons.length-b.reasons.length)||(b.quality-a.quality));
 $('#engines').innerHTML=choices.length?choices.map((e,i)=>{const model=installedModel(e.id),active=model?.activeFor.includes(task),best=!e.reasons.length&&i===0;return `<article class="${best?'recommended':''}"><div><h3>${e.name}</h3><p>${e.reasons.length?'Not compatible: '+e.reasons.join(', '):'Compatible · '+e.diskGb+' GB package'} · ${model?'Installed':'Not installed'}</p></div><span>${active?'Active':best?'Recommended':e.quality+'/5 quality'}</span><div class="actions">${model?`<button data-activate="${e.id}" ${e.reasons.length||active?'disabled':''}>${active?'Selected':'Select'}</button><button class="secondary" data-remove="${e.id}" ${active?'disabled':''}>Remove</button>`:`<button data-install="${e.id}" ${e.reasons.length?'disabled':''}>${e.downloadUrl?'Install':'Import'}</button>`}</div></article>`}).join(''):'<p>No registered engine for this tool.</p>';
 document.querySelectorAll('[data-task]').forEach(b=>b.onclick=()=>{task=b.dataset.task;render()});
 document.querySelectorAll('[data-install]').forEach(b=>b.onclick=()=>install(b.dataset.install));
 document.querySelectorAll('[data-activate]').forEach(b=>b.onclick=()=>activate(b.dataset.activate));
 document.querySelectorAll('[data-remove]').forEach(b=>b.onclick=()=>remove(b.dataset.remove));
}
async function install(id){try{const engine=registry.engines.find(e=>e.id===id);if(!engine.sha256)throw new Error('Verified package manifest is not published yet.');const result=engine.downloadUrl?await window.studio.downloadModel({id,name:engine.name,url:engine.downloadUrl,sha256:engine.sha256}):await window.studio.importModel({id,name:engine.name,sha256:engine.sha256});if(result){installed=await window.studio.listModels();message(`${engine.name} verified and installed.`);render()}}catch(error){message(error.message,true)}}
async function activate(id){try{await window.studio.activateModel(task,id);installed=await window.studio.listModels();message('Engine selected for this tool.');render()}catch(error){message(error.message,true)}}
async function remove(id){try{await window.studio.removeModel(id);installed=await window.studio.listModels();message('Model removed safely.');render()}catch(error){message(error.message,true)}}
async function scan(){try{$('#status').textContent='Scanning system…';[registry,hardware,installed]=await Promise.all([window.studio.getRegistry(),window.studio.scanHardware(),window.studio.listModels()]);$('#status').textContent='System scan complete';render()}catch(error){message(error.message,true)}}
$('#rescan').onclick=scan;document.querySelectorAll('input[name=mode]').forEach(r=>r.onchange=render);scan();
