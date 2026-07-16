<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'COG-TOR System') }} — Sign In</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:    #384a32;
            --navy-2:  #4b6043;
            --navy-3:  #658354;
            --navy-4:  #75975e;
            --gold:    #c9a84c;
            --gold-l:  #e8c96e;
            --gold-xl: #f5e0a0;
            --gold-d:  #9e7428;
            --cream:   #f6f8f2;
            --cream-2: #eaf1e3;
            --white:   #ffffff;
            --text:    #111827;
            --muted:   #64748b;
            --border:  #e2e8f0;
            --error:   #ef4444;
        }

        html, body { height: 100%; font-family: 'DM Sans', sans-serif; background: var(--cream); color: var(--text); -webkit-font-smoothing: antialiased; }

        /* ─── LAYOUT ─────────────────────────────────── */
        .auth-shell { display: flex; min-height: 100vh; }

        /* ─── LEFT ───────────────────────────────────── */
        .panel-left {
            flex: 0 0 42%;
            background: var(--navy);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 3rem 3rem 2.5rem;
        }

        /* Layered background glow */
        .panel-left::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 90% 55% at 15% 90%, rgba(201,168,76,0.14) 0%, transparent 60%),
                radial-gradient(ellipse 70% 45% at 85% 5%,  rgba(201,168,76,0.09) 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 50% 50%, rgba(75,96,67,0.5) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Decorative rings */
        .ring {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(201,168,76,0.12);
            pointer-events: none;
        }
        .ring-1 { width: 480px; height: 480px; top: -160px; right: -160px; }
        .ring-2 { width: 360px; height: 360px; bottom: -140px; left: -120px; }
        .ring-3 { width: 200px; height: 200px; top: 50%; left: -60px; margin-top: -100px; border-color: rgba(201,168,76,0.07); }

        /* Dot grid */
        .dots {
            position: absolute;
            top: 2.25rem; left: 2.25rem;
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            opacity: 0.15;
            pointer-events: none;
        }
        .dots span { display: block; width: 3px; height: 3px; border-radius: 50%; background: var(--gold); }

        /* Brand */
        .brand {
            position: relative; z-index: 2;
            text-align: center;
            margin-bottom: 2.75rem;
        }

        .brand-seal {
            width: 68px; height: 68px;
            background: linear-gradient(145deg, var(--gold-l) 0%, var(--gold) 50%, var(--gold-d) 100%);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.1rem;
            box-shadow:
                0 0 0 6px rgba(201,168,76,0.12),
                0 8px 28px rgba(201,168,76,0.30),
                0 2px 6px rgba(0,0,0,0.30);
        }
        .brand-seal i { font-size: 1.65rem; color: var(--navy); }

        .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.45rem;
            font-weight: 700;
            color: var(--white);
            line-height: 1.2;
            letter-spacing: 0.01em;
            margin-bottom: 0.3rem;
        }
        .brand-tag {
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--gold);
        }

        /* SVG Illustration area */
        .illus-wrap {
            position: relative; z-index: 2;
            width: 260px; height: 220px;
            margin: 0 auto;
        }

        .illus-wrap svg {
            width: 100%; height: 100%;
            filter: drop-shadow(0 12px 32px rgba(0,0,0,0.35));
            animation: floatDoc 4s ease-in-out infinite;
        }

        @keyframes floatDoc {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-10px); }
        }

        /* Tagline */
        .tagline {
            position: relative; z-index: 2;
            text-align: center;
            margin-top: 2.25rem;
            padding: 0 0.5rem;
        }
        .tagline p {
            font-size: 0.875rem;
            line-height: 1.7;
            color: rgba(255,255,255,0.48);
            max-width: 270px;
            margin: 0 auto;
        }
        .tagline strong { color: var(--gold-l); font-weight: 500; }

        .tagline-rule {
            width: 36px; height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            border-radius: 2px;
            margin: 1.1rem auto 0;
        }

        /* Version badge bottom */
        .left-footer {
            position: absolute; bottom: 1.5rem; left: 0; right: 0;
            text-align: center; z-index: 2;
            font-size: 0.68rem;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.2);
            text-transform: uppercase;
        }

        /* ─── RIGHT ──────────────────────────────────── */
        .panel-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--cream);
            padding: 2.5rem 2rem;
            position: relative;
        }

        /* Subtle cream texture */
        .panel-right::before {
            content: '';
            position: absolute; inset: 0;
            background-image: radial-gradient(circle at 80% 20%, rgba(201,168,76,0.06) 0%, transparent 50%),
                              radial-gradient(circle at 10% 80%, rgba(56,74,50,0.04) 0%, transparent 45%);
            pointer-events: none;
        }

        .form-card {
            position: relative; z-index: 1;
            width: 100%;
            max-width: 400px;
            background: var(--white);
            border-radius: 20px;
            padding: 2.5rem 2.25rem 2rem;
            box-shadow:
                0 1px 3px rgba(0,0,0,0.04),
                0 8px 24px rgba(56,74,50,0.08),
                0 24px 56px rgba(56,74,50,0.06);
            border: 1px solid rgba(226,232,240,0.8);
            animation: riseIn 0.5s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes riseIn {
            from { opacity: 0; transform: translateY(20px) scale(0.99); }
            to   { opacity: 1; transform: translateY(0)    scale(1); }
        }

        /* Card top accent */
        .card-accent {
            position: absolute;
            top: 0; left: 2rem; right: 2rem; height: 3px;
            background: linear-gradient(90deg, var(--gold-d), var(--gold-l), var(--gold-d));
            border-radius: 0 0 4px 4px;
        }

        /* Header */
        .form-header { margin-bottom: 2rem; }

        .eyebrow {
            display: inline-flex; align-items: center; gap: 0.5rem;
            font-size: 0.7rem; font-weight: 600;
            letter-spacing: 0.15em; text-transform: uppercase;
            color: var(--gold-d);
            margin-bottom: 0.65rem;
        }
        .eyebrow::before {
            content: ''; display: inline-block;
            width: 16px; height: 2px;
            background: var(--gold-d); border-radius: 2px;
        }

        .form-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.9rem; font-weight: 700;
            color: var(--navy);
            line-height: 1.15;
            margin-bottom: 0.45rem;
        }

        .form-sub {
            font-size: 0.845rem;
            color: var(--muted);
        }

        /* Fields */
        .field { margin-bottom: 1.2rem; }

        .field-label {
            display: block;
            font-size: 0.75rem; font-weight: 600;
            letter-spacing: 0.06em; text-transform: uppercase;
            color: var(--navy-2);
            margin-bottom: 0.45rem;
        }

        .field-input-wrap { position: relative; }

        .field-icon {
            position: absolute; left: 13px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8; font-size: 0.8rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        .field-input {
            width: 100%;
            padding: 0.72rem 1rem 0.72rem 2.5rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            color: var(--text);
            background: #f8fafc;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            outline: none;
            transition: all 0.2s;
        }

        .field-input::placeholder { color: #b0bec5; }

        .field-input:focus {
            background: var(--white);
            border-color: var(--navy-3);
            box-shadow: 0 0 0 3px rgba(101,131,84,0.08);
        }

        .field-input:focus ~ .field-icon { color: var(--navy-3); }
        .field-input-wrap:focus-within .field-icon { color: var(--navy-3); }

        .eye-btn {
            position: absolute; right: 11px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #94a3b8; font-size: 0.8rem;
            padding: 4px 5px; border-radius: 5px;
            transition: color 0.2s, background 0.2s;
        }
        .eye-btn:hover { color: var(--navy); background: #f1f5f9; }

        /* Error */
        .field-err {
            display: flex; align-items: center; gap: 0.3rem;
            font-size: 0.75rem; font-weight: 500;
            color: var(--error); margin-top: 0.4rem;
        }

        /* Session alert */
        .alert-success {
            display: flex; align-items: center; gap: 0.5rem;
            background: #f0fdf4; color: #166534;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 0.6rem 0.9rem;
            font-size: 0.8rem; font-weight: 500;
            margin-bottom: 1.4rem;
        }

        /* Footer row */
        .field-row {
            display: flex; align-items: center;
            justify-content: space-between;
            margin: 1.4rem 0 1.6rem;
        }

        .check-label {
            display: inline-flex; align-items: center; gap: 0.45rem;
            font-size: 0.815rem; color: var(--muted); cursor: pointer;
        }
        .check-label input[type="checkbox"] {
            width: 14px; height: 14px;
            accent-color: var(--navy-3); cursor: pointer;
        }

        .link-forgot {
            font-size: 0.815rem; font-weight: 500;
            color: var(--navy-3); text-decoration: none;
            transition: color 0.2s;
        }
        .link-forgot:hover { color: var(--gold-d); }

        /* Button */
        .btn-submit {
            width: 100%;
            padding: 0.85rem 1.5rem;
            display: flex; align-items: center; justify-content: center; gap: 0.65rem;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-3) 100%);
            color: var(--white);
            border: none; border-radius: 11px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem; font-weight: 600;
            letter-spacing: 0.03em;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 14px rgba(56,74,50,0.25), 0 1px 3px rgba(56,74,50,0.15);
        }

        .btn-submit:hover {
            transform: translateY(-1.5px);
            box-shadow: 0 8px 22px rgba(56,74,50,0.32), 0 2px 6px rgba(56,74,50,0.18);
        }

        .btn-submit:active { transform: translateY(0); }

        .btn-icon-wrap {
            width: 26px; height: 26px;
            background: rgba(255,255,255,0.14);
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem;
        }

        /* Card footer */
        .card-footer {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border);
            text-align: center;
            font-size: 0.72rem;
            color: #94a3b8;
            display: flex; align-items: center; justify-content: center; gap: 0.4rem;
        }

        .panel-watermark {
            position: absolute;
            width: 640px; height: 640px;
            right: -160px; bottom: -160px;
            opacity: 0.08;
            pointer-events: none;
            z-index: 0;
        }

        .brand-seal-img {
            width: 84px; height: 84px;
            object-fit: contain;
            margin: 0 auto 1.1rem;
            display: block;
            filter: drop-shadow(0 8px 20px rgba(0,0,0,0.35));
        }

        .brand-slogan {
            font-size: 0.62rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(232,201,110,0.75);
            margin-top: 0.5rem;
        }
        .brand-slogan span { color: rgba(232,201,110,0.4); margin: 0 0.35rem; }

        /* ─── RESPONSIVE ─────────────────────────────── */
        @media (max-width: 768px) {
            .auth-shell { flex-direction: column; }
            .panel-left { flex: 0 0 auto; min-height: 220px; padding: 2rem 1.5rem 1.75rem; }
            .illus-wrap { width: 140px; height: 120px; }
            .tagline { display: none; }
            .panel-right { padding: 1.75rem 1.25rem 3rem; }
            .form-card { padding: 2rem 1.5rem 1.75rem; }
        }
    </style>
</head>
<body>
<div class="auth-shell">

    {{-- ── LEFT PANEL ── --}}
    <div class="panel-left">
        <img src="{{ asset('images/logo/essu-seal-full.png') }}" alt="" class="panel-watermark" aria-hidden="true">
        <div class="ring ring-1"></div>
        <div class="ring ring-2"></div>
        <div class="ring ring-3"></div>
        <div class="dots">@for($i=0;$i<30;$i++)<span></span>@endfor</div>

        <div class="brand">
            <img src="{{ asset('images/logo/essu-seal.png') }}" alt="Eastern Samar State University Seal" class="brand-seal-img">
            <div class="brand-name">COG-TOR System</div>
            <div class="brand-tag">Academic Records Management</div>
            <div class="brand-slogan">Excellence <span>&middot;</span> Integrity <span>&middot;</span> Accountability</div>
        </div>

        {{-- SVG Document Illustration --}}
        <div class="illus-wrap">
            <svg viewBox="0 0 260 220" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Back document (shadow layer) -->
                <rect x="55" y="30" width="145" height="175" rx="10" fill="#4b6043" opacity="0.7"/>

                <!-- Main document body -->
                <rect x="40" y="18" width="145" height="175" rx="10" fill="#658354"/>
                <rect x="40" y="18" width="145" height="175" rx="10" stroke="rgba(201,168,76,0.25)" stroke-width="1.5"/>

                <!-- Gold header bar -->
                <rect x="40" y="18" width="145" height="38" rx="10" fill="url(#goldHeader)"/>
                <rect x="40" y="42" width="145" height="14" fill="#c9a84c" opacity="0.85"/>

                <!-- Seal circle -->
                <circle cx="112" cy="37" r="16" fill="url(#sealGrad)" opacity="0.95"/>
                <circle cx="112" cy="37" r="12" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
                <text x="112" y="42" text-anchor="middle" font-size="12" fill="#384a32" font-family="serif">✦</text>

                <!-- Document lines -->
                <rect x="58" y="70" width="108" height="5" rx="2.5" fill="rgba(255,255,255,0.55)"/>
                <rect x="58" y="83" width="85"  height="4" rx="2" fill="rgba(255,255,255,0.25)"/>
                <rect x="58" y="97" width="108" height="3" rx="1.5" fill="rgba(201,168,76,0.35)"/>
                <rect x="58" y="107" width="70" height="3" rx="1.5" fill="rgba(255,255,255,0.18)"/>
                <rect x="58" y="120" width="108" height="3" rx="1.5" fill="rgba(255,255,255,0.18)"/>
                <rect x="58" y="131" width="90" height="3" rx="1.5" fill="rgba(255,255,255,0.18)"/>
                <rect x="58" y="142" width="108" height="3" rx="1.5" fill="rgba(255,255,255,0.18)"/>
                <rect x="58" y="153" width="60" height="3" rx="1.5" fill="rgba(255,255,255,0.18)"/>

                <!-- Signature line area -->
                <rect x="58" y="168" width="55" height="1" rx="0.5" fill="rgba(201,168,76,0.6)"/>
                <rect x="58" y="175" width="40" height="2.5" rx="1.5" fill="rgba(255,255,255,0.15)"/>
                <rect x="118" y="168" width="48" height="1" rx="0.5" fill="rgba(201,168,76,0.6)"/>
                <rect x="118" y="175" width="35" height="2.5" rx="1.5" fill="rgba(255,255,255,0.15)"/>

                <!-- Floating badge -->
                <g transform="translate(165, 10)" filter="url(#badgeShadow)">
                    <rect width="54" height="26" rx="13" fill="url(#badgeGrad)"/>
                    <text x="27" y="17" text-anchor="middle" font-size="8.5" font-family="'DM Sans',sans-serif" font-weight="600" fill="#384a32" letter-spacing="0.5">OFFICIAL</text>
                </g>

                <!-- Corner fold -->
                <path d="M185 18 L185 32 L199 18 Z" fill="rgba(201,168,76,0.4)"/>

                <defs>
                    <linearGradient id="goldHeader" x1="40" y1="18" x2="185" y2="56" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#c9a84c"/>
                        <stop offset="100%" stop-color="#9e7428"/>
                    </linearGradient>
                    <radialGradient id="sealGrad" cx="50%" cy="40%" r="60%">
                        <stop offset="0%" stop-color="#e8c96e"/>
                        <stop offset="100%" stop-color="#c9a84c"/>
                    </radialGradient>
                    <linearGradient id="badgeGrad" x1="0" y1="0" x2="54" y2="26" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#e8c96e"/>
                        <stop offset="100%" stop-color="#c9a84c"/>
                    </linearGradient>
                    <filter id="badgeShadow" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="rgba(0,0,0,0.3)"/>
                    </filter>
                </defs>
            </svg>
        </div>

        <div class="tagline">
            <p>Streamlining <strong>Certificate of Grades</strong> and <strong>Transcript of Records</strong> — from encoding to generation.</p>
            <div class="tagline-rule"></div>
        </div>

        <div class="left-footer">ESSU Guiuan Campus &nbsp;·&nbsp; v1.0</div>
    </div>

    {{-- ── RIGHT PANEL ── --}}
    <div class="panel-right">
        <div class="form-card">
            <div class="card-accent"></div>
            {{ $slot }}
        </div>
    </div>

</div>
</body>
</html>
