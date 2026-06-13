<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign In — CK Takhmao School</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/js/app.js'])
    <style>
        :root,[data-theme="light"]{--login-bg:#0f172a;--card-bg:#ffffff;--text-h:#111827;--text-s:#6b7280;--text-l:#374151;--border:#e5e7eb;--input-bg:#ffffff;--input-text:#111827;--err-bg:#fde8e8;--err-text:#7f1d1d;--err-border:#fecaca;--check-label:#6b7280;}
        [data-theme="dark"]{--login-bg:#060d1a;--card-bg:#131c2e;--text-h:#f1f5f9;--text-s:#94a3b8;--text-l:#cbd5e1;--border:#1e3a5f;--input-bg:#1e2d45;--input-text:#e2e8f0;--err-bg:rgba(239,68,68,0.12);--err-text:#fca5a5;--err-border:rgba(239,68,68,0.3);--check-label:#94a3b8;}
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
        html{-webkit-text-size-adjust:100%;}
        body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--login-bg);min-height:100vh;min-height:100dvh;display:flex;align-items:center;justify-content:center;padding:16px;transition:background 0.25s;}
        .bg-pattern{position:fixed;inset:0;background:radial-gradient(ellipse at 20% 50%,rgba(26,86,219,0.18) 0%,transparent 50%),radial-gradient(ellipse at 80% 50%,rgba(124,58,237,0.12) 0%,transparent 50%);pointer-events:none;}
        .login-card{background:var(--card-bg);border-radius:20px;padding:48px 44px;width:100%;max-width:480px;position:relative;z-index:1;box-shadow:0 25px 60px rgba(0,0,0,0.4);border:1px solid var(--border);transition:background 0.25s,border-color 0.25s;}
        .brand{text-align:center;margin-bottom:32px;}
        .brand-icon{width:52px;height:52px;background:linear-gradient(135deg,#1a56db,#7c3aed);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:22px;color:#fff;}
        .brand-logo{width:240px;height:120px;object-fit:contain;margin:0 auto 18px;display:block;}
        .brand-name{font-size:24px;font-weight:800;color:var(--text-h);}
        .brand-sub{font-size:17px;font-family:'Kantumruy Pro',sans-serif;color:var(--text-s);margin-top:4px;line-height:1.5;}
        .form-group{margin-bottom:20px;}
        .form-label{display:block;margin-bottom:8px;font-size:14px;font-weight:600;color:var(--text-l);}
        .form-control{width:100%;padding:14px 16px;border:1.5px solid var(--border);border-radius:10px;font-size:16px;color:var(--input-text);background:var(--input-bg);font-family:inherit;outline:none;transition:border 0.15s,box-shadow 0.15s;-webkit-appearance:none;appearance:none;}
        .form-control:focus{border-color:#1a56db;box-shadow:0 0 0 3px rgba(26,86,219,0.15);}
        .form-control.is-invalid{border-color:#ef4444;}
        .invalid-feedback{color:#ef4444;font-size:13px;margin-top:5px;}
        .remember-row{display:flex;align-items:center;gap:8px;margin-bottom:24px;}
        .remember-row input[type="checkbox"]{width:18px;height:18px;accent-color:#1a56db;cursor:pointer;}
        .remember-row label{font-size:14px;color:var(--check-label);cursor:pointer;}
        .btn-login{width:100%;padding:15px;background:linear-gradient(135deg,#1a56db,#1342b0);color:#fff;border:none;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer;font-family:inherit;transition:transform 0.1s,box-shadow 0.1s;-webkit-tap-highlight-color:transparent;}
        .btn-login:hover{transform:translateY(-1px);box-shadow:0 4px 16px rgba(26,86,219,0.4);}
        .btn-login:active{transform:translateY(0);box-shadow:none;}
        .btn-login:focus-visible{outline:2px solid #1a56db;outline-offset:2px;}
        .alert-danger{background:var(--err-bg);color:var(--err-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;border:1px solid var(--err-border);display:flex;align-items:flex-start;gap:8px;}
        .theme-btn{position:absolute;top:16px;right:16px;background:none;border:1px solid var(--border);border-radius:8px;width:34px;height:34px;cursor:pointer;color:var(--text-s);font-size:14px;display:flex;align-items:center;justify-content:center;transition:background 0.15s;}
        .theme-btn:hover{background:rgba(0,0,0,0.05);}
        @media(max-width:576px){.login-card{padding:32px 24px;max-width:100%;} .brand-logo{width:200px;height:100px;} .brand-name{font-size:20px;} .brand-sub{font-size:15px;}}
    </style>
</head>
<body>
<div class="bg-pattern" aria-hidden="true"></div>
<main>
    <div class="login-card" role="main" style="position:relative">
        <button class="theme-btn" id="loginThemeBtn" aria-label="Toggle theme">
            <i class="fas fa-moon" id="loginThemeIcon"></i>
        </button>
        <div class="brand">
            <img src="/logo.png" class="brand-logo" alt="CK Takhmao School Logo">
            <div class="brand-name">CK Takhmao School</div>
            <div class="brand-sub">សាលាបង្វឹក CK តាខ្មៅ</div>
        </div>
        @if($errors->any())
        <div class="alert-danger" role="alert" aria-live="polite">
            <span aria-hidden="true">⚠</span>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif
        <form action="{{ route('login') }}" method="POST" novalidate>
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       autocomplete="email" inputmode="email"
                       required autofocus aria-required="true">
                @error('email')<div class="invalid-feedback" role="alert">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       autocomplete="current-password" required aria-required="true">
                @error('password')<div class="invalid-feedback" role="alert">{{ $message }}</div>@enderror
            </div>
            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Keep me signed in</label>
            </div>
            <button type="submit" class="btn-login">Sign In</button>
        </form>
    </div>
</main>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script>
(function(){
    var html=document.documentElement,btn=document.getElementById('loginThemeBtn'),icon=document.getElementById('loginThemeIcon'),KEY='edupay-theme';
    function apply(t){html.setAttribute('data-theme',t);icon.className=t==='dark'?'fas fa-sun':'fas fa-moon';try{localStorage.setItem(KEY,t);}catch(e){}}
    var saved;try{saved=localStorage.getItem(KEY);}catch(e){}
    if(saved==='dark'||saved==='light'){apply(saved);}
    else if(window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches){apply('dark');}
    btn.addEventListener('click',function(){apply(html.getAttribute('data-theme')==='dark'?'light':'dark');});
})();
</script>
</body>
</html>
