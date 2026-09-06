<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login | C-Net AI Studio</title>
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:grid;place-items:center;background:radial-gradient(circle at 20% 15%,#18346d 0,#080d1c 38%,#03050b 100%);font-family:Inter,system-ui,Arial;color:#fff}
.card{width:min(450px,92vw);padding:36px;background:rgba(10,17,34,.94);border:1px solid rgba(255,255,255,.12);border-radius:28px;box-shadow:0 30px 100px #0009}
.logo{width:90px;height:90px;border-radius:50%;margin:auto;display:grid;place-items:center;background:conic-gradient(#19dcff,#685fff,#ef50d8,#ff9838,#19dcff);padding:4px}
.logo span{width:100%;height:100%;border-radius:50%;display:grid;place-items:center;background:#08101e;font-size:34px;font-weight:900}
h1{text-align:center;margin:18px 0 5px}.sub{text-align:center;color:#97a6c3;margin-bottom:26px}
input{width:100%;padding:15px;margin:7px 0;border:1px solid #2a3752;background:#091221;color:white;border-radius:13px}
button{width:100%;border:0;padding:15px;margin-top:14px;border-radius:13px;background:linear-gradient(90deg,#00caff,#665dff,#df48e9);color:white;font-weight:800;cursor:pointer}
a{color:#5adfff;text-decoration:none}.foot{text-align:center;margin-top:20px;color:#9baac4}
.error{padding:11px;border-radius:11px;background:#6b2435;margin-bottom:12px}
</style>
</head>
<body>
<div class="card">
<div class="logo"><span>C</span></div>
<h1>C-Net AI Studio</h1>
<div class="sub">Sign in to your AI creative workspace</div>

@if($errors->any())
<div class="error">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('login.perform') }}">
@csrf
<input type="email" name="email" value="{{ old('email') }}" placeholder="Email address" required>
<input type="password" name="password" placeholder="Password" required>
<label style="display:flex;gap:8px;color:#96a5bf;margin-top:8px">
<input type="checkbox" name="remember" value="1" style="width:auto"> Remember me
</label>
<button type="submit">Login to Studio</button>
</form>

<div class="foot">
New creator? <a href="{{ route('register') }}">Start 7-day free trial</a>
</div>
</div>
</body>
</html>
