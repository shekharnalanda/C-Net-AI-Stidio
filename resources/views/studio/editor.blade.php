<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $project->name }} | C-Net AI Studio</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#05070d;color:#fff;font-family:Inter,system-ui,Arial;overflow:hidden}
.app{height:100vh;display:grid;grid-template-rows:62px 1fr 230px}
.top{display:flex;align-items:center;justify-content:space-between;padding:0 18px;background:#0a0f1b;border-bottom:1px solid #1a2437}
.brand{font-weight:900;font-size:19px}.brand span{color:#59ddff}
.actions{display:flex;gap:8px}
button,.btn{border:0;padding:10px 14px;border-radius:10px;background:#182238;color:white;font-weight:700;text-decoration:none;cursor:pointer}
.primary{background:linear-gradient(90deg,#04c9ff,#7162ff,#d94ce8)}
.workspace{display:grid;grid-template-columns:260px 1fr 300px;min-height:0}
.left,.right{background:#090e18;padding:14px;overflow:auto}
.left{border-right:1px solid #182235}.right{border-left:1px solid #182235}
.center{background:#020407;display:flex;align-items:center;justify-content:center;padding:20px;overflow:auto}
.canvas{width:min(820px,90%);aspect-ratio:16/9;background:radial-gradient(circle at 70% 20%,#432963 0,transparent 35%),linear-gradient(135deg,#101c34,#080d18);border:1px solid #27314a;border-radius:12px;box-shadow:0 30px 100px #000;display:grid;place-items:center;text-align:center}
.canvas h2{font-size:32px;margin:0}
.section{margin-bottom:20px}.section h3{font-size:13px;text-transform:uppercase;color:#7788a5;letter-spacing:.08em}
.tool{display:block;padding:11px;background:#111928;border:1px solid #1d2a41;border-radius:10px;margin:7px 0;color:white;text-decoration:none}
input,textarea,select{width:100%;padding:11px;border-radius:9px;border:1px solid #283751;background:#090f1c;color:white;margin:5px 0}
textarea{min-height:120px;resize:vertical}
.media{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.media-item{height:85px;background:#111b2b;border:1px solid #22304a;border-radius:9px;display:grid;place-items:center;font-size:12px;text-align:center;overflow:hidden}
.media-item img{width:100%;height:100%;object-fit:cover}
.timeline{background:#090e18;border-top:1px solid #1b2639;padding:12px;overflow:auto}
.timeline-head{display:flex;justify-content:space-between;margin-bottom:10px;color:#8595b0}
.track{height:42px;background:#0c1423;border:1px solid #19263c;border-radius:8px;margin:6px 0;display:flex;align-items:center}
.track-name{width:85px;padding-left:10px;color:#8292ad;font-size:12px}
.clip{height:30px;min-width:180px;margin-left:5px;border-radius:6px;background:linear-gradient(90deg,#174c70,#493b87);display:flex;align-items:center;padding:0 9px;font-size:12px}
.success{padding:9px;background:#103321;color:#7ef0a6;border-radius:8px;margin-bottom:10px}
.job{font-size:12px;padding:9px;background:#101827;border-radius:8px;margin:6px 0}
.status{color:#5fe0ff}
@media(max-width:1000px){.workspace{grid-template-columns:210px 1fr}.right{display:none}}
</style>
</head>
<body>

<div class="app">

<header class="top">
<div>
<div class="brand">C-Net <span>AI Studio</span></div>
<div style="font-size:12px;color:#8090aa">{{ $project->name }}</div>
</div>

<div class="actions">
<a class="btn" href="{{ route('projects.index') }}">Projects</a>
<a class="btn" href="{{ route('studio.dashboard') }}">Dashboard</a>
<button class="primary" form="project-settings">Save Project</button>
</div>
</header>

<div class="workspace">

<aside class="left">

@if(session('success'))
<div class="success">{{ session('success') }}</div>
@endif

<div class="section">
<h3>AI Creation</h3>

<form method="POST" action="{{ route('projects.generate',$project) }}">
@csrf

<textarea name="prompt" placeholder="Describe what you want C-Net AI Studio to create..." required>{{ $project->prompt }}</textarea>

<select name="duration">
<option value="15">15 seconds</option>
<option value="30" selected>30 seconds</option>
<option value="45">45 seconds</option>
<option value="60">60 seconds</option>
<option value="90">90 seconds</option>
</select>

<select name="style">
<option value="professional">Professional</option>
<option value="cinematic">Cinematic</option>
<option value="corporate">Corporate</option>
<option value="education">Education</option>
<option value="social">Social Media</option>
</select>

<select name="voice">
<option value="auto">Auto Voice</option>
<option value="male">Male Voice</option>
<option value="female">Female Voice</option>
<option value="none">No Voice</option>
</select>

<button class="primary" style="width:100%;margin-top:7px">Generate with AI</button>
</form>
</div>

<div class="section">
<h3>Tools</h3>
<a class="tool">▶ Text to Video</a>
<a class="tool">▧ Image to Video</a>
<a class="tool">◉ Business Ad</a>
<a class="tool">✦ Smart Reel</a>
<a class="tool">♫ Voice & Audio</a>
<a class="tool">CC Auto Captions</a>
</div>

<div class="section">
<h3>Media Library</h3>

<form method="POST" action="{{ route('media.store',$project) }}" enctype="multipart/form-data">
@csrf
<input type="file" name="media" required>
<button style="width:100%">Upload Media</button>
</form>

<div class="media" style="margin-top:10px">
@foreach($media as $item)
<div class="media-item">
@if($item->media_type === 'image')
<img src="{{ asset('storage/'.$item->path) }}">
@else
{{ strtoupper($item->media_type) }}
@endif
</div>
@endforeach
</div>

</div>
</aside>

<main class="center">

<div class="canvas" id="previewCanvas">
<div>
<div style="font-size:13px;color:#7485a2">LIVE CREATIVE PREVIEW</div>
<h2>{{ $project->name }}</h2>
<p style="color:#93a2bd">
{{ strtoupper(str_replace('-', ' ', $project->type)) }} •
{{ $project->aspect_ratio }} •
{{ strtoupper($project->quality) }}
</p>
<div style="margin-top:22px;color:#64dcff">
AI-FIRST EDITOR V3
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
<select name="aspect_ratio">
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

@forelse($jobs as $job)
<div class="job">
<div>{{ strtoupper(str_replace('-',' ',$job->job_type)) }}</div>
<div class="status">{{ strtoupper($job->status) }} • {{ $job->progress }}%</div>
</div>
@empty
<div style="color:#71829f;font-size:13px">No AI jobs yet.</div>
@endforelse
</div>

</aside>
</div>

<div class="timeline">
<div class="timeline-head">
<span>PRO TIMELINE</span>
<span>00:00 / 00:30</span>
</div>

<div class="track">
<div class="track-name">VIDEO</div>
<div class="clip">Main Video Track</div>
</div>

<div class="track">
<div class="track-name">TEXT</div>
<div class="clip">Titles / Captions</div>
</div>

<div class="track">
<div class="track-name">AUDIO</div>
<div class="clip">Voice / Music</div>
</div>

<div class="track">
<div class="track-name">EFFECTS</div>
<div class="clip">Transitions / AI Effects</div>
</div>
</div>

</div>

<script>
const ratioSelect = document.querySelector('select[name="aspect_ratio"]');
const canvas = document.getElementById('previewCanvas');

function applyRatio() {
    const ratio = ratioSelect?.value || '{{ $project->aspect_ratio }}';
    canvas.style.aspectRatio = ratio.replace(':','/');
}

ratioSelect?.addEventListener('change', applyRatio);
applyRatio();
</script>

</body>
</html>
