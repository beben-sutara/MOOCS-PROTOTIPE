<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MoocsPangarti')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #a435f0;
            --primary-dark: #8710d8;
            --secondary: #5624d0;
            --ink: #1c1d1f;
            --muted: #6a6f73;
            --surface: #ffffff;
            --surface-soft: #f7f9fa;
            --border: #d1d7dc;
            --success: #1e9b5a;
            --warning: #b4690e;
            --danger: #b32d0f;
            --shadow-soft: 0 10px 30px rgba(28, 29, 31, 0.08);
            --shadow-hover: 0 18px 40px rgba(28, 29, 31, 0.12);
        }

        body {
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
            color: var(--ink);
            background: var(--surface-soft);
            min-height: 100vh;
        }

        a {
            color: inherit;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.96) !important;
            border-bottom: 1px solid var(--border);
            padding: 0.9rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.35rem;
            color: var(--ink) !important;
            letter-spacing: -0.02em;
        }

        .nav-link {
            color: var(--ink) !important;
            font-weight: 600;
            margin: 0 0.35rem;
            border-radius: 999px;
            padding: 0.6rem 1rem !important;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            color: var(--primary) !important;
            background: rgba(164, 53, 240, 0.08);
        }

        .nav-link.active {
            color: var(--primary) !important;
            background: rgba(164, 53, 240, 0.08);
        }

        .container-main {
            padding: 2rem 0 3rem;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: var(--shadow-soft);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .card-header {
            border-bottom: 1px solid var(--border) !important;
            border-radius: 18px 18px 0 0 !important;
        }

        .btn {
            border-radius: 999px;
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            letter-spacing: 0.01em;
            transition: all 0.2s ease;
        }

        .btn-sm {
            padding: 0.5rem 0.9rem;
        }

        .btn-primary {
            background: var(--primary);
            border: 1px solid var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(164, 53, 240, 0.2);
        }

        .btn-outline-primary {
            border: 1px solid var(--ink);
            color: var(--ink);
            background: #fff;
        }

        .btn-outline-primary:hover {
            background: var(--ink);
            border-color: var(--ink);
            color: #fff;
        }

        .btn-outline-secondary {
            border: 1px solid var(--border);
            color: var(--ink);
            background: #fff;
        }

        .btn-outline-secondary:hover {
            background: #f3f5f7;
            border-color: var(--border);
            color: var(--ink);
        }

        .btn-secondary {
            background: var(--ink);
            border-color: var(--ink);
            color: #fff;
        }

        .btn-secondary:hover {
            background: #000;
            border-color: #000;
            color: #fff;
        }

        .form-control,
        .form-select {
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 0.85rem 1rem;
            box-shadow: none !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(164, 53, 240, 0.55);
            box-shadow: 0 0 0 0.2rem rgba(164, 53, 240, 0.12) !important;
        }

        .level-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 8px 16px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 1.1rem;
            min-width: 50px;
            text-align: center;
        }

        .level-badge-animated {
            box-shadow: 0 12px 28px rgba(164, 53, 240, 0.22);
            animation: badgeFloat 3.2s ease-in-out infinite;
        }

        .level-badge-celebrating {
            animation:
                badgeFloat 3.2s ease-in-out infinite,
                badgeBurstGlow 0.9s cubic-bezier(0.22, 1, 0.36, 1) 2;
        }

        .xp-bar {
            background: #e5e7eb;
            height: 8px;
            border-radius: 10px;
            overflow: hidden;
        }

        .xp-bar-fill {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            height: 100%;
            transition: width 0.5s ease;
        }

        .progress-bar[data-progress-width],
        .xp-bar-fill[data-progress-width] {
            width: 0;
            transition: width 1.1s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .nav-xp-widget {
            min-width: 220px;
            padding: 0.6rem 0.9rem;
            border-radius: 20px;
            border: 1px solid rgba(164, 53, 240, 0.12);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(247, 241, 255, 0.98) 100%);
            box-shadow: 0 12px 24px rgba(86, 36, 208, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .nav-xp-widget:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(86, 36, 208, 0.14);
            border-color: rgba(164, 53, 240, 0.25);
        }

        .nav-xp-widget__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.45rem;
        }

        .nav-xp-widget__badge {
            min-width: auto;
            padding: 0.35rem 0.7rem;
            font-size: 0.8rem;
        }

        .nav-xp-widget__xp {
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--ink);
            white-space: nowrap;
        }

        .nav-xp-widget__bar {
            height: 6px;
            margin-bottom: 0.35rem;
        }

        .nav-xp-widget__meta {
            display: block;
            color: var(--muted);
            font-size: 0.72rem;
            line-height: 1.3;
        }

        .metric-pop {
            display: inline-block;
            transform-origin: center;
            animation: metricPop 0.65s ease-out both;
        }

        @keyframes badgeFloat {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-4px);
            }
        }

        @keyframes metricPop {
            0% {
                opacity: 0;
                transform: scale(0.92);
            }
            65% {
                opacity: 1;
                transform: scale(1.04);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes badgeBurstGlow {
            0% {
                box-shadow: 0 12px 28px rgba(164, 53, 240, 0.22);
                filter: saturate(1);
            }
            30% {
                box-shadow:
                    0 0 0 8px rgba(164, 53, 240, 0.12),
                    0 0 28px rgba(164, 53, 240, 0.42),
                    0 0 52px rgba(245, 158, 11, 0.22);
                filter: saturate(1.18);
            }
            100% {
                box-shadow: 0 12px 28px rgba(164, 53, 240, 0.22);
                filter: saturate(1);
            }
        }

        .alert {
            border-radius: 16px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }

        .app-toast-container {
            position: fixed;
            top: 88px;
            right: 1rem;
            z-index: 1085;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            max-width: min(360px, calc(100vw - 2rem));
        }

        .app-toast {
            border: 0;
            border-radius: 18px;
            box-shadow: var(--shadow-hover);
            overflow: hidden;
        }

        .app-toast .toast-body {
            font-size: 0.95rem;
        }

        .app-toast.text-bg-warning {
            color: #1c1d1f !important;
        }

        .app-celebration-layer {
            position: fixed;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 1080;
        }

        .confetti-piece {
            position: absolute;
            top: -12vh;
            width: var(--confetti-size, 10px);
            height: calc(var(--confetti-size, 10px) * 1.9);
            border-radius: 4px;
            opacity: 0;
            will-change: transform, opacity;
            animation: confettiDrop var(--confetti-duration, 1.9s) ease-out forwards;
            animation-delay: var(--confetti-delay, 0s);
        }

        @keyframes confettiDrop {
            0% {
                opacity: 0;
                transform: translate3d(0, -8vh, 0) rotate(0deg) scale(0.9);
            }
            12% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                transform: translate3d(var(--confetti-drift, 0px), 110vh, 0) rotate(var(--confetti-rotate, 540deg)) scale(1);
            }
        }

        .footer {
            background: var(--ink);
            color: rgba(255, 255, 255, 0.84);
            margin-top: 4rem;
            padding: 3rem 0;
        }

        .hero {
            background:
                radial-gradient(circle at top right, rgba(164, 53, 240, 0.18), transparent 28%),
                linear-gradient(180deg, #ffffff 0%, #f7f9fa 100%);
            border: 1px solid var(--border);
            color: var(--ink);
            padding: 4rem;
            border-radius: 24px;
            margin-bottom: 3rem;
            box-shadow: var(--shadow-soft);
        }

        .hero h1 {
            font-weight: 700;
            font-size: clamp(2.3rem, 4vw, 4rem);
            margin-bottom: 1rem;
            letter-spacing: -0.04em;
        }

        .hero p {
            font-size: 1.05rem;
            color: var(--muted);
            margin-bottom: 1.6rem;
        }

        .stat-card {
            text-align: center;
            padding: 1.5rem;
            background: white;
            border-radius: 18px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-soft);
        }

        .stat-card h3 {
            color: var(--ink);
            font-weight: 700;
            font-size: 2rem;
        }

        .stat-card p {
            color: var(--muted);
            font-size: 0.9rem;
        }

        .dropdown-menu {
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-soft);
            padding: 0.75rem;
        }

        .dropdown-item {
            border-radius: 12px;
            padding: 0.65rem 0.85rem;
            font-weight: 500;
        }

        .dropdown-item:hover {
            background: rgba(164, 53, 240, 0.08);
            color: var(--primary);
        }

        .section-title {
            font-weight: 700;
            font-size: clamp(1.6rem, 3vw, 2.5rem);
            letter-spacing: -0.03em;
            margin-bottom: 0.75rem;
        }

        .section-subtitle {
            color: var(--muted);
            max-width: 720px;
        }

        .soft-panel {
            background: linear-gradient(135deg, #ffffff 0%, #fbf7ff 100%);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow-soft);
        }

        .market-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 999px;
            padding: 0.45rem 0.9rem;
            background: rgba(164, 53, 240, 0.08);
            color: var(--primary);
            font-weight: 700;
            font-size: 0.85rem;
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(164, 53, 240, 0.08);
            color: var(--primary);
            font-size: 1.5rem;
        }

        .surface-muted {
            background: #f8f9fb;
            border: 1px solid var(--border);
            border-radius: 20px;
        }

        .course-thumbnail-frame {
            --thumbnail-height: 180px;
            position: relative;
            width: 100%;
            height: var(--thumbnail-height);
            overflow: hidden;
            background: linear-gradient(135deg, #ffffff 0%, #f4ebff 100%);
        }

        .course-thumbnail-frame::after {
            content: "";
            position: absolute;
            inset: auto 0 0 0;
            height: 45%;
            background: linear-gradient(180deg, rgba(28, 29, 31, 0) 0%, rgba(28, 29, 31, 0.2) 100%);
            pointer-events: none;
        }

        .course-thumbnail-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .course-thumbnail-fallback {
            position: relative;
            z-index: 1;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background:
                radial-gradient(circle at top right, rgba(164, 53, 240, 0.18), transparent 25%),
                linear-gradient(135deg, #ffffff 0%, #f7f1ff 100%);
            text-align: center;
        }

        .course-thumbnail-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(164, 53, 240, 0.18);
            color: var(--primary);
            font-size: 0.78rem;
            font-weight: 700;
            backdrop-filter: blur(8px);
        }

        .course-thumbnail-status {
            position: absolute;
            top: 1rem;
            left: 1rem;
            z-index: 2;
        }

        .text-primary {
            color: var(--primary) !important;
        }

        .text-warning {
            color: var(--warning) !important;
        }

        .text-success {
            color: var(--success) !important;
        }

        .table > :not(caption) > * > * {
            border-bottom-color: var(--border);
            vertical-align: middle;
        }

        @media (max-width: 991.98px) {
            .hero {
                padding: 2rem;
            }

            .container-main {
                padding-top: 1.5rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .level-badge-animated,
            .metric-pop,
            .level-badge-celebrating {
                animation: none !important;
            }

            .progress-bar[data-progress-width],
            .xp-bar-fill[data-progress-width] {
                transition: none !important;
            }
        }
    </style>
    @yield('extra_css')
</head>
<body>
    {{-- Navigation --}}
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-mortarboard-fill text-primary"></i> MoocsPangarti
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    @if(Auth::check())
                        @if(Auth::user()->role === 'admin')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link" href="/courses">Courses</a>
                        </li>
                        @if(Auth::user()->role === 'user')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('instructor.apply') }}">Become Instructor</a>
                            </li>
                        @endif
                        @if(Auth::user()->role !== 'user')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('courses.manage') }}">Manage Courses</a>
                            </li>
                        @endif
                        @if(Auth::user()->role === 'user')
                            <li class="nav-item">
                                <a class="nav-link" href="/leaderboard">Leaderboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('certificates.index') }}">Sertifikat</a>
                            </li>
                        @endif
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                            </a>
                                 <ul class="dropdown-menu dropdown-menu-end">
                                 <li><a class="dropdown-item" href="/dashboard">Dashboard</a></li>
                                 @if(Auth::user()->role === 'admin')
                                     <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
                                     <li><a class="dropdown-item" href="{{ route('admin.courses.index') }}">Moderate Courses</a></li>
                                     <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Manage Users</a></li>
                                     <li><a class="dropdown-item" href="{{ route('admin.instructor-applications.index') }}">Instructor Applications</a></li>
                                 @endif
                                 @if(Auth::user()->role === 'user')
                                     <li><a class="dropdown-item" href="{{ route('instructor.apply') }}">Become Instructor</a></li>
                                     <li><a class="dropdown-item" href="{{ route('certificates.index') }}">Sertifikat Saya</a></li>
                                 @endif
                                 @if(Auth::user()->role !== 'user')
                                     <li><a class="dropdown-item" href="{{ route('courses.manage') }}">Manage Courses</a></li>
                                 @endif
                                <li><a class="dropdown-item" href="/profile">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="/logout" style="display:inline;">
                                        @csrf
                                        <button class="dropdown-item" type="submit">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="btn btn-outline-secondary btn-sm ms-lg-2" href="/login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-sm btn-primary ms-lg-2" href="/register">Daftar Gratis</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    {{-- Alert Messages --}}
    <div class="container container-main">
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Error!</strong>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- Main Content --}}
    <main class="container container-main">
        @yield('content')
    </main>

    <div id="appToastContainer" class="app-toast-container" aria-live="polite" aria-atomic="true"></div>
    <div id="appCelebrationLayer" class="app-celebration-layer" aria-hidden="true"></div>

    {{-- Footer --}}
    <footer class="footer">
        <div class="container">
            <div class="row g-4 align-items-start">
                <div class="col-lg-5">
                    <h5 class="text-white mb-3">
                        <i class="bi bi-mortarboard-fill text-primary"></i> MoocsPangarti
                    </h5>
                    <p class="mb-0">Platform pembelajaran modern untuk belajar, mengajar, dan membangun course online dengan pengalaman yang lebih profesional.</p>
                </div>
                <div class="col-lg-3">
                    <h6 class="text-white mb-3">Navigasi</h6>
                    <div class="d-flex flex-column gap-2">
                        <a href="/" class="text-decoration-none text-white-50">Home</a>
                        <a href="/courses" class="text-decoration-none text-white-50">Courses</a>
                        @auth
                            @if(Auth::user()->role === 'user')
                                <a href="/leaderboard" class="text-decoration-none text-white-50">Leaderboard</a>
                            @endif
                        @endauth
                    </div>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-white mb-3">Build your learning marketplace</h6>
                    <p class="mb-0">Tampilan baru ini dibuat untuk memberi nuansa course marketplace yang lebih modern, ringan, dan fokus ke konten.</p>
                </div>
            </div>
            <hr class="border-secondary-subtle my-4">
            <p class="mb-0 text-white-50">&copy; 2026 MoocsPangarti. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
    (function () {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        document.querySelectorAll('[data-progress-width]').forEach(function (element) {
            const targetWidth = Math.max(0, Math.min(100, Number(element.dataset.progressWidth || 0)));

            if (prefersReducedMotion) {
                element.style.width = `${targetWidth}%`;
                return;
            }

            element.style.width = '0%';
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(function () {
                    element.style.width = `${targetWidth}%`;
                });
            });
        });

        const countFormatter = function (value, decimals) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            }).format(value);
        };

        const animateCounter = function (element) {
            if (element.dataset.countStarted === 'true') {
                return;
            }

            element.dataset.countStarted = 'true';

            const targetValue = Number(element.dataset.countUp || 0);
            const duration = Number(element.dataset.countUpDuration || 1200);
            const decimals = Number(element.dataset.countUpDecimals || 0);
            const prefix = element.dataset.countUpPrefix || '';
            const suffix = element.dataset.countUpSuffix || '';

            const render = function (value) {
                element.textContent = `${prefix}${countFormatter(value, decimals)}${suffix}`;
            };

            if (prefersReducedMotion) {
                render(targetValue);
                return;
            }

            const startTime = performance.now();

            const tick = function (now) {
                const elapsed = Math.min((now - startTime) / duration, 1);
                const eased = 1 - Math.pow(1 - elapsed, 3);
                render(targetValue * eased);

                if (elapsed < 1) {
                    window.requestAnimationFrame(tick);
                } else {
                    render(targetValue);
                }
            };

            window.requestAnimationFrame(tick);
        };

        const countElements = document.querySelectorAll('[data-count-up]');
        if ('IntersectionObserver' in window && !prefersReducedMotion) {
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.35 });

            countElements.forEach(function (element) {
                observer.observe(element);
            });
        } else {
            countElements.forEach(animateCounter);
        }
    })();

    (function () {
        const celebrationLayer = document.getElementById('appCelebrationLayer');
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const queuedCelebrationStorageKey = 'app_celebration';
        const confettiColors = ['#a435f0', '#5624d0', '#f59e0b', '#1e9b5a', '#38bdf8', '#fb7185'];
        const celebrateBadges = function () {
            document.querySelectorAll('[data-celebration-badge]').forEach(function (badge) {
                badge.classList.remove('level-badge-celebrating');
                void badge.offsetWidth;
                badge.classList.add('level-badge-celebrating');
                window.setTimeout(function () {
                    badge.classList.remove('level-badge-celebrating');
                }, 1900);
            });
        };

        window.launchLevelUpCelebration = function () {
            if (!celebrationLayer || prefersReducedMotion) {
                return;
            }

            celebrateBadges();

            const pieceCount = 42;

            for (let index = 0; index < pieceCount; index += 1) {
                const piece = document.createElement('span');
                const size = 8 + Math.random() * 8;

                piece.className = 'confetti-piece';
                piece.style.left = `${Math.random() * 100}%`;
                piece.style.background = confettiColors[Math.floor(Math.random() * confettiColors.length)];
                piece.style.setProperty('--confetti-size', `${size}px`);
                piece.style.setProperty('--confetti-drift', `${-140 + Math.random() * 280}px`);
                piece.style.setProperty('--confetti-rotate', `${-360 + Math.random() * 1080}deg`);
                piece.style.setProperty('--confetti-delay', `${Math.random() * 0.18}s`);
                piece.style.setProperty('--confetti-duration', `${1.6 + Math.random() * 0.8}s`);

                celebrationLayer.appendChild(piece);
                window.setTimeout(function () {
                    piece.remove();
                }, 2600);
            }
        };

        window.queueAppCelebration = function (payload) {
            if (!payload || typeof payload !== 'object') {
                return;
            }

            sessionStorage.setItem(queuedCelebrationStorageKey, JSON.stringify(payload));
        };

        const queuedCelebrationRaw = sessionStorage.getItem(queuedCelebrationStorageKey);
        if (!queuedCelebrationRaw) {
            return;
        }

        sessionStorage.removeItem(queuedCelebrationStorageKey);

        try {
            const queuedCelebration = JSON.parse(queuedCelebrationRaw);
            if (queuedCelebration?.type === 'level-up') {
                window.setTimeout(function () {
                    window.launchLevelUpCelebration();
                }, 180);
            }
        } catch (error) {
            console.error('Failed to restore queued celebration.', error);
        }
    })();

    (function () {
        const toastContainer = document.getElementById('appToastContainer');
        const toastClassMap = {
            success: 'text-bg-success',
            danger: 'text-bg-danger',
            warning: 'text-bg-warning',
            info: 'text-bg-primary',
            primary: 'text-bg-primary',
        };
        const queuedToastStorageKey = 'app_toasts';

        window.showAppToast = function (options) {
            if (!toastContainer || !window.bootstrap?.Toast) {
                return;
            }

            const {
                title = 'Notifikasi',
                message = '',
                variant = 'primary',
                icon = 'bi-info-circle-fill',
                delay = 5000,
            } = options || {};

            const toast = document.createElement('div');
            toast.className = `toast app-toast align-items-start ${toastClassMap[variant] || toastClassMap.primary}`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <div class="fw-semibold mb-1"><i class="bi ${icon} me-2"></i>${title}</div>
                        <div>${message}</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;

            if (variant === 'warning') {
                const closeButton = toast.querySelector('.btn-close');
                closeButton?.classList.remove('btn-close-white');
            }

            toastContainer.appendChild(toast);
            const toastInstance = new bootstrap.Toast(toast, { delay });
            toast.addEventListener('hidden.bs.toast', function () {
                toast.remove();
            });
            toastInstance.show();
        };

        window.queueAppToasts = function (toasts) {
            if (!Array.isArray(toasts) || toasts.length === 0) {
                return;
            }

            sessionStorage.setItem(queuedToastStorageKey, JSON.stringify(toasts));
        };

        const queuedToastsRaw = sessionStorage.getItem(queuedToastStorageKey);
        if (!queuedToastsRaw) {
            return;
        }

        sessionStorage.removeItem(queuedToastStorageKey);

        try {
            const queuedToasts = JSON.parse(queuedToastsRaw);
            if (Array.isArray(queuedToasts)) {
                queuedToasts.forEach(function (toast, index) {
                    window.setTimeout(function () {
                        window.showAppToast(toast);
                    }, index * 250);
                });
            }
        } catch (error) {
            console.error('Failed to restore queued toasts.', error);
        }
    })();
    </script>
    @yield('extra_js')
</body>
</html>
