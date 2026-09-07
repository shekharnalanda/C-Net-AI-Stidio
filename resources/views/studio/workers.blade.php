<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>AI Worker Management | C-Net AI Studio</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#050811;color:#fff;font-family:Inter,system-ui,Arial}
.wrap{max-width:1450px;margin:auto;padding:32px}
.top{display:flex;justify-content:space-between;align-items:center;gap:20px}
.brand{font-size:28px;font-weight:900}.brand span{color:#5ae1ff}
a{text-decoration:none;color:#5ae1ff}
.muted{color:#8d9cb7}
.panel{background:#0c1424;border:1px solid #1c2941;border-radius:20px;padding:20px;margin-top:22px}
.cards{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:20px}
.card{background:#101929;border:1px solid #1e2d47;border-radius:17px;padding:18px}
.num{font-size:28px;font-weight:900}
table{width:100%;border-collapse:collapse;margin-top:12px}
th,td{padding:12px 10px;text-align:left;border-bottom:1px solid #1b2840;font-size:13px}
th{color:#7f91ad}
button,.btn{border:0;padding:10px 13px;border-radius:10px;background:#17243a;color:#fff;font-weight:700;cursor:pointer}
.primary{background:linear-gradient(90deg,#03c9ff,#6d61ff,#dd4ce9)}
.danger{background:#5a1e2b}
.good{color:#71efa1}.bad{color:#ff7f96}.busy{color:#ffd36c}
input{padding:11px;border-radius:9px;border:1px solid #2b3a57;background:#091221;color:#fff}
.notice{margin-top:15px;padding:15px;border-radius:12px;background:#103521;border:1px solid #286342;color:#89f0ae}
.secret{padding:16px;background:#281d0c;border:1px solid #685221;color:#ffd979;border-radius:12px;margin-top:15px;word-break:break-all}
.small{font-size:12px}
.actions{display:flex;gap:6px;flex-wrap:wrap}
@media(max-width:900px){.cards{grid-template-columns:repeat(2,1fr)}.panel{overflow:auto}}
</style>
</head>
<body>
<div class="wrap">

<div class="top">
<div>
<div class="brand">C-Net <span>AI Workers</span></div>
<div class="muted">Distributed Processing & Device Security Center</div>
</div>
<div>
<a class="btn" href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
<a class="btn primary" href="{{ route('admin.worker.download') }}">Download Windows Worker</a>
</div>
</div>

@if(session('success'))
<div class="notice">{{ session('success') }}</div>
@endif

@if(session('activation_code'))
<div class="secret">
<b>ONE-TIME ACTIVATION CODE</b><br><br>
{{ session('activation_code') }}
<br><br>
<span class="small">Shown only now. Expires in 24 hours and can be used once.</span>
</div>
@endif

@if(session('worker_new_token'))
<div class="secret">
<b>NEW WORKER CREDENTIAL — Worker #{{ session('worker_token_id') }}</b><br><br>
{{ session('worker_new_token') }}
<br><br>
<span class="small">Store/update this credential now. It will not be shown again.</span>
</div>
@endif

@php
$online = $workers->filter(fn($w) => $w->isOnline())->count();
$busy = $workers->where('status','busy')->count();
$disabled = $workers->where('is_enabled',false)->count();
@endphp

<div class="cards">
<div class="card"><div class="num">{{ $workers->count() }}</div><div class="muted">Registered Workers</div></div>
<div class="card"><div class="num">{{ $online }}</div><div class="muted">Online</div></div>
<div class="card"><div class="num">{{ $busy }}</div><div class="muted">Busy</div></div>
<div class="card"><div class="num">{{ $disabled }}</div><div class="muted">Disabled</div></div>
</div>

<div class="panel">
<h2>Provision New Computer</h2>
<p class="muted">Create a one-time activation code for a new laptop, desktop or dedicated AI server.</p>

<form method="POST" action="{{ route('admin.workers.activation') }}">
@csrf
<input name="label" placeholder="Example: Office AI Server / Laptop 2">
<button class="primary" type="submit">Generate Activation Code</button>
</form>
</div>

<div class="panel">
<h2>Registered Workers</h2>

<table>
<thead>
<tr>
<th>Worker</th>
<th>Status</th>
<th>Hardware</th>
<th>FFmpeg</th>
<th>Last Seen</th>
<th>Security</th>
<th>Actions</th>
</tr>
</thead>
<tbody>

@forelse($workers as $worker)
<tr>
<td>
<b>{{ $worker->name ?: 'Worker #'.$worker->id }}</b><br>
<span class="muted">{{ $worker->hostname }}</span><br>
<span class="small muted">{{ $worker->worker_uuid }}</span>
</td>

<td>
@if(!$worker->is_enabled)
<span class="bad">DISABLED</span>
@elseif($worker->status === 'busy')
<span class="busy">BUSY</span>
@elseif($worker->isOnline())
<span class="good">ONLINE</span>
@else
<span class="muted">OFFLINE</span>
@endif
</td>

<td>
{{ $worker->cpu ?: '—' }}<br>
<span class="muted">{{ $worker->gpu ?: 'GPU: —' }}</span><br>
<span class="small">{{ $worker->ram_mb ? round($worker->ram_mb/1024,1).' GB RAM' : '—' }}</span>
</td>

<td>
{{ $worker->ffmpeg_version ? 'Available' : 'Not reported' }}
</td>

<td>
{{ $worker->last_seen_at ? $worker->last_seen_at->diffForHumans() : 'Never' }}
</td>

<td>
@if($worker->token_hash)
<span class="good">Per-device credential</span>
@else
<span class="bad">Legacy credential</span>
@endif
</td>

<td>
<div class="actions">

<form method="POST" action="{{ route('admin.workers.toggle',$worker) }}">
@csrf
<button>{{ $worker->is_enabled ? 'Disable' : 'Enable' }}</button>
</form>

<form method="POST" action="{{ route('admin.workers.rotate',$worker) }}">
@csrf
<button>Reset Credential</button>
</form>

<form method="POST" action="{{ route('admin.workers.destroy',$worker) }}" onsubmit="return confirm('Remove this worker?')">
@csrf
@method('DELETE')
<button class="danger">Remove</button>
</form>

</div>
</td>
</tr>
@empty
<tr><td colspan="7" class="muted">No worker computers registered yet.</td></tr>
@endforelse

</tbody>
</table>
</div>

<div class="panel">
<h2>Recent Activation Codes</h2>
<table>
<tr>
<th>Label</th>
<th>Created</th>
<th>Expires</th>
<th>Status</th>
</tr>

@foreach($activations as $item)
<tr>
<td>{{ $item->label }}</td>
<td>{{ $item->created_at?->format('d M Y H:i') }}</td>
<td>{{ $item->expires_at?->format('d M Y H:i') }}</td>
<td>
@if($item->used_at)
<span class="good">USED</span>
@elseif($item->expires_at && $item->expires_at->isPast())
<span class="bad">EXPIRED</span>
@else
<span class="busy">AVAILABLE</span>
@endif
</td>
</tr>
@endforeach
</table>
</div>

</div>
</body>
</html>
