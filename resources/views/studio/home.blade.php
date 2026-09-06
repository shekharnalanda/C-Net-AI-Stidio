<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C-Net AI Studio</title>

    <style>
        *{box-sizing:border-box}
        body{
            margin:0;
            font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
            background:
                radial-gradient(circle at 20% 20%,rgba(0,179,255,.18),transparent 35%),
                radial-gradient(circle at 80% 30%,rgba(136,0,255,.18),transparent 32%),
                #050914;
            color:#fff;
            min-height:100vh;
        }
        .wrap{
            max-width:1240px;
            margin:auto;
            padding:32px 24px 80px;
        }
        nav{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:24px;
        }
        .brand{
            font-size:25px;
            font-weight:800;
            letter-spacing:-.5px;
        }
        .brand span{
            background:linear-gradient(90deg,#13d9ff,#7868ff,#e84cff);
            -webkit-background-clip:text;
            color:transparent;
        }
        .nav-actions a{
            color:#fff;
            text-decoration:none;
            margin-left:24px;
            opacity:.85;
        }
        .btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:14px 22px;
            border-radius:14px;
            text-decoration:none;
            font-weight:700;
            color:#fff;
            background:linear-gradient(90deg,#00bdf9,#6c63ff,#d13dff);
            box-shadow:0 12px 40px rgba(74,106,255,.28);
        }
        .hero{
            min-height:650px;
            display:grid;
            grid-template-columns:1.15fr .85fr;
            gap:48px;
            align-items:center;
        }
        .badge{
            display:inline-block;
            padding:8px 13px;
            border:1px solid rgba(255,255,255,.14);
            background:rgba(255,255,255,.05);
            border-radius:999px;
            font-size:13px;
            letter-spacing:.08em;
            text-transform:uppercase;
        }
        h1{
            font-size:clamp(46px,7vw,86px);
            line-height:.96;
            margin:22px 0;
            letter-spacing:-4px;
        }
        .gradient{
            background:linear-gradient(90deg,#20e2ff,#5279ff,#c04dff,#ff8b38);
            -webkit-background-clip:text;
            color:transparent;
        }
        .lead{
            max-width:680px;
            font-size:19px;
            color:#b8c4db;
            line-height:1.7;
        }
        .actions{
            display:flex;
            flex-wrap:wrap;
            gap:14px;
            margin-top:30px;
        }
        .secondary{
            border:1px solid rgba(255,255,255,.16);
            background:rgba(255,255,255,.05);
        }
        .panel{
            position:relative;
            border:1px solid rgba(255,255,255,.12);
            background:linear-gradient(180deg,rgba(255,255,255,.09),rgba(255,255,255,.035));
            padding:28px;
            border-radius:28px;
            box-shadow:0 35px 90px rgba(0,0,0,.45);
            backdrop-filter:blur(20px);
        }
        .mock{
            height:410px;
            border-radius:20px;
            background:
                linear-gradient(135deg,rgba(0,199,255,.26),transparent 35%),
                linear-gradient(315deg,rgba(205,58,255,.26),transparent 35%),
                #0b1222;
            border:1px solid rgba(255,255,255,.08);
            display:flex;
            align-items:center;
            justify-content:center;
            text-align:center;
            padding:30px;
        }
        .mock strong{
            font-size:36px;
        }
        .features{
            display:grid;
            grid-template-columns:repeat(4,1fr);
            gap:16px;
            margin-top:20px;
        }
        .feature{
            padding:20px;
            border-radius:18px;
            background:rgba(255,255,255,.045);
            border:1px solid rgba(255,255,255,.08);
        }
        .feature b{
            display:block;
            margin-bottom:8px;
        }
        .feature span{
            color:#9eadc6;
            font-size:14px;
            line-height:1.5;
        }
        @media(max-width:900px){
            .hero{grid-template-columns:1fr}
            .features{grid-template-columns:repeat(2,1fr)}
            .nav-actions a:not(.btn){display:none}
            h1{letter-spacing:-2px}
        }
    </style>
</head>
<body>
<div class="wrap">

    <nav>
        <div class="brand">C-Net <span>AI Studio</span></div>

        <div class="nav-actions">
            <a href="#">Features</a>
            <a href="#">AI Tools</a>
            <a href="#">Pricing</a>
            <a href="{{ route('register') }}" class="btn">Start Creating</a>
        </div>
    </nav>

    <section class="hero">

        <div>
            <span class="badge">V3 • AI-First Creative Platform</span>

            <h1>
                Create anything.<br>
                <span class="gradient">Powered by AI.</span>
            </h1>

            <p class="lead">
                Create, edit, enhance and automate professional videos,
                images, ads, reels, audio and creative content from one
                powerful studio — built for creators, businesses,
                institutions and agencies.
            </p>

            <div class="actions">
                <a class="btn" href="{{ route('register') }}">Start Free Trial</a>
                <a class="btn secondary" href="{{ route('login') }}">Login to Studio</a>
            </div>
        </div>

        <div class="panel">
            <div class="mock">
                <div>
                    <strong>C-Net AI Studio</strong>
                    <p style="color:#aab8d2;line-height:1.6">
                        Text → Video<br>
                        Image → Video<br>
                        Business Ads<br>
                        Reels • Voice • Captions • AI Editing
                    </p>
                </div>
            </div>
        </div>

    </section>

    <div class="features">

        <div class="feature">
            <b>AI Video Generator</b>
            <span>Turn prompts, scripts and ideas into complete videos.</span>
        </div>

        <div class="feature">
            <b>Smart Video Editor</b>
            <span>Professional timeline, layers, effects and automation.</span>
        </div>

        <div class="feature">
            <b>Business Ad Studio</b>
            <span>Create branded ads and promotional videos in one click.</span>
        </div>

        <div class="feature">
            <b>Open AI Architecture</b>
            <span>No mandatory paid AI provider dependency.</span>
        </div>

    </div>

</div>
</body>
</html>
