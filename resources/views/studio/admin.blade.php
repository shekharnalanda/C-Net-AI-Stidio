<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Master Admin | C-Net AI Studio</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#060912;color:#fff;font-family:Inter,system-ui,Arial}
.wrap{max-width:1250px;margin:auto;padding:35px 24px}
.head{display:flex;justify-content:space-between;align-items:center;gap:20px}
.brand{font-size:28px;font-weight:900}.brand span{color:#59ddff}
a{color:#59ddff;text-decoration:none}
.muted{color:#8d9bb5}
.cards{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin:26px 0}
.card,.panel{background:#0d1424;border:1px solid #1d2940;border-radius:18px;padding:19px}
.num{font-size:30px;font-weight:900}
table{width:100%;border-collapse:collapse}
td,th{padding:12px 8px;text-align:left;border-bottom:1px solid #1a263b}
th{color:#8091ae}
@media(max-width:850px){.cards{grid-template-columns:repeat(2,1fr)}}
</style>
</head>
<body>
<div class="wrap">

<div class="head">
<div>
<div class="brand">C-Net <span>AI Studio</span></div>
<div class="muted">Master Administration Center</div>
</div>
<a href="{{ route('studio.dashboard') }}">← Studio Dashboard</a>
</div>

<div class="cards">
<div class="card"><div class="num">{{ $userCount }}</div><div class="muted">Users</div></div>
<div class="card"><div class="num">{{ $projectCount }}</div><div class="muted">Projects</div></div>
<div class="card"><div class="num">{{ $jobCount }}</div><div class="muted">AI Jobs</div></div>
<div class="card"><div class="num">{{ $workerCount }}</div><div class="muted">Workers</div></div>
<div class="card"><div class="num">{{ $onlineWorkerCount }}</div><div class="muted">Online</div></div>
</div>

<div class="panel">
<h2>Recent Users</h2>

<table>
<tr>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Status</th>
<th>Trial End</th>
</tr>

@foreach($users as $item)
<tr>
<td>{{ $item->name }}</td>
<td>{{ $item->email }}</td>
<td>{{ $item->role }}</td>
<td>{{ $item->account_status }}</td>
<td>{{ $item->trial_ends_at ? $item->trial_ends_at->format('d M Y') : '—' }}</td>
</tr>
@endforeach

</table>
</div>

</div>
</body>
</html>
