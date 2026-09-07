<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard | C-Net AI Studio</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#050811;color:#fff;font-family:Inter,system-ui,Arial}
.shell{display:grid;grid-template-columns:250px 1fr;min-height:100vh}
aside{background:#080d18;border-right:1px solid #172136;padding:25px;position:sticky;top:0;height:100vh}
.logo{width:78px;height:78px;border-radius:50%;display:grid;place-items:center;background:conic-gradient(#18ddff,#6f61ff,#ec50db,#ff983b,#18ddff);padding:4px;margin-bottom:14px}
.logo span{width:100%;height:100%;display:grid;place-items:center;border-radius:50%;background:#07101e;font-size:30px;font-weight:900}
.brand{font-size:21px;font-weight:900;margin-bottom:28px}.brand b{color:#5cddff}
nav a{display:block;padding:12px 14px;margin:4px 0;color:#9facbf;text-decoration:none;border-radius:12px}
nav a:hover,.active{background:#131c2f;color:white}
main{padding:30px 35px}.top{display:flex;justify-content:space-between;align-items:center;gap:20px}
.muted{color:#8d9bb6}.trial{padding:10px 14px;border:1px solid #1d5368;background:#102832;color:#65e3ff;border-radius:999px}
.hero{margin-top:24px;padding:32px;border:1px solid #273452;border-radius:26px;background:radial-gradient(circle at 85% 0,#482971 0,transparent 36%),linear-gradient(135deg,#10254c,#10152b)}
.hero h2{font-size:38px;margin:5px 0 10px}.gradient{background:linear-gradient(90deg,#20ddff,#8071ff,#ec5bd9);-webkit-background-clip:text;color:transparent}
.quick{display:flex;gap:10px;flex-wrap:wrap;margin-top:20px}
input,select{padding:13px 14px;border:1px solid #31415e;background:#091221;color:#fff;border-radius:12px}
button{padding:13px 18px;border:0;border-radius:12px;background:linear-gradient(90deg,#04c9ff,#7061ff,#d94ce9);color:white;font-weight:800}
.stats,.tools{display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-top:22px}
.stat,.tool{background:#0c1424;border:1px solid #1b2941;border-radius:18px;padding:19px}
.num{font-size:28px;font-weight:900}.tool{min-height:150px}.tool b{font-size:17px}.tool p{color:#8e9cb7;font-size:14px;line-height:1.5}.link{color:#55ddff;font-weight:700}
.success{margin-top:15px;padding:12px 15px;background:#113322;border:1px solid #1e6841;color:#82efaa;border-radius:12px}
@media(max-width:1000px){.tools,.stats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:720px){.shell{grid-template-columns:1fr}aside{display:none}main{padding:20px}.tools,.stats{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="shell">

<aside>
<div class="logo"><span>C</span></div>
<div class="brand">C-Net <b>AI Studio</b></div>

<nav>
<a class="active" href="{{ route('studio.dashboard') }}">Dashboard</a>
<a href="#">AI Video</a>
<a href="#">Image Studio</a>
<a href="#">Audio & Voice</a>
<a href="{{ route('studio.smart-tools') }}">AI Tools</a>
<a href="{{ route('projects.index') }}">My Projects</a>
<a href="{{ route('studio.templates') }}">Templates</a>
<a href="{{ route('studio.outputs') }}">Exports</a>
<a href="{{ route('studio.billing') }}">Plan & Usage</a>

@if($user->isAdmin())
<a href="{{ route('admin.dashboard') }}">Master Admin</a>
@endif
</nav>

<form method="POST" action="{{ route('logout') }}" style="margin-top:26px">
@csrf
<button type="submit">Sign Out</button>
</form>
</aside>

<main>

<div class="top">
<div>
<div class="muted">Welcome back</div>
<h1>{{ $user->name }}</h1>
</div>

<div class="trial">
@if($user->isAdmin())
Master Access
@elseif($user->isTrialActive())
Free Trial Active
@else
Subscription Required
@endif
</div>
</div>

@if(session('success'))
<div class="success">{{ session('success') }}</div>
@endif

<div class="hero">
<div class="muted">C-NET AI STUDIO V3</div>
<h2>Turn your <span class="gradient">idea into impact</span> with AI.</h2>
<p class="muted">Video, image, audio, advertising, reels and intelligent creative automation from one workspace.</p>

<form class="quick" method="POST" action="{{ route('projects.store') }}">
@csrf
<input name="name" placeholder="Project name">

<select name="type">
<option value="text-to-video">Text to Video</option>
<option value="image-to-video">Image to Video</option>
<option value="business-ad">Business Ad</option>
<option value="reel">Social Reel</option>
<option value="video">Video Editor</option>
</select>

<select name="aspect_ratio">
<option value="16:9">Landscape 16:9</option>
<option value="9:16">Vertical 9:16</option>
<option value="1:1">Square 1:1</option>
<option value="4:5">Portrait 4:5</option>
</select>

<button type="submit">Create Project</button>
</form>
</div>

<div class="stats">
<div class="stat"><div class="num">{{ $projectCount }}</div><div class="muted">Projects</div></div>
<div class="stat"><div class="num">{{ $jobCount }}</div><div class="muted">AI Jobs</div></div>
<div class="stat"><div class="num">{{ $onlineWorkers }}</div><div class="muted">AI Workers</div></div>
<div class="stat"><div class="num">{{ $plans->count() }}</div><div class="muted">Plans</div></div>
</div>

<h2>AI Creative Tools</h2>

<div class="tools">
<div class="tool"><b>Text to Video</b><p>Turn prompts and scripts into complete structured video projects.</p><button onclick="launchTool('text-to-video','16:9')">Create →</button></div>
<div class="tool"><b>Image to Video</b><p>Animate images and create cinematic or promotional visual sequences.</p><button onclick="launchTool('image-to-video','16:9')">Animate →</button></div>
<div class="tool"><b>Business Ad Studio</b><p>Create branded advertisements for businesses and institutions.</p><button onclick="launchTool('business-ad','16:9')">Build Ad →</button></div>
<div class="tool"><b>Smart Reels</b><p>Vertical short-form videos with smart captions and sequencing.</p><button onclick="launchTool('reel','9:16')">Create Reel →</button></div>
<div class="tool"><b>AI Voice Studio</b><p>Voiceover, narration and multilingual audio workflows.</p><a class="link" href="{{ route('studio.smart-tools') }}">Open Voice →</a></div>
<div class="tool"><b>Image AI</b><p>Thumbnails, backgrounds, enhancement and creative composition.</p><span class="link">Open Image AI →</span></div>
<div class="tool"><b>Auto Captions</b><p>Subtitle generation and synchronized caption workflows.</p><a class="link" href="{{ route('studio.smart-tools') }}">Generate →</a></div>
<div class="tool"><b>Pro Timeline Editor</b><p>Tracks, layers, titles, transitions, audio and effects.</p><button onclick="launchTool('video','16:9')">Open Editor →</button></div>
</div>

</main>
</div>

<form id="tool-launcher" method="POST" action="{{ route('projects.store') }}" style="display:none">
@csrf
<input id="tool-name" name="name">
<input id="tool-type" name="type">
<input id="tool-ratio" name="aspect_ratio">
</form>

<script>
function launchTool(type, ratio) {
    const titles = {
        'text-to-video':'Text to Video Project',
        'image-to-video':'Image to Video Project',
        'business-ad':'Business Advertisement',
        'reel':'Smart Reel',
        'video':'Professional Video Project'
    };

    document.getElementById('tool-name').value = titles[type] || 'AI Project';
    document.getElementById('tool-type').value = type;
    document.getElementById('tool-ratio').value = ratio;
    document.getElementById('tool-launcher').submit();
}
</script>

</body>
</html>
