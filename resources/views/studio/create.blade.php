<!doctype html>
<html lang="hi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Voice & Video | C-Net AI Studio</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#060b16;color:#edf3ff;font-family:Inter,system-ui,Arial;line-height:1.6}a{color:#6adfff;text-decoration:none}button,input,textarea,select{font:inherit}button,a,input,textarea,select{outline-offset:4px}.shell{max-width:1180px;margin:auto;padding:24px}.top{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}.brand{font-size:23px;font-weight:800}.brand span{color:#6adfff}.nav{display:flex;gap:18px;flex-wrap:wrap;font-size:14px}.hero{padding:35px 0 24px;max-width:760px}.eyebrow{color:#71e2c0;font-size:12px;letter-spacing:2px;text-transform:uppercase}h1{font-size:clamp(30px,5vw,46px);line-height:1.15;margin:12px 0}h2{font-size:22px;margin:0 0 8px}.muted{color:#a5b4cf}.tabs{display:flex;gap:10px;margin:0 0 20px}.tabs button{background:#111e35;border:1px solid #293f61;color:#a5b4cf}.tabs button[aria-selected=true]{background:#1b3956;color:#9aeaff;border-color:#62d6fc}.grid{display:grid;grid-template-columns:1.15fr 1fr;gap:24px;align-items:start}.card{padding:25px;background:linear-gradient(145deg,#11213a,#0e1627);border:1px solid #233552;border-radius:20px}.card p{margin:4px 0 20px}.fields{display:grid;grid-template-columns:1fr 1fr;gap:14px}label{display:block;margin:15px 0 7px;font-size:14px;font-weight:650}input:not([type=checkbox]),textarea,select{display:block;width:100%;padding:12px;color:#eaf2ff;border:1px solid #344b70;background:#080f1e;border-radius:10px}textarea{resize:vertical;min-height:170px}button,.download{padding:11px 18px;border-radius:10px;cursor:pointer;font-weight:750}.primary,.download{display:inline-block;background:linear-gradient(100deg,#20bddd,#796bfa);color:#fff;border:0}.primary{width:100%;margin-top:16px}button:disabled{opacity:.55;cursor:wait}.consent{display:flex;gap:10px;align-items:flex-start;font-weight:400;color:#a5b4cf;line-height:1.5}.consent input{margin-top:5px}.hint{font-size:12px;color:#a5b4cf;margin-top:8px}.result{min-height:230px;display:grid;align-content:center;text-align:center;padding:12px}.result audio,.result video{width:100%;max-height:400px;margin:18px 0}.result strong{font-size:19px}.status{padding:13px;margin-top:15px;background:#12283a;border-radius:10px;white-space:pre-wrap;overflow-wrap:anywhere}.error{background:#3b1d2a;color:#ffb9c9}.history{margin-top:34px}.history-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}.item{background:#0f192c;border:1px solid #233552;padding:18px;border-radius:14px;min-width:0;overflow-wrap:anywhere}.item h3{font-size:16px;margin:0 0 8px}.item audio,.item video{width:100%;max-height:230px;margin:14px 0}.badge{font-size:12px;color:#89d6ee}.selected-image{width:100%;max-height:240px;object-fit:contain;border-radius:10px;margin:12px 0}footer{margin:40px 0 8px;color:#778aa8;font-size:12px}[hidden]{display:none!important}@media(max-width:760px){.shell{padding:18px}.grid{grid-template-columns:1fr}.history-grid{grid-template-columns:1fr}.card{padding:19px}.fields{grid-template-columns:1fr}.hero{padding-top:24px}.nav{gap:14px}}
</style>
</head>
<body>
<div class="shell">
<header class="top"><a class="brand" href="{{ route('studio.dashboard') }}">C-Net <span>AI Studio</span></a><nav class="nav" aria-label="Studio navigation"><a href="{{ route('studio.dashboard') }}">Dashboard</a><a href="{{ route('projects.index') }}">Projects</a><a href="{{ route('studio.outputs') }}">Exports</a></nav></header>
<section class="hero"><div class="eyebrow">Create · Preview · Download</div><h1>आपकी आवाज। आपकी कहानी।</h1><p class="muted">हिंदी narration बनाएँ या अपनी तस्वीर को छोटे animated video में बदलें। परिणाम यहीं सुनें, देखें और डाउनलोड करें।</p></section>
<div class="tabs" role="tablist" aria-label="Creation type"><button id="voice-tab" role="tab" aria-controls="voice-panel" aria-selected="true" type="button">Hindi Voice</button><button id="video-tab" role="tab" aria-controls="video-panel" aria-selected="false" type="button">Image to Video</button></div>
<div class="grid">
<section class="card">
<div id="voice-panel" role="tabpanel" aria-labelledby="voice-tab">
<h2>Text to Speech</h2><p class="muted">Script डालें और प्राकृतिक हिंदी आवाज की MP3 बनाएँ।</p>
<form class="creation-form" method="POST" action="{{ route('studio.create.store') }}">@csrf
<input type="hidden" name="kind" value="voiceover">
<label for="voice-name">Creation name</label><input id="voice-name" name="name" value="Hindi narration" required maxlength="150">
<label for="voice-text">हिंदी script</label><textarea id="voice-text" name="text" required maxlength="5000" placeholder="यहाँ अपनी script लिखें…">नमस्कार! माइक्रो कंप्यूटर इंस्टिट्यूट में आपका स्वागत है। प्रैक्टिकल प्रशिक्षण और मार्गदर्शन के लिए हमसे जुड़ें।</textarea><div class="hint">अधिकतम 5,000 अक्षर • MP3 output</div>
<div class="fields"><div><label for="voice">Voice</label><select id="voice" name="voice"><option value="swara">Swara · Female · Hindi</option><option value="madhur">Madhur · Male · Hindi</option></select></div><div><label for="rate">Speaking speed</label><select id="rate" name="rate"><option value="-15">Slow</option><option value="0" selected>Normal</option><option value="15">Fast</option></select></div></div>
<label class="consent"><input type="checkbox" name="provider_consent" value="1" required><span>आवाज बनाने के लिए यह text Hugging Face की सेवा को भेजने की अनुमति है।</span></label>
<button class="primary" type="submit">Generate Voice →</button></form>
</div>
<div id="video-panel" role="tabpanel" aria-labelledby="video-tab" hidden>
<h2>Image to Video</h2><p class="muted">हल्की natural motion और camera movement वाले छोटे clips बनाएँ।</p>
<form class="creation-form" method="POST" enctype="multipart/form-data" action="{{ route('studio.create.store') }}">@csrf
<input type="hidden" name="kind" value="image-to-video">
<label for="video-name">Creation name</label><input id="video-name" name="name" value="Animated image" required maxlength="150">
<label for="image">Your image</label><input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" required><div class="hint">JPG, PNG या WebP • अधिकतम 6 MB और 4096 × 4096 pixels</div><img id="image-preview" class="selected-image" alt="Selected image preview" hidden>
<label for="prompt">Movement description</label><textarea id="prompt" name="prompt" required maxlength="1500" style="min-height:105px">Gentle natural movement, subtle camera motion. Preserve the subject's identity and appearance.</textarea>
<label for="duration">Clip length</label><select id="duration" name="duration"><option value="1">1 second · quick test</option><option value="2">2 seconds</option><option value="3" selected>3 seconds</option><option value="4">4 seconds</option><option value="5">5 seconds</option></select><div class="hint">MP4 output • Silent clip • यह सुविधा आवाज या lip-sync नहीं जोड़ती।</div>
<label class="consent"><input type="checkbox" name="provider_consent" value="1" required><span>मेरे पास इस तस्वीर के उपयोग की अनुमति है। वीडियो बनाने के लिए तस्वीर और description Hugging Face की सेवा को भेज सकते हैं।</span></label>
<button class="primary" type="submit">Animate Image →</button></form>
</div>
<div id="message" class="status" role="status" aria-live="polite" hidden></div>
</section>
<section class="card"><h2>Your result</h2><p class="muted">सफल होने पर preview और download यहाँ आएगा।</p><div id="result" class="result"><strong>कुछ नया बनाएँ</strong><span class="muted">आवाज के लिए script, वीडियो के लिए तस्वीर चुनें।</span></div><div class="hint">काम शुरू होने में लगभग एक मिनट लग सकता है। आप पेज छोड़कर बाद में लौट सकते हैं। मुफ्त सेवा की उपलब्धता और सीमा के अनुसार समय बदल सकता है।</div></section>
</div>
<section class="history"><h2>Recent creations</h2><p class="muted">आपके खाते के हाल के परिणाम</p><div id="history" class="history-grid">@forelse($jobs as $job)<article class="item" data-status-url="{{ route('studio.create.status', $job) }}"><h3>{{ $job->project?->name }}</h3><span class="badge">{{ $job->job_type === 'voiceover' ? 'Hindi Voice' : 'Image to Video' }}</span><div class="job-result"><p class="muted">{{ $job->status }}</p></div></article>@empty<p id="empty-history" class="muted">आपकी पहली creation यहाँ दिखाई देगी।</p>@endforelse</div></section>
<footer>C-Net AI Studio · आपकी generated files खाते में सुरक्षित रहती हैं। अन्य creative tools पर काम जारी है।</footer>
</div>
<script>
const message = document.getElementById('message');
const result = document.getElementById('result');
const buttons = [...document.querySelectorAll('.creation-form button[type=submit]')];
function selectTab(video) {
    document.getElementById('video-panel').hidden = !video;
    document.getElementById('voice-panel').hidden = video;
    document.getElementById('voice-tab').setAttribute('aria-selected', String(!video));
    document.getElementById('video-tab').setAttribute('aria-selected', String(video));
}
document.getElementById('voice-tab').addEventListener('click',()=>selectTab(false));
document.getElementById('video-tab').addEventListener('click',()=>selectTab(true));
selectTab(new URLSearchParams(location.search).get('type') === 'image-to-video');
let imageUrl;
document.getElementById('image').addEventListener('change', event => {
    const preview = document.getElementById('image-preview');
    if (imageUrl) URL.revokeObjectURL(imageUrl);
    preview.hidden = !event.target.files.length;
    if (!preview.hidden) {imageUrl = URL.createObjectURL(event.target.files[0]); preview.src = imageUrl;}
});
function showMessage(text,error=false) {message.hidden=false;message.textContent=text;message.classList.toggle('error',error);}
function renderResult(container,data) {
    container.replaceChildren();
    if (data.status === 'completed' && data.preview_url) {
        const player = document.createElement(data.type === 'audio' ? 'audio' : 'video');
        player.controls=true;player.preload='metadata';player.src=data.preview_url;
        if(data.type==='video') player.playsInline=true;
        const download=document.createElement('a');download.className='download';download.href=data.download_url;download.textContent='Download '+(data.type==='audio'?'MP3':'MP4');
        container.append(player,download);
    } else {
        const status=document.createElement('p');status.className='muted';
        status.textContent=data.status==='failed'?data.error:(data.status==='pending'?'Waiting to start…':'Generating… Please wait.');
        container.append(status);
    }
}
async function poll(url,container,active=false,attempt=0) {
    try {
        const response=await fetch(url,{headers:{Accept:'application/json'}});
        if(!response.ok) throw new Error('Session expired or status unavailable. Please refresh and sign in again.');
        const data=await response.json();renderResult(container,data);
        if(active) {
            renderResult(result,data);
            if(['completed','failed'].includes(data.status)) {buttons.forEach(b=>b.disabled=false);showMessage(data.status==='completed'?'Creation ready. Preview or download your file.':data.error,data.status==='failed');}
            else showMessage(data.status==='pending'?'Request saved. Waiting for generation to start…':'Generating your creation. You can return later to see the result.');
        }
        if(!['completed','failed'].includes(data.status) && attempt<180) setTimeout(()=>poll(url,container,active,attempt+1),5000);
        else if(attempt>=180 && active) {buttons.forEach(b=>b.disabled=false);showMessage('Your creation is still in progress. Refresh later to check its result.');}
    } catch(error) {if(active){buttons.forEach(b=>b.disabled=false);showMessage(error.message,true);}}
}
document.querySelectorAll('[data-status-url]').forEach(card=>poll(card.dataset.statusUrl,card.querySelector('.job-result')));
document.querySelectorAll('.creation-form').forEach(form=>form.addEventListener('submit',async event=>{
    event.preventDefault();buttons.forEach(b=>b.disabled=true);showMessage('Saving your request…');
    try {
        const response=await fetch(form.action,{method:'POST',body:new FormData(form),headers:{Accept:'application/json'}});
        const data=await response.json();
        if(!response.ok) throw new Error(data.errors?Object.values(data.errors).flat().join('\n'):'Could not save your request. Refresh and try again.');
        document.getElementById('empty-history')?.remove();
        const card=document.createElement('article');card.className='item';
        const title=document.createElement('h3');title.textContent=form.elements.name.value;
        const content=document.createElement('div');card.append(title,content);document.getElementById('history').prepend(card);
        poll(data.status_url,content,true);
    } catch(error) {buttons.forEach(b=>b.disabled=false);showMessage(error.message,true);}
}));
</script>
</body>
</html>
