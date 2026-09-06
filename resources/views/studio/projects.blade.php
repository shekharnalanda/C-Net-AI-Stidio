<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Projects | C-Net AI Studio</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#060912;color:#fff;font-family:Inter,system-ui,Arial}
.wrap{max-width:1400px;margin:auto;padding:30px}
.top{display:flex;justify-content:space-between;align-items:center;gap:20px}
a{color:inherit;text-decoration:none}
.brand{font-size:26px;font-weight:900}.brand span{color:#59ddff}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:28px}
.card{padding:20px;background:#0c1424;border:1px solid #1c2942;border-radius:20px;transition:.2s}
.card:hover{transform:translateY(-3px);border-color:#5063a2}
.preview{height:150px;border-radius:15px;background:radial-gradient(circle at 70% 20%,#50347d,transparent 35%),linear-gradient(135deg,#10294a,#121729);display:grid;place-items:center;font-size:40px}
.name{font-size:19px;font-weight:800;margin-top:14px}.muted{color:#8d9bb5}.tag{display:inline-block;margin-top:9px;padding:6px 9px;border-radius:999px;background:#102938;color:#66ddff;font-size:12px}
.btn{padding:11px 16px;background:linear-gradient(90deg,#05c8ff,#7063ff,#d94de8);border-radius:12px;font-weight:800}
@media(max-width:950px){.grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.grid{grid-template-columns:1fr}.wrap{padding:20px}}
</style>
</head>
<body>
<div class="wrap">
<div class="top">
<div>
<div class="brand">C-Net <span>AI Studio</span></div>
<div class="muted">Your creative projects</div>
</div>
<a class="btn" href="{{ route('studio.dashboard') }}">Studio Dashboard</a>
</div>

<div class="grid">
@forelse($projects as $project)
<a href="{{ route('projects.editor',$project) }}" class="card">
<div class="preview">▶</div>
<div class="name">{{ $project->name }}</div>
<div class="muted">{{ strtoupper(str_replace('-', ' ', $project->type)) }}</div>
<span class="tag">{{ $project->aspect_ratio }} • {{ strtoupper($project->status) }}</span>
</a>
@empty
<div class="card">
<div class="name">No projects yet</div>
<div class="muted">Create your first project from Studio Dashboard.</div>
</div>
@endforelse
</div>

<div style="margin-top:25px">{{ $projects->links() }}</div>
</div>
</body>
</html>
