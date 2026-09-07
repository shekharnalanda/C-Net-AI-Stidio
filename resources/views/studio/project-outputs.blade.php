<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $project->name }} Outputs | C-Net AI Studio</title>
<style>
body{margin:0;background:#050812;color:#fff;font-family:Inter,system-ui,Arial}
.wrap{max-width:1200px;margin:auto;padding:30px}
a{color:#65ddff;text-decoration:none}
.item{padding:17px;background:#0d1525;border:1px solid #1c2a42;border-radius:14px;margin:12px 0}
.btn{display:inline-block;padding:9px 13px;background:#17243d;border-radius:9px;color:#fff;margin-top:8px}
.muted{color:#8997af}
</style>
</head>
<body>
<div class="wrap">
<a href="{{ route('projects.editor',$project) }}">← Back to Editor</a>

<h1>{{ $project->name }} — Outputs</h1>
<p class="muted">All generated files for this project.</p>

@forelse($outputs as $output)
<div class="item">
<strong>{{ $output->name }}</strong><br>
<span class="muted">
{{ strtoupper($output->output_type) }}
• {{ strtoupper($output->format ?: 'FILE') }}
• {{ $output->generated_at?->diffForHumans() ?: '—' }}
</span><br>
<a class="btn" href="{{ route('outputs.download',$output) }}">Download</a>
</div>
@empty
<p class="muted">No outputs generated for this project yet.</p>
@endforelse

{{ $outputs->links() }}
</div>
</body>
</html>
