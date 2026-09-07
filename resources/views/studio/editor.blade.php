<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $project->name }} | C-Net AI Studio</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#03050a;color:#fff;font-family:Inter,system-ui,Arial;overflow:hidden}
.app{height:100vh;display:grid;grid-template-rows:64px 1fr 240px}
.top{display:flex;align-items:center;justify-content:space-between;padding:0 18px;background:#080d17;border-bottom:1px solid #1a263b}
.brand{font-weight:900;font-size:19px}.brand span{color:#61ddff}.sub{font-size:11px;color:#7f8da7}
.actions{display:flex;gap:8px;align-items:center}
button,.btn{border:0;padding:10px 13px;border-radius:9px;background:#172238;color:white;font-weight:700;text-decoration:none;cursor:pointer}
.primary{background:linear-gradient(90deg,#02c7ff,#7360ff,#df4ddd)}
.workspace{display:grid;grid-template-columns:285px 1fr 310px;min-height:0}
.left,.right{background:#080d17;padding:13px;overflow:auto}.left{border-right:1px solid #182338}.right{border-left:1px solid #182338}
.center{background:#010204;display:flex;align-items:center;justify-content:center;padding:22px;overflow:auto}
.canvas{width:min(850px,92%);aspect-ratio:16/9;background:radial-gradient(circle at 75% 15%,#48276e 0,transparent 34%),linear-gradient(135deg,#102449,#070b14);border:1px solid #263550;border-radius:15px;box-shadow:0 35px 110px #000;display:grid;place-items:center;text-align:center;position:relative}
.canvas h2{font-size:34px;margin:4px}.badge{position:absolute;top:12px;left:12px;padding:7px 10px;border-radius:8px;background:#091321cc;font-size:10px;color:#75e0ff}
.section{margin-bottom:19px}.section h3{font-size:11px;text-transform:uppercase;color:#7b8aa5;letter-spacing:.09em}
.tabs{display:flex;gap:5px;margin-bottom:12px}.tab{flex:1;padding:9px 4px;font-size:11px}.tab.active{background:#213250;color:#71e0ff}
.panel{display:none}.panel.active{display:block}
input,textarea,select{width:100%;padding:10px;border-radius:8px;border:1px solid #263752;background:#080f1c;color:white;margin:4px 0}
textarea{min-height:115px;resize:vertical}
label{font-size:11px;color:#8392aa}
.media{display:grid;grid-template-columns:1fr 1fr;gap:7px}.media-item{height:82px;background:#101929;border:1px solid #21304a;border-radius:8px;display:grid;place-items:center;font-size:11px;overflow:hidden}.media-item img{width:100%;height:100%;object-fit:cover}
.job{padding:9px;background:#101827;border-radius:8px;margin:6px 0;font-size:11px}.status{color:#5ce0ff}
.progress{height:4px;background:#202c40;border-radius:8px;margin-top:6px;overflow:hidden}.progress span{display:block;height:100%;background:linear-gradient(90deg,#00d0ff,#9b59ff);width:0}
.timeline{background:#080d17;border-top:1px solid #1b273b;padding:11px;overflow:auto}
.timeline-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;color:#8190a9}
.track{height:39px;background:#0c1423;border:1px solid #18263a;border-radius:7px;margin:5px 0;display:flex;align-items:center}.track-name{width:90px;padding-left:9px;color:#8291aa;font-size:11px}.clip{height:28px;min-width:210px;border-radius:6px;background:linear-gradient(90deg,#174c70,#4b3d88);display:flex;align-items:center;padding:0 9px;font-size:11px}
.success{padding:8px;background:#103321;color:#7ceea4;border-radius:8px;margin-bottom:8px}
.save-state{font-size:11px;color:#74dda0}
.brand-preview{padding:10px;border:1px solid #253651;border-radius:9px;background:#0c1525;margin-top:7px}
@media(max-width:1050px){.workspace{grid-template-columns:230px 1fr}.right{display:none}}
</style>
</head>
<body>
<div class="app">

<header class="top">
<div>
<div class="brand">C-Net <span>AI Studio</span> <small style="color:#667691">V4 Workspace</small></div>
<div class="sub">{{ strtoupper(str_replace('-',' ',$project->type)) }} • {{ $project->name }}</div>
</div>

<div class="actions">
<span id="saveState" class="save-state">Ready</span>
<a class="btn" href="{{ route('studio.templates') }}">Templates</a>
<a class="btn" href="{{ route('projects.index') }}">Projects</a>
<form method="POST" action="{{ route('projects.duplicate',$project) }}">
@csrf
<button type="submit">Duplicate</button>
</form>
<button class="primary" form="project-settings">Save</button>
</div>
</header>

<div class="workspace">

<aside class="left">
@if(session('success'))<div class="success">{{ session('success') }}</div>@endif

<div class="tabs">
<button class="tab active" data-tab="create">Create</button>
<button class="tab" data-tab="media">Media</button>
<button class="tab" data-tab="brand">Brand</button>
</div>

<div id="panel-create" class="panel active">
<div class="section">
<h3>AI Creation Engine</h3>

<form method="POST" action="{{ route('projects.generate',$project) }}">
@csrf

<textarea name="prompt" placeholder="Describe your video, advertisement, reel or story..." required>{{ $project->prompt }}</textarea>

<label>Duration</label>
<select name="duration">
@foreach([15,30,45,60,90,120] as $seconds)
<option value="{{ $seconds }}" @selected(($project->generation_settings['duration'] ?? 30)===$seconds)>{{ $seconds }} seconds</option>
@endforeach
</select>

<label>Creative Style</label>
<select name="style" id="generationStyle">
@foreach(['professional','cinematic','corporate','education','social'] as $style)
<option value="{{ $style }}" @selected(($project->generation_settings['style'] ?? 'professional')===$style)>{{ ucfirst($style) }}</option>
@endforeach
</select>

<label>Voice</label>
<select name="voice">
<option value="auto">Auto Voice</option>
<option value="male">Male Voice</option>
<option value="female">Female Voice</option>
<option value="none">No Voice</option>
</select>

<button class="primary" style="width:100%;margin-top:7px">✦ Generate with AI</button>
</form>
</div>

<div class="section">
<h3>Smart Creative Tools</h3>
<button style="width:100%;margin:3px 0">Script Assistant</button>
<button style="width:100%;margin:3px 0">Scene Planner</button>
<button style="width:100%;margin:3px 0">Auto Captions</button>
<button style="width:100%;margin:3px 0">Voice & Audio</button>
<button style="width:100%;margin:3px 0">Smart Resize</button>
</div>
</div>

<div id="panel-media" class="panel">
<div class="section">
<h3>Media Library</h3>
<form method="POST" action="{{ route('media.store',$project) }}" enctype="multipart/form-data">
@csrf
<input type="file" name="media" required>
<button style="width:100%">Upload Media</button>
</form>

<div class="media" style="margin-top:9px">
@forelse($media as $item)
<div class="media-item">
@if($item->media_type === 'image')
<img src="{{ asset('storage/'.$item->path) }}">
@else
{{ strtoupper($item->media_type) }}
@endif
</div>
@empty
<div style="color:#73829c;font-size:12px">No uploaded media yet.</div>
@endforelse
</div>
</div>
</div>

<div id="panel-brand" class="panel">
<div class="section">
<h3>Brand Kit</h3>
<label>Brand / Business Name</label>
<input id="brandName" value="{{ $project->brand_settings['name'] ?? '' }}" placeholder="Your brand name">

<label>Primary Brand Color</label>
<input id="brandColor" type="color" value="{{ $project->brand_settings['primary_color'] ?? '#25cfff' }}">

<label>Call To Action</label>
<input id="brandCta" value="{{ $project->brand_settings['cta'] ?? '' }}" placeholder="Admissions Open / Call Now / Shop Now">

<label>Website / Contact</label>
<input id="brandContact" value="{{ $project->brand_settings['contact'] ?? '' }}" placeholder="Website or phone">

<div class="brand-preview">
<div class="sub">BRAND PREVIEW</div>
<strong id="brandPreviewName">{{ $project->brand_settings['name'] ?? 'Your Brand' }}</strong>
</div>

<button type="button" class="primary" style="width:100%;margin-top:8px" onclick="saveWorkspace()">Save Brand Kit</button>
</div>
</div>
</aside>

<main class="center">
<div class="canvas" id="previewCanvas">
<div class="badge">LIVE CREATIVE PREVIEW</div>
<div>
<div class="sub">C-NET AI STUDIO</div>
<h2 id="previewTitle">{{ $project->name }}</h2>
<p style="color:#94a3bd">
{{ strtoupper(str_replace('-',' ',$project->type)) }} •
{{ $project->aspect_ratio }} •
{{ strtoupper($project->quality) }}
</p>
<div id="previewBrand" style="margin-top:17px;color:#65ddff">
{{ $project->brand_settings['name'] ?? 'AI-FIRST CREATIVE WORKSPACE' }}
</div>
</div>
</div>
</main>

<aside class="right">

<div class="section">
<h3>Project Settings</h3>
<form id="project-settings" method="POST" action="{{ route('projects.update',$project) }}">
@csrf
@method('PUT')

<label>Name</label>
<input name="name" value="{{ $project->name }}" required>

<label>Aspect Ratio</label>
<select name="aspect_ratio" id="ratioSelect">
<option @selected($project->aspect_ratio==='16:9') value="16:9">16:9 Landscape</option>
<option @selected($project->aspect_ratio==='9:16') value="9:16">9:16 Vertical</option>
<option @selected($project->aspect_ratio==='1:1') value="1:1">1:1 Square</option>
<option @selected($project->aspect_ratio==='4:5') value="4:5">4:5 Portrait</option>
</select>

<label>Language</label>
<select name="language">
<option @selected($project->language==='en') value="en">English</option>
<option @selected($project->language==='hi') value="hi">Hindi</option>
</select>

<label>Quality</label>
<select name="quality">
<option @selected($project->quality==='sd') value="sd">SD</option>
<option @selected($project->quality==='hd') value="hd">HD</option>
<option @selected($project->quality==='full-hd') value="full-hd">Full HD</option>
</select>

<label>Editor Mode</label>
<select name="editor_mode">
<option @selected($project->editor_mode==='smart') value="smart">Smart AI</option>
<option @selected($project->editor_mode==='pro') value="pro">Professional</option>
</select>

<input type="hidden" name="prompt" value="{{ $project->prompt }}">
</form>
</div>

<div class="section">
<h3>AI Job Queue</h3>

<div id="jobsContainer">
@forelse($jobs as $job)
<div class="job" data-job="{{ $job->id }}" data-url="{{ route('jobs.status',$job) }}">
<div>{{ strtoupper(str_replace('-',' ',$job->job_type)) }}</div>
<div class="status job-status">{{ strtoupper($job->status) }} • <span>{{ $job->progress }}</span>%</div>
<div class="progress"><span style="width:{{ $job->progress }}%"></span></div>
</div>
@empty
<div style="color:#71819c;font-size:12px">No AI jobs yet.</div>
@endforelse
</div>
</div>

</aside>
</div>

<div class="timeline">
<div class="timeline-head">
<span>PRO MULTI-TRACK TIMELINE</span>
<span id="timelineDuration">00:00 / {{ sprintf('%02d:%02d', intdiv(($project->timeline['duration'] ?? 30),60), (($project->timeline['duration'] ?? 30)%60)) }}</span>
</div>

@php
$tracks = $project->timeline['tracks'] ?? [
['id'=>'video-1','type'=>'video','items'=>[]],
['id'=>'text-1','type'=>'text','items'=>[]],
['id'=>'audio-1','type'=>'audio','items'=>[]],
];
@endphp

@foreach($tracks as $track)
<div class="track">
<div class="track-name">{{ strtoupper($track['type'] ?? 'TRACK') }}</div>
<div class="clip">{{ ucfirst($track['type'] ?? 'Media') }} Track</div>
</div>
@endforeach
</div>

</div>

<script>
const csrf = @json(csrf_token());
const autosaveUrl = @json(route('projects.autosave',$project));
const currentTimeline = @json($project->timeline ?? []);
const ratio = document.getElementById('ratioSelect');
const canvas = document.getElementById('previewCanvas');
const saveState = document.getElementById('saveState');

document.querySelectorAll('.tab').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('panel-' + btn.dataset.tab).classList.add('active');
    });
});

function applyRatio() {
    canvas.style.aspectRatio = (ratio?.value || '16:9').replace(':','/');
}
ratio?.addEventListener('change', applyRatio);
applyRatio();

document.querySelector('input[name="name"]')?.addEventListener('input', e => {
    document.getElementById('previewTitle').textContent = e.target.value;
});

document.getElementById('brandName')?.addEventListener('input', e => {
    document.getElementById('brandPreviewName').textContent = e.target.value || 'Your Brand';
    document.getElementById('previewBrand').textContent = e.target.value || 'AI-FIRST CREATIVE WORKSPACE';
});

document.getElementById('brandColor')?.addEventListener('input', e => {
    document.getElementById('previewBrand').style.color = e.target.value;
});

async function saveWorkspace() {
    saveState.textContent = 'Saving...';

    const body = {
        timeline: currentTimeline,
        brand_settings: {
            name: document.getElementById('brandName')?.value || '',
            primary_color: document.getElementById('brandColor')?.value || '#25cfff',
            cta: document.getElementById('brandCta')?.value || '',
            contact: document.getElementById('brandContact')?.value || ''
        },
        generation_settings: {
            style: document.getElementById('generationStyle')?.value || 'professional'
        }
    };

    try {
        const res = await fetch(autosaveUrl, {
            method: 'POST',
            headers: {
                'Content-Type':'application/json',
                'Accept':'application/json',
                'X-CSRF-TOKEN':csrf
            },
            body: JSON.stringify(body)
        });

        if (!res.ok) throw new Error('Save failed');

        saveState.textContent = 'Saved';
        setTimeout(() => saveState.textContent = 'Ready', 1800);
    } catch(e) {
        saveState.textContent = 'Save failed';
    }
}

setInterval(saveWorkspace, 60000);

async function pollJobs() {
    const jobs = document.querySelectorAll('.job[data-url]');

    for (const el of jobs) {
        try {
            const res = await fetch(el.dataset.url, {
                headers:{'Accept':'application/json'}
            });

            if (!res.ok) continue;

            const data = await res.json();
            const status = String(data.status || '').toUpperCase();
            const progress = Number(data.progress || 0);

            el.querySelector('.job-status').innerHTML =
                status + ' • <span>' + progress + '</span>%';

            el.querySelector('.progress span').style.width = progress + '%';
        } catch(e) {}
    }
}

setInterval(pollJobs, 5000);
</script>
</body>
</html>
