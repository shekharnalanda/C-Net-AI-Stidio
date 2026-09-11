let registry, hardware, task = 'text';
const tasks = [['text','Text & Script'],['image','Image'],['voice','Voice'],['caption','Captions'],['video','Video']];
const $ = selector => document.querySelector(selector);
const mode = () => document.querySelector('input[name=mode]:checked').value;
function compatible(engine){const reasons=[]; if(hardware.ramGb<engine.minRamGb)reasons.push(`${engine.minRamGb} GB RAM`);if(hardware.vramGb<engine.minVramGb)reasons.push(`${engine.minVramGb} GB VRAM`);if(hardware.freeDiskGb<engine.diskGb)reasons.push(`${engine.diskGb} GB disk`);if(engine.gpu!=='any'&&!hardware.gpuVendor.includes(engine.gpu))reasons.push(`${engine.gpu.toUpperCase()} GPU`);return reasons}
function render(){
 $('#hardware').innerHTML=`<strong>${hardware.ramGb} GB RAM</strong><span>${hardware.gpuName}</span><span>${hardware.vramGb} GB VRAM · ${hardware.freeDiskGb} GB free</span>`;
 $('#tools').innerHTML=tasks.map(([id,name])=>`<button class="${task===id?'selected':''}" data-task="${id}">${name}</button>`).join('');
 const choices=registry.engines.filter(e=>e.tasks.includes(task)).map(e=>({...e,reasons:compatible(e)})).sort((a,b)=>(a.reasons.length-b.reasons.length)||(b.quality-a.quality));
 $('#engines').innerHTML=choices.length?choices.map((e,i)=>`<article class="${!e.reasons.length&&i===0?'recommended':''}"><div><h3>${e.name}</h3><p>${e.reasons.length?'Not compatible: '+e.reasons.join(', '):'Compatible with this system'}</p></div><span>${!e.reasons.length&&i===0?'Recommended':e.quality+'/5 quality'}</span><button ${e.reasons.length||mode()==='auto'?'disabled':''}>Select</button></article>`).join(''):'<p>No registered engine for this tool.</p>';
 document.querySelectorAll('[data-task]').forEach(b=>b.onclick=()=>{task=b.dataset.task;render()});
}
async function scan(){ $('#status').textContent='Scanning system…'; [registry,hardware]=await Promise.all([window.studio.getRegistry(),window.studio.scanHardware()]); $('#status').textContent='System scan complete'; render(); }
$('#rescan').onclick=scan; document.querySelectorAll('input[name=mode]').forEach(r=>r.onchange=render); scan();
