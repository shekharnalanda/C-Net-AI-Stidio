<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Templates | C-Net AI Studio</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#050812;color:#fff;font-family:Inter,system-ui,Arial}
.wrap{max-width:1450px;margin:auto;padding:32px}
.top{display:flex;justify-content:space-between;align-items:center;gap:20px}
.brand{font-size:28px;font-weight:900}.brand span{color:#62ddff}
.muted{color:#8b99b3}
.btn{display:inline-block;padding:11px 15px;border-radius:11px;background:#142039;color:#fff;text-decoration:none}
.hero{margin-top:25px;padding:32px;border-radius:25px;border:1px solid #263550;background:radial-gradient(circle at 90% 0,#45266e,transparent 35%),linear-gradient(135deg,#102449,#101329)}
.hero h1{font-size:42px;margin:8px 0}
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:17px;margin-top:25px}
.card{background:#0d1525;border:1px solid #1d2b44;border-radius:19px;padding:20px;display:flex;flex-direction:column;min-height:260px}
.icon{font-size:37px}.tag{font-size:11px;color:#65ddff;text-transform:uppercase;margin-top:14px}
.card h3{margin:7px 0}.card p{color:#8d9bb5;line-height:1.5;flex:1}
button{width:100%;padding:12px;border:0;border-radius:11px;color:#fff;font-weight:800;cursor:pointer;background:linear-gradient(90deg,#04c9ff,#7562ff,#df4bdc)}
@media(max-width:1050px){.grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:650px){.grid{grid-template-columns:1fr}.wrap{padding:18px}}
</style>
</head>
<body>
<div class="wrap">
<div class="top">
<div>
<div class="brand">C-Net <span>AI Studio</span></div>
<div class="muted">Professional Template Library</div>
</div>
<div>
<a class="btn" href="{{ route('studio.dashboard') }}">Dashboard</a>
<a class="btn" href="{{ route('projects.index') }}">My Projects</a>
</div>
</div>

<section class="hero">
<div class="muted">CREATIVE STARTING POINTS</div>
<h1>Start faster with <span style="color:#72ddff">AI-ready templates.</span></h1>
<p class="muted">Choose a workflow and continue in the full C-Net AI Studio editor.</p>
</section>

<div class="grid">
@foreach($templates as $key => $template)
<div class="card">
<div class="icon">{{ $template['icon'] }}</div>
<div class="tag">{{ strtoupper(str_replace('-',' ',$template['type'])) }} • {{ $template['ratio'] }}</div>
<h3>{{ $template['name'] }}</h3>
<p>{{ $template['description'] }}</p>
<form method="POST" action="{{ route('studio.templates.create',$key) }}">
@csrf
<button>Use Template →</button>
</form>
</div>
@endforeach
</div>
</div>
</body>
</html>
