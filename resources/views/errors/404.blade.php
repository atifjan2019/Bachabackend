<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found | Bacha Stylo Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0d1726;
            color: #fff;
            -webkit-font-smoothing: antialiased;
            overflow: hidden;
            position: relative;
        }

        /* Animated gradient background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 800px 600px at 30% 20%, rgba(217, 45, 32, 0.12), transparent),
                radial-gradient(ellipse 600px 500px at 70% 80%, rgba(59, 130, 246, 0.06), transparent),
                radial-gradient(ellipse 400px 400px at 50% 50%, rgba(217, 45, 32, 0.04), transparent);
            animation: bgPulse 8s ease-in-out infinite alternate;
            z-index: 0;
        }

        @keyframes bgPulse {
            0% { opacity: .6; transform: scale(1); }
            100% { opacity: 1; transform: scale(1.1); }
        }

        /* Grid pattern */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background: repeating-linear-gradient(
                0deg,
                transparent, transparent 60px,
                rgba(255,255,255,.015) 60px, rgba(255,255,255,.015) 61px
            ),
            repeating-linear-gradient(
                90deg,
                transparent, transparent 60px,
                rgba(255,255,255,.015) 60px, rgba(255,255,255,.015) 61px
            );
            z-index: 0;
        }

        /* Floating orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }
        .orb-1 {
            width: 300px; height: 300px;
            top: -80px; right: -60px;
            background: radial-gradient(circle, rgba(217,45,32,.08), transparent 70%);
            animation: orbFloat1 12s ease-in-out infinite;
        }
        .orb-2 {
            width: 200px; height: 200px;
            bottom: -40px; left: -40px;
            background: radial-gradient(circle, rgba(59,130,246,.06), transparent 70%);
            animation: orbFloat2 10s ease-in-out infinite;
        }
        .orb-3 {
            width: 120px; height: 120px;
            top: 40%; left: 15%;
            background: radial-gradient(circle, rgba(255,255,255,.03), transparent 70%);
            animation: orbFloat3 8s ease-in-out infinite;
        }

        @keyframes orbFloat1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-30px, 20px) scale(1.1); }
        }
        @keyframes orbFloat2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(20px, -30px) scale(1.15); }
        }
        @keyframes orbFloat3 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(15px, -20px); }
        }

        /* Main container */
        .error-container {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 520px;
            padding: 40px;
            animation: fadeInUp .8s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Error code */
        .error-code {
            position: relative;
            margin-bottom: 24px;
        }
        .error-code-text {
            font-size: clamp(8rem, 18vw, 12rem);
            font-weight: 800;
            letter-spacing: -8px;
            line-height: .85;
            background: linear-gradient(180deg, rgba(255,255,255,.12) 0%, rgba(255,255,255,.03) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            user-select: none;
        }
        .error-code-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(8rem, 18vw, 12rem);
            font-weight: 800;
            letter-spacing: -8px;
            line-height: .85;
            background: linear-gradient(135deg, #e81d25, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: glitch 4s ease-in-out infinite;
            user-select: none;
        }

        @keyframes glitch {
            0%, 90%, 100% { clip-path: inset(0); transform: translate(0); opacity: .15; }
            92% { clip-path: inset(20% 0 60% 0); transform: translate(-4px, 2px); opacity: .4; }
            94% { clip-path: inset(50% 0 20% 0); transform: translate(3px, -1px); opacity: .3; }
            96% { clip-path: inset(70% 0 5% 0); transform: translate(-2px, 3px); opacity: .35; }
            98% { clip-path: inset(5% 0 85% 0); transform: translate(4px, -2px); opacity: .25; }
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 28px;
        }
        .divider-line {
            width: 48px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.15));
        }
        .divider-line:last-child {
            background: linear-gradient(90deg, rgba(255,255,255,.15), transparent);
        }
        .divider-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #e81d25;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(232,29,37,.4); }
            50% { opacity: .7; box-shadow: 0 0 0 8px rgba(232,29,37,0); }
        }

        /* Title */
        .error-title {
            font-size: clamp(1.2rem, 3vw, 1.6rem);
            font-weight: 800;
            color: #fff;
            letter-spacing: -.6px;
            margin-bottom: 10px;
        }
        .error-desc {
            font-size: .84rem;
            color: rgba(255,255,255,.4);
            line-height: 1.7;
            max-width: 380px;
            margin: 0 auto 32px;
        }

        /* Buttons */
        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-404 {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-family: inherit;
            font-size: .78rem;
            font-weight: 700;
            cursor: pointer;
            transition: all .25s ease;
            text-decoration: none;
            border: none;
            letter-spacing: -.1px;
        }
        .btn-404-primary {
            background: linear-gradient(135deg, #e81d25, #f97316);
            color: #fff;
            box-shadow: 0 12px 32px rgba(232,29,37,.25);
        }
        .btn-404-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 44px rgba(232,29,37,.35);
            color: #fff;
        }
        .btn-404-ghost {
            background: rgba(255,255,255,.06);
            color: rgba(255,255,255,.7);
            border: 1px solid rgba(255,255,255,.1);
        }
        .btn-404-ghost:hover {
            background: rgba(255,255,255,.1);
            color: #fff;
            border-color: rgba(255,255,255,.2);
            transform: translateY(-2px);
        }
        .btn-404 i { font-size: 1rem; }

        /* Footer */
        .error-footer {
            margin-top: 48px;
            font-size: .62rem;
            color: rgba(255,255,255,.2);
            text-transform: uppercase;
            letter-spacing: .16em;
            font-weight: 700;
        }

        /* Floating shapes */
        .shape {
            position: fixed;
            border: 1px solid rgba(255,255,255,.04);
            pointer-events: none;
            z-index: 0;
        }
        .shape-1 {
            width: 80px; height: 80px;
            top: 12%; left: 8%;
            border-radius: 50%;
            animation: shapeFloat 7s ease-in-out infinite;
        }
        .shape-2 {
            width: 50px; height: 50px;
            bottom: 20%; right: 12%;
            border-radius: 12px;
            transform: rotate(45deg);
            animation: shapeFloat 5s ease-in-out infinite reverse;
        }
        .shape-3 {
            width: 30px; height: 30px;
            top: 55%; right: 20%;
            border-radius: 50%;
            border-color: rgba(232,29,37,.08);
            animation: shapeFloat 6s ease-in-out infinite;
        }
        .shape-4 {
            width: 60px; height: 60px;
            bottom: 30%; left: 18%;
            border-radius: 16px;
            transform: rotate(-15deg);
            animation: shapeFloat 8s ease-in-out infinite reverse;
        }

        @keyframes shapeFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-18px) rotate(8deg); }
        }

        @media (max-width: 480px) {
            .error-container { padding: 24px 20px; }
            .btn-group { flex-direction: column; align-items: center; }
            .btn-404 { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <!-- Decorative elements -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
    <div class="shape shape-4"></div>

    <div class="error-container">
        <!-- Error code -->
        <div class="error-code">
            <div class="error-code-text">404</div>
            <div class="error-code-overlay">404</div>
        </div>

        <!-- Divider -->
        <div class="divider">
            <span class="divider-line"></span>
            <span class="divider-dot"></span>
            <span class="divider-line"></span>
        </div>

        <!-- Message -->
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-desc">
            The page you're looking for doesn't exist or has been moved. Let's get you back to the admin panel.
        </p>

        <!-- Actions -->
        <div class="btn-group">
            <a href="{{ route('admin.login') }}" class="btn-404 btn-404-primary">
                <i class="mdi mdi-arrow-left"></i>
                Back to Admin
            </a>
            <a href="{{ route('admin.login') }}" class="btn-404 btn-404-ghost">
                <i class="mdi mdi-login"></i>
                Sign In
            </a>
        </div>

        <!-- Footer -->
        <div class="error-footer">
            Bacha Stylo Admin &copy; {{ date('Y') }}
        </div>
    </div>

    <script>
        // Mouse-tracking parallax on the orbs
        document.addEventListener('mousemove', function(e) {
            const x = (e.clientX / window.innerWidth - 0.5) * 2;
            const y = (e.clientY / window.innerHeight - 0.5) * 2;
            document.querySelectorAll('.orb').forEach(function(orb, i) {
                const factor = (i + 1) * 8;
                orb.style.transform = 'translate(' + (x * factor) + 'px, ' + (y * factor) + 'px)';
            });
        });
    </script>
</body>
</html>
