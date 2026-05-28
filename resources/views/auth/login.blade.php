<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — EduPay Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bg-pattern {
            position: fixed; inset: 0;
            background: radial-gradient(ellipse at 20% 50%, rgba(26,86,219,0.15) 0%, transparent 50%),
                        radial-gradient(ellipse at 80% 50%, rgba(124,58,237,0.1) 0%, transparent 50%);
        }
        .login-card {
            background: white; border-radius: 20px;
            padding: 44px 40px; width: 400px;
            position: relative; z-index: 1;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4);
        }
        .brand { text-align: center; margin-bottom: 32px; }
        .brand-icon {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, #1a56db, #7c3aed);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
            font-size: 22px; color: white;
        }
        .brand-name { font-size: 20px; font-weight: 800; color: #111827; }
        .brand-sub { font-size: 13px; color: #6b7280; margin-top: 4px; }
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #374151; }
        .form-control {
            width: 100%; padding: 11px 14px;
            border: 1.5px solid #e5e7eb; border-radius: 9px;
            font-size: 14px; color: #111827;
            font-family: inherit; outline: none;
            transition: border 0.15s;
        }
        .form-control:focus { border-color: #1a56db; box-shadow: 0 0 0 3px rgba(26,86,219,0.1); }
        .btn-login {
            width: 100%; padding: 12px;
            background: linear-gradient(135deg, #1a56db, #1342b0);
            color: white; border: none; border-radius: 9px;
            font-size: 15px; font-weight: 700;
            cursor: pointer; font-family: inherit;
            transition: transform 0.1s, box-shadow 0.1s;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(26,86,219,0.4); }
        .alert-danger {
            background: #fde8e8; color: #7f1d1d;
            padding: 10px 14px; border-radius: 8px;
            font-size: 13px; margin-bottom: 16px;
            border: 1px solid #fecaca;
        }
        .demo-info {
            margin-top: 20px; padding: 14px;
            background: #f0f9ff; border-radius: 9px;
            border: 1px solid #bae6fd;
            font-size: 12px; color: #0c4a6e;
        }
        .demo-info strong { display: block; margin-bottom: 4px; color: #075985; }
    </style>
</head>
<body>
<div class="bg-pattern"></div>
<div class="login-card">
    <div class="brand">
        <div class="brand-icon">🎓</div>
        <div class="brand-name">EduPay Manager</div>
        <div class="brand-sub">Student Payment Management System</div>
    </div>

    @if($errors->any())
    <div class="alert-danger">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                   placeholder="admin@school.edu" required autofocus>
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px">
            <input type="checkbox" name="remember" id="remember" style="width:16px;height:16px;accent-color:#1a56db">
            <label for="remember" style="font-size:13px;color:#6b7280;cursor:pointer">Remember me</label>
        </div>
        <button type="submit" class="btn-login">Sign In</button>
    </form>

    <div class="demo-info">
        <strong>Demo Credentials:</strong>
        Email: admin@school.edu<br>
        Password: password
    </div>
</div>
</body>
</html>