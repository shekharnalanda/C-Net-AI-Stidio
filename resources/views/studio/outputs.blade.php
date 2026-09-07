<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Outputs | C-Net AI Studio</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#050812;color:#fff;font-family:Inter,system-ui,Arial}
.wrap{max-width:1450px;margin:auto;padding:30px}
.top{display:flex;justify-content:space-between;align-items:center;gap:20px}
.brand{font-size:27px;font-weight:900}.brand span{color:#60ddff}
.muted{color:#8998b3}
.btn{display:inline-block;padding:11px 14px;border-radius:10px;background:#15213a;color:white;text-decoration:none}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:24px}
.card{padding:18px;border-radius:17px;background:#0d1525;border:1px solid #1b2942}
.num{font-size:29px;font-weight:900}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:25px}
.output{background:#0c1423;border:1px solid #1c2b45;border-radius:18px;overflow:hidden}
.preview{height:185px;background:radial-gradient(circle at 80% 15%,#4b286f,transparent 35%),linear-gradient(135deg,#102448,#070b14);display:grid;place-items:center;font-size:45px}
.body{padding:16px}.tag{font-size:10px;color:#62ddff;text-transform:uppercase}.body h3{margin:7px 0}.meta{font-size:12px;color:#8795ad;line-height:1.7}
.download{display:block;margin-top:12px;text-align:center;padding:11px;border-radius:10px;background:linear-gradient(90deg,#03c9ff,#7361ff,#dd4de0);color:#fff;text-decoration:none;font-weight:800}
.empty{padding:40px;text-align:center;color:#7b8aa5}
@media(max-width:1000px){.grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:650px){.stats,.grid{grid-template-columns:1fr}.wrap{padding:18px}}
</style>
</head>
<body>
<div class="wrap">

<div class="top">
<div>
<div class="brand">C-Net <span>Output Center</span></div>
<div class="muted">Generated videos, images, audio and export files</div>
</div>
<div>
<a class="btn" href="{{ route('studio.dashboard') }}">Dashboard</a>
<a class="btn" href="{{ route('projects.index') }}">Projects</a>
</div>
</div>

<div class="stats">
<div class="card"><div class="num">{{ $readyCount }}</div><div class="muted">Ready Outputs</div></div>
<div class="card"><div class="num">{{ $videoCount }}</div><div class="muted">Videos</div></div>
<div class="card"><div class="num">{{ $imageCount }}</div><div class="muted">Images</div></div>
<div class="card"><div class="num">{{ $audioCount }}</div><div class="muted">Audio</div></div>
</div>

<div class="grid">
@forelse($outputs as $output)
<div class="output">
<div class="preview">
@if($output->output_type === 'video') 🎬
@elseif($output->output_type === 'image') 🖼️
@elseif($output->output_type === 'audio') 🎵
@else 📦
@endif
</div>

<div class="body">
<div class="tag">{{ strtoupper($output->output_type) }} • {{ strtoupper($output->format ?: 'FILE') }}</div>
<h3>{{ $output->name }}</h3>

<div class="meta">
Project: {{ $output->project?->name ?: '—' }}<br>
Generated: {{ $output->generated_at?->diffForHumans() ?: '—' }}<br>
@if($output->duration_seconds)
Duration: {{ $output->duration_seconds }} sec<br>
@endif
@if($output->size_bytes)
Size: {{ number_format($output->size_bytes / 1024 / 1024, 2) }} MB
@endif
</div>

<a class="download" href="{{ route('outputs.download',$output) }}">Download Export</a>
</div>
</div>
@empty
<div class="empty">No generated outputs yet.</div>
@endforelse
</div>

<div style="margin-top:25px">
{{ $outputs->links() }}
</div>

</div>
</body>
</html>
