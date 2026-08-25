<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — {{ config('app.name', 'Veldora App') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"><style>
*,*::before,*::after{box-sizing:border-box}
body{margin:0;font-family:'Inter',system-ui,sans-serif;background:#09090b;color:#f4f4f5;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.25rem}
.auth-card{width:100%;max-width:420px;background:#18181b;border:1px solid #27272a;border-radius:1.125rem;padding:2.25rem 2.5rem;box-shadow:0 25px 60px rgba(0,0,0,.6)}
.auth-logo{text-align:center;margin-bottom:1.75rem}
.auth-logo span{font-size:1.25rem;font-weight:800;background:linear-gradient(135deg,#8b5cf6,#6366f1);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.auth-logo small{display:block;color:#71717a;font-size:.8rem;margin-top:.15rem}
h1{margin:0 0 1.5rem;font-size:1.5rem;font-weight:700;color:#f4f4f5;text-align:center}
label{display:block;font-size:.8rem;font-weight:600;color:#a1a1aa;margin-bottom:.4rem;letter-spacing:.04em;text-transform:uppercase}
input[type=text],input[type=email],input[type=password]{width:100%;background:#09090b;border:1px solid #27272a;border-radius:.625rem;padding:.7rem 1rem;color:#f4f4f5;font-size:.95rem;outline:none;transition:border-color .2s,box-shadow .2s}
input[type=text]:focus,input[type=email]:focus,input[type=password]:focus{border-color:#8b5cf6;box-shadow:0 0 0 3px rgba(139,92,246,.18)}
input[type=text]::placeholder,input[type=email]::placeholder,input[type=password]::placeholder{color:#52525b}
.form-group{margin-bottom:1.1rem}
.auth-btn{width:100%;background:linear-gradient(135deg,#8b5cf6,#6366f1);color:#fff;border:none;border-radius:.625rem;padding:.875rem;font-size:.95rem;font-weight:600;cursor:pointer;letter-spacing:.02em;transition:opacity .2s,transform .1s;margin-top:.5rem}
.auth-btn:hover{opacity:.88}
.auth-btn:active{transform:scale(.98)}
.auth-link{text-align:center;margin-top:1.25rem;font-size:.875rem;color:#71717a}
.auth-link a{color:#a78bfa;text-decoration:none;font-weight:500}
.auth-link a:hover{text-decoration:underline}
.auth-alert{padding:.7rem 1rem;border-radius:.625rem;font-size:.875rem;margin-bottom:1rem}
.auth-alert-error{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.35);color:#fca5a5}
.auth-alert-success{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.35);color:#86efac}
.auth-remember{display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem}
.auth-remember input{width:1rem;height:1rem;accent-color:#8b5cf6;cursor:pointer}
.auth-remember label{margin:0;font-size:.875rem;text-transform:none;letter-spacing:0;color:#a1a1aa;cursor:pointer}
.auth-divider{border:none;border-top:1px solid #27272a;margin:1.25rem 0}
.auth-forgot{text-align:right;font-size:.8rem;margin-top:.3rem}
.auth-forgot a{color:#a78bfa;text-decoration:none}
.auth-forgot a:hover{text-decoration:underline}
.auth-back{text-align:center;margin-top:1rem}
.auth-back a{color:#a78bfa;font-size:.875rem;text-decoration:none}
.auth-back a:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="auth-card">
    <div class="auth-logo">
        <span>{{ config('app.name', 'Veldora') }}</span>
        <small>Create your account</small>
    </div>
    <h1>Create Account</h1>

    @if (session()->has('error'))
        <div class="auth-alert auth-alert-error">{{ session()->get('error') }}</div>
    @endif

    <form action="/register" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Full Name</label>
            <input id="name" type="text" name="name" required placeholder="John Doe" autocomplete="name">
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input id="email" type="email" name="email" required placeholder="you@example.com" autocomplete="email">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required placeholder="Min. 8 characters" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="••••••••" autocomplete="new-password">
        </div>
        <button type="submit" class="auth-btn">Create Account</button>
    </form>

    <div class="auth-link">Already have an account? <a href="/login">Sign in</a></div>
</div>
</body>
</html>