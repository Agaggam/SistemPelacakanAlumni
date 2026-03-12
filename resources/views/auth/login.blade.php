<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Sistem Pelacakan Alumni</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0f1117;
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        /* Background grid + orbs */
        body::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(99,102,241,0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(139,92,246,0.1) 0%, transparent 50%);
        }
        .grid-bg {
            position: absolute; inset: 0;
            background-image: 
                linear-gradient(rgba(45,49,84,0.4) 1px, transparent 1px),
                linear-gradient(90deg, rgba(45,49,84,0.4) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        .login-container {
            position: relative; z-index: 10;
            width: 100%; max-width: 420px;
            padding: 20px;
        }
        .login-card {
            background: rgba(30, 34, 53, 0.9);
            border: 1px solid rgba(45,49,84,0.8);
            border-radius: 20px;
            padding: 40px;
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 28px;
        }
        .logo-icon {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 16px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 28px;
            margin-bottom: 14px;
            box-shadow: 0 8px 25px rgba(99,102,241,0.35);
        }
        .login-logo h1 { font-size: 20px; font-weight: 800; margin-bottom: 4px; }
        .login-logo p { font-size: 13px; color: #64748b; }
        
        .form-group { margin-bottom: 16px; }
        .form-label {
            display: block; font-size: 13px; font-weight: 500;
            color: #94a3b8; margin-bottom: 8px;
        }
        .form-control {
            width: 100%; padding: 11px 14px;
            background: #0f1117;
            border: 1px solid #2d3154;
            border-radius: 10px;
            color: #f1f5f9;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }
        .form-control::placeholder { color: #475569; }
        
        .form-check {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: #94a3b8;
        }
        
        .btn-login {
            width: 100%; padding: 12px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none; border-radius: 10px;
            color: white; font-size: 15px; font-weight: 700;
            cursor: pointer; transition: all 0.2s;
            margin-top: 8px;
            font-family: 'Inter', sans-serif;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(99,102,241,0.45);
        }

        .error-msg {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.25);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #f87171;
            margin-bottom: 16px;
        }

        .hint-box {
            margin-top: 20px;
            padding: 12px 14px;
            background: rgba(99,102,241,0.08);
            border: 1px solid rgba(99,102,241,0.2);
            border-radius: 10px;
            font-size: 12px;
            color: #818cf8;
        }
        .hint-box strong { color: #a5b4fc; }
    </style>
</head>
<body>
    <div class="grid-bg"></div>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <div class="logo-icon">🎓</div>
                <h1>Sistem Pelacakan Alumni</h1>
                <p>Login untuk mengakses dashboard admin</p>
            </div>

            @if($errors->any())
                <div class="error-msg">
                    ❌ {{ $errors->first('email') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email"
                        class="form-control" placeholder="email@alumni.ac.id"
                        value="{{ old('email') }}" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password"
                        class="form-control" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="remember"> Ingat saya
                    </label>
                </div>
                <button type="submit" class="btn-login">🚀 Masuk</button>
            </form>

            <div class="hint-box">
                <strong>Akun Demo:</strong><br>
                👑 <strong>Admin</strong> — admin@alumni.ac.id | password<br>
                👤 <strong>User</strong> — user@alumni.ac.id | password
            </div>

            <div style="text-align:center; margin-top:16px;">
                <a href="{{ route('search') }}" style="font-size:12px; color:#64748b; text-decoration:none;">
                    ← Kembali ke Pencarian Alumni
                </a>
            </div>
        </div>
    </div>
</body>
</html>
