<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Bacha Stylo</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', system-ui, sans-serif;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 440px;
            background: #111113;
            -webkit-font-smoothing: antialiased;
        }

        /* LEFT PANEL */
        .left-panel {
            position: relative;
            background: #111113;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 48px;
            overflow: hidden;
        }
        .left-panel::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(232,29,37,.12) 0%, transparent 70%);
            top: 50%; left: 50%;
            transform: translate(-50%,-50%);
            pointer-events: none;
        }
        .left-panel::after {
            content: '';
            position: absolute; inset: 0;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 40px,
                rgba(255,255,255,.015) 40px,
                rgba(255,255,255,.015) 41px
            ),
            repeating-linear-gradient(
                90deg,
                transparent,
                transparent 40px,
                rgba(255,255,255,.015) 40px,
                rgba(255,255,255,.015) 41px
            );
        }
        .left-content { position: relative; z-index: 1; text-align: center; max-width: 340px; }
        .left-logo { margin-bottom: 24px; }
        .left-logo img { height: 38px; filter: brightness(0) invert(1); opacity: .9; }
        .brand-name {
            font-size: 2rem; font-weight: 800; color: #fff;
            letter-spacing: -1.2px; line-height: 1.05; margin-bottom: 10px;
        }
        .brand-name span { color: #e81d25; }
        .brand-sub { font-size: .82rem; color: rgba(255,255,255,.38); font-weight: 500; line-height: 1.6; }

        .left-stats {
            display: flex; gap: 22px; margin-top: 30px;
            justify-content: center;
        }
        .left-stat { text-align: center; }
        .left-stat-val { font-size: 1.2rem; font-weight: 800; color: #fff; letter-spacing: -.8px; }
        .left-stat-label { font-size: .6rem; color: rgba(255,255,255,.34); text-transform: uppercase; letter-spacing: .14em; margin-top: 3px; }

        /* RIGHT PANEL */
        .right-panel {
            background: #fff;
            display: flex; align-items: center; justify-content: center;
            padding: 40px 42px;
        }
        .login-box { width: 100%; max-width: 332px; }

        .login-header { margin-bottom: 26px; }
        .login-header h1 { font-size: 1.35rem; font-weight: 800; color: #111827; letter-spacing: -.4px; }
        .login-header p { font-size: .78rem; color: #6b7280; margin-top: 4px; }

        .form-group { margin-bottom: 14px; }
        .form-label {
            display: block; font-size: .68rem; font-weight: 700;
            color: #374151; margin-bottom: 6px; letter-spacing: .1px;
        }
        .form-input {
            width: 100%; padding: 9px 12px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-family: inherit; font-size: .82rem; color: #111827;
            background: #fff;
            outline: none; transition: all .18s ease;
            -webkit-appearance: none;
        }
        .form-input:focus { border-color: #e81d25; box-shadow: 0 0 0 3px rgba(232,29,37,.1); }
        .form-input::placeholder { color: #d1d5db; }

        .pwd-wrap { position: relative; }
        .pwd-toggle {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: #9ca3af;
            cursor: pointer; font-size: .78rem; padding: 4px;
            transition: color .15s;
        }
        .pwd-toggle:hover { color: #6b7280; }

        .error-box {
            padding: 10px 12px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            color: #991b1b;
            font-size: .76rem;
            margin-bottom: 16px;
            display: flex; align-items: center; gap: 8px;
        }

        .login-btn {
            width: 100%; padding: 10px;
            background: #e81d25;
            border: none; border-radius: 10px;
            color: #fff; font-family: inherit;
            font-size: .82rem; font-weight: 800;
            cursor: pointer; transition: all .18s ease;
            letter-spacing: -.1px;
            -webkit-appearance: none;
        }
        .login-btn:hover { background: #c0141b; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(232,29,37,.3); }
        .login-btn:active { transform: translateY(0); }

        .login-footer { text-align: center; margin-top: 22px; font-size: .68rem; color: #9ca3af; }

        @media (max-width: 768px) {
            body { grid-template-columns: 1fr; }
            .left-panel { display: none; }
            .right-panel { padding: 32px 24px; }
        }
    </style>
</head>
<body>
    <div class="left-panel">
        <div class="left-content">
            <div class="left-logo">
                @if(!empty($logo_url))
                    <img src="{{ $logo_url }}" alt="Bacha Stylo">
                @endif
            </div>
            <div class="brand-name">Bacha <span>Stylo</span><br>Admin</div>
            <p class="brand-sub">Manage your store, products, orders and customers from one powerful control panel.</p>
            <div class="left-stats">
                <div class="left-stat">
                    <div class="left-stat-val">100%</div>
                    <div class="left-stat-label">Uptime</div>
                </div>
                <div class="left-stat">
                    <div class="left-stat-val" style="color:#e81d25;">Fast</div>
                    <div class="left-stat-label">Response</div>
                </div>
                <div class="left-stat">
                    <div class="left-stat-val">Secure</div>
                    <div class="left-stat-label">Access</div>
                </div>
            </div>
        </div>
    </div>

    <div class="right-panel">
        <div class="login-box">
            <div class="login-header">
                <h1>Welcome back</h1>
                <p>Sign in to your admin account to continue</p>
            </div>

            @if($errors->has('error'))
                <div class="error-box">
                    <i class="mdi mdi-alert-circle-outline"></i>
                    {{ $errors->first('error') }}
                </div>
            @endif

            <form action="{{ route('admin.login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="username">Email or Username</label>
                    <input type="text" id="username" name="username" class="form-input"
                        placeholder="admin@bachastylo.com"
                        value="{{ old('username') }}" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="pwd-wrap">
                        <input type="password" id="password" name="password" class="form-input"
                            placeholder="••••••••" required autocomplete="current-password"
                            style="padding-right: 36px;">
                        <button type="button" class="pwd-toggle" onclick="togglePwd()">
                            <i class="mdi mdi-eye-outline" id="pwd-icon"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="login-btn">Sign In</button>
            </form>

            <div class="login-footer">Bacha Stylo Admin Panel &copy; {{ date('Y') }}</div>
        </div>
    </div>

    <script>
        function togglePwd() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('pwd-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'mdi mdi-eye-off-outline';
            } else {
                input.type = 'password';
                icon.className = 'mdi mdi-eye-outline';
            }
        }
    </script>
</body>
</html>
