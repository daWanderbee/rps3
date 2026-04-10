<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #0a0a0f;
            --surface: #111118;
            --border: rgba(255,255,255,0.08);
            --border-focus: rgba(200,170,255,0.5);
            --text: #e8e8f0;
            --muted: #6b6b80;
            --accent: #c8aaff;
            --accent-dim: rgba(200,170,255,0.12);
            --error: #ff7070;
            --error-dim: rgba(255,112,112,0.1);
        }

        body {
            min-height: 100vh;
            background-color: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'DM Mono', monospace;
            color: var(--text);
            overflow: hidden;
            position: relative;
        }

        /* Atmospheric background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 50% at 20% 50%, rgba(100,60,200,0.12) 0%, transparent 70%),
                radial-gradient(ellipse 50% 60% at 80% 30%, rgba(60,40,160,0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Grain overlay */
        body::after {
            content: '';
            position: fixed;
            inset: -50%;
            width: 200%;
            height: 200%;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
            opacity: 0.35;
            pointer-events: none;
        }

        .card {
            position: relative;
            width: 100%;
            max-width: 420px;
            padding: 56px 48px 48px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 2px;
            animation: rise 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        /* Top accent line */
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 48px;
            right: 48px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            opacity: 0.6;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .eyebrow {
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            font-weight: 300;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 16px;
            opacity: 0;
            animation: rise 0.5s 0.15s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 38px;
            font-weight: 400;
            line-height: 1.1;
            letter-spacing: -0.01em;
            color: var(--text);
            margin-bottom: 40px;
            opacity: 0;
            animation: rise 0.5s 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        h1 em {
            font-style: italic;
            color: var(--accent);
        }

        /* Error flash */
        .error-msg {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: var(--error-dim);
            border: 1px solid rgba(255,112,112,0.2);
            border-radius: 2px;
            font-size: 12px;
            color: var(--error);
            letter-spacing: 0.02em;
            margin-bottom: 28px;
            animation: rise 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .error-msg::before {
            content: '!';
            display: flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            border: 1px solid var(--error);
            border-radius: 50%;
            font-size: 10px;
            flex-shrink: 0;
        }

        .form-group {
            margin-bottom: 20px;
            opacity: 0;
            animation: rise 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.28s; }
        .form-group:nth-child(2) { animation-delay: 0.34s; }

        label {
            display: block;
            font-size: 10px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 10px;
            transition: color 0.2s;
        }

        .form-group:focus-within label {
            color: var(--accent);
        }

        input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 2px;
            color: var(--text);
            font-family: 'DM Mono', monospace;
            font-size: 13px;
            font-weight: 300;
            letter-spacing: 0.04em;
            outline: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }

        input::placeholder {
            color: var(--muted);
            opacity: 0.5;
        }

        input:focus {
            border-color: var(--border-focus);
            background: var(--accent-dim);
            box-shadow: 0 0 0 3px rgba(200,170,255,0.06);
        }

        .btn {
            width: 100%;
            margin-top: 32px;
            padding: 16px;
            background: var(--accent);
            border: none;
            border-radius: 2px;
            color: #0a0a0f;
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            font-weight: 400;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: opacity 0.2s, transform 0.15s;
            opacity: 0;
            animation: rise 0.5s 0.42s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.25) 50%, transparent 100%);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }

        .btn:hover { opacity: 0.88; transform: translateY(-1px); }
        .btn:hover::after { transform: translateX(100%); }
        .btn:active { transform: translateY(0px); }

        .footer-text {
            margin-top: 28px;
            text-align: center;
            font-size: 11px;
            color: var(--muted);
            letter-spacing: 0.04em;
            opacity: 0;
            animation: rise 0.5s 0.48s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .footer-text a {
            color: var(--accent);
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .footer-text a:hover { opacity: 1; }
    </style>
</head>
<body>

<div class="card">

    <p class="eyebrow">Welcome back</p>
    <h1>Sign <em>in</em><br>to continue</h1>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="error-msg">
            <?= $this->session->flashdata('error') ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/login">
        <?= $this->security->get_csrf_field() ?>

        <div class="form-group">
            <label for="emp_code">Employee Code</label>
            <input
                id="emp_code"
                name="emp_code"
                type="text"
                placeholder="Enter your employee code"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                placeholder="••••••••"
                required
                autocomplete="current-password"
            >
        </div>

        <button type="submit" class="btn">Login &rarr;</button>
    </form>

</div>

</body>
</html>