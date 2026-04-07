<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { background: #0d1117; color: #e6edf3; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Helvetica,Arial,sans-serif; font-size: 14px; line-height: 1.5; margin: 0; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #30363d; border-radius: 3px; }

        /* ── Sidebar ───────────────────────────────────────────── */
        .sidebar { width: 256px; background: #161b22; border-right: 1px solid #21262d; display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 30; overflow-y: auto; transition: transform .25s ease; }
        .nav-link { display: flex; align-items: center; gap: 8px; padding: 6px 8px; border-radius: 6px; color: #8b949e; font-size: 14px; font-weight: 500; text-decoration: none; transition: color .12s, background .12s; }
        .nav-link:hover { color: #e6edf3; background: #21262d; }
        .nav-link.active { color: #e6edf3; background: #21262d; font-weight: 600; }
        .nav-link svg { flex-shrink: 0; }
        .nav-section { padding: 8px 8px 4px; font-size: 11px; font-weight: 600; color: #6e7681; text-transform: uppercase; letter-spacing: .06em; }

        /* ── Overlay (mobile) ──────────────────────────────────── */
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.6); z-index: 28; }

        /* ── Buttons ───────────────────────────────────────────── */
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 5px 16px; font-size: 14px; font-weight: 500; border-radius: 6px; border: 1px solid; cursor: pointer; text-decoration: none; transition: background .12s, border-color .12s; white-space: nowrap; line-height: 20px; }
        .btn-primary { background: #238636; border-color: rgba(240,246,252,.1); color: #fff; }
        .btn-primary:hover { background: #2ea043; border-color: rgba(240,246,252,.15); }
        .btn-secondary { background: #21262d; border-color: #30363d; color: #c9d1d9; }
        .btn-secondary:hover { background: #30363d; border-color: #8b949e; color: #e6edf3; }
        .btn-danger { background: #da3633; border-color: rgba(240,246,252,.1); color: #fff; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-sm { padding: 3px 12px; font-size: 12px; }

        /* ── Forms ─────────────────────────────────────────────── */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 14px; font-weight: 600; color: #e6edf3; margin-bottom: 6px; }
        .form-hint { font-size: 12px; color: #8b949e; margin-top: 4px; }
        .form-input { display: block; width: 100%; padding: 5px 12px; font-size: 14px; line-height: 20px; color: #e6edf3; background: #0d1117; border: 1px solid #30363d; border-radius: 6px; outline: none; transition: border-color .15s, box-shadow .15s; }
        .form-input:focus { border-color: #58a6ff; box-shadow: 0 0 0 3px rgba(31,111,235,.15); }
        .form-input::placeholder { color: #6e7681; }
        input[type="date"].form-input { color-scheme: dark; }
        input[type="date"].form-input::-webkit-calendar-picker-indicator { filter: invert(0.5) brightness(1.2); cursor: pointer; opacity: .7; }
        input[type="date"].form-input::-webkit-calendar-picker-indicator:hover { opacity: 1; }
        .form-select { display: block; width: 100%; padding: 5px 28px 5px 12px; font-size: 14px; line-height: 20px; color: #e6edf3; background: #0d1117 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%236e7681' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E") no-repeat right 10px center; border: 1px solid #30363d; border-radius: 6px; outline: none; cursor: pointer; transition: border-color .15s; appearance: none; -webkit-appearance: none; }
        .form-select:focus { border-color: #58a6ff; box-shadow: 0 0 0 3px rgba(31,111,235,.15); }
        .form-textarea { display: block; width: 100%; padding: 8px 12px; font-size: 14px; line-height: 1.5; color: #e6edf3; background: #0d1117; border: 1px solid #30363d; border-radius: 6px; outline: none; resize: vertical; transition: border-color .15s; }
        .form-textarea:focus { border-color: #58a6ff; box-shadow: 0 0 0 3px rgba(31,111,235,.15); }
        .form-checkbox { width: 16px; height: 16px; border: 1px solid #30363d; border-radius: 3px; background: #0d1117; accent-color: #238636; cursor: pointer; }

        /* ── Cards / Boxes ─────────────────────────────────────── */
        .box { background: #161b22; border: 1px solid #30363d; border-radius: 6px; }
        .box-header { padding: 16px; border-bottom: 1px solid #21262d; display: flex; align-items: center; justify-content: space-between; }
        .box-header h3 { font-size: 14px; font-weight: 600; color: #e6edf3; margin: 0; }
        .box-body { padding: 16px; }

        /* ── Table ─────────────────────────────────────────────── */
        .gh-table { width: 100%; border-collapse: collapse; }
        .gh-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .gh-table thead tr { border-bottom: 1px solid #21262d; }
        .gh-table th { padding: 8px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #8b949e; }
        .gh-table tbody tr { border-bottom: 1px solid #21262d; transition: background .1s; }
        .gh-table tbody tr:last-child { border-bottom: none; }
        .gh-table tbody tr:hover { background: rgba(177,186,196,.04); }
        .gh-table td { padding: 12px 16px; font-size: 14px; color: #e6edf3; vertical-align: middle; }

        /* ── Labels / Badges ───────────────────────────────────── */
        .label { display: inline-flex; align-items: center; padding: 0 7px; font-size: 12px; font-weight: 500; line-height: 18px; border-radius: 2em; border: 1px solid transparent; white-space: nowrap; }
        .label-success { background: rgba(35,134,54,.15); border-color: rgba(63,185,80,.4); color: #3fb950; }
        .label-danger { background: rgba(218,54,51,.15); border-color: rgba(248,81,73,.4); color: #f85149; }
        .label-warning { background: rgba(210,153,34,.15); border-color: rgba(210,153,34,.4); color: #d29922; }
        .label-info { background: rgba(31,111,235,.15); border-color: rgba(88,166,255,.4); color: #58a6ff; }
        .label-default { background: rgba(110,118,129,.2); border-color: rgba(110,118,129,.4); color: #8b949e; }
        .label-creator { background: rgba(188,140,255,.12); border-color: rgba(188,140,255,.4); color: #bc8cff; }

        /* ── Flash messages ────────────────────────────────────── */
        .flash-success { padding: 12px 16px; background: rgba(35,134,54,.15); border: 1px solid rgba(63,185,80,.3); border-radius: 6px; color: #3fb950; font-size: 14px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        .flash-error { padding: 12px 16px; background: rgba(218,54,51,.15); border: 1px solid rgba(248,81,73,.3); border-radius: 6px; color: #f85149; font-size: 14px; margin-bottom: 16px; }
        .flash-error ul { margin: 4px 0 0 16px; }
        .flash-warning { padding: 12px 16px; background: rgba(210,153,34,.12); border: 1px solid rgba(210,153,34,.3); border-radius: 6px; color: #d29922; font-size: 14px; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

        /* ── Stat cards ────────────────────────────────────────── */
        .stat-card { background: #161b22; border: 1px solid #30363d; border-radius: 6px; padding: 16px; }
        .stat-card .stat-label { font-size: 12px; color: #8b949e; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 8px; }
        .stat-card .stat-value { font-size: 28px; font-weight: 600; color: #e6edf3; line-height: 1; }
        .stat-card .stat-sub { font-size: 12px; color: #6e7681; margin-top: 4px; }

        /* ── Code / Mono ───────────────────────────────────────── */
        .mono { font-family: "SFMono-Regular",Consolas,"Liberation Mono",Menlo,monospace; font-size: 12px; }
        .code-block { background: #010409; border: 1px solid #30363d; border-radius: 6px; padding: 16px; font-family: "SFMono-Regular",Consolas,"Liberation Mono",Menlo,monospace; font-size: 12px; color: #e6edf3; overflow-x: auto; }

        /* ── Dropzone ──────────────────────────────────────────── */
        .dropzone { border: 2px dashed #30363d; border-radius: 6px; padding: 32px; text-align: center; cursor: pointer; transition: border-color .15s, background .15s; }
        .dropzone:hover, .dropzone.active { border-color: #388bfd; background: rgba(31,111,235,.05); }

        /* ── Online dot ────────────────────────────────────────── */
        .online-dot { width: 8px; height: 8px; border-radius: 50%; background: #3fb950; display: inline-block; flex-shrink: 0; }

        /* ── Hamburger button ──────────────────────────────────── */
        .hamburger-btn { display: none; align-items: center; justify-content: center; width: 36px; height: 36px; background: none; border: 1px solid #30363d; border-radius: 6px; cursor: pointer; color: #8b949e; padding: 0; transition: color .12s, border-color .12s; }
        .hamburger-btn:hover { color: #e6edf3; border-color: #8b949e; }

        /* ── Badge (nav count) ─────────────────────────────────── */
        .nav-badge { background: #da3633; color: #fff; font-size: 10px; font-weight: 700; padding: 0 5px; border-radius: 10px; line-height: 16px; min-width: 16px; text-align: center; }
        .nav-badge-warn { background: #9e6a03; }

        /* ── Responsive ────────────────────────────────────────── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-256px); }
            .sidebar.sidebar-open { transform: translateX(0); }
            .sidebar-overlay.overlay-open { display: block; }
            #main-wrapper { margin-left: 0 !important; }
            .hamburger-btn { display: flex; }
            /* Hide breadcrumb section label + separator on mobile */
            .header-title, .header-sep { display: none !important; }
            /* Tables scroll via .gh-table-wrap wrapper (added by JS) */
            /* Collapse multi-column grids to single column */
            .cv-grid { grid-template-columns: 1fr !important; }
        }
        @media (max-width: 480px) {
            main { padding: 12px !important; }
        }
        @media (min-width: 769px) {
            .sidebar { transform: translateX(0) !important; }
            .sidebar-overlay { display: none !important; }
        }
    </style>
</head>
<body>

{{-- Mobile overlay --}}
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

<div style="display:flex; min-height:100vh;">

    {{-- ── Sidebar ──────────────────────────────────────────────── --}}
    <nav class="sidebar" id="sidebar">
        {{-- Logo --}}
        <div style="padding:14px 16px; border-bottom:1px solid #21262d; flex-shrink:0;">
            <a href="{{ route('dashboard') }}" style="display:flex; align-items:center; gap:10px; text-decoration:none;">
                {{-- ConfigVault vault-door logo --}}
                <svg width="34" height="34" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;">
                    <circle cx="17" cy="17" r="15" fill="#0d1117" stroke="#3fb950" stroke-width="1.5"/>
                    <circle cx="17" cy="17" r="10" fill="none" stroke="#238636" stroke-width="1"/>
                    {{-- Locking bolts --}}
                    <rect x="15.25" y="1.5"  width="3.5" height="5"   rx="1.75" fill="#3fb950"/>
                    <rect x="15.25" y="27.5" width="3.5" height="5"   rx="1.75" fill="#3fb950"/>
                    <rect x="1.5"   y="15.25" width="5"  height="3.5" rx="1.75" fill="#3fb950"/>
                    <rect x="27.5"  y="15.25" width="5"  height="3.5" rx="1.75" fill="#3fb950"/>
                    {{-- Handle / dial --}}
                    <circle cx="17" cy="15.5" r="4" fill="none" stroke="#3fb950" stroke-width="1.5"/>
                    {{-- Keyhole --}}
                    <circle cx="17" cy="15"   r="1.75" fill="#3fb950"/>
                    <rect   x="16.25" y="15.75" width="1.5" height="3.5" rx="0.75" fill="#3fb950"/>
                </svg>
                <div>
                    <div style="font-size:14px; font-weight:700; color:#e6edf3; letter-spacing:-.01em;">ConfigVault</div>
                    <div style="font-size:11px; color:#6e7681;">Реестр пакетов</div>
                </div>
            </a>
        </div>

        {{-- Nav items --}}
        <div style="flex:1; padding:8px; overflow-y:auto;">
            @php $user = auth()->user(); @endphp

            @if(!$user->isGhost())
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M6.906.664a1.749 1.749 0 0 1 2.187 0l5.25 4.2c.415.332.657.835.657 1.367v7.019A1.75 1.75 0 0 1 13.25 15h-3.5a.75.75 0 0 1-.75-.75V9H7v5.25a.75.75 0 0 1-.75.75h-3.5A1.75 1.75 0 0 1 1 13.25V6.23c0-.531.242-1.034.657-1.366l5.25-4.2Zm1.25 1.171a.25.25 0 0 0-.312 0l-5.25 4.2a.25.25 0 0 0-.094.196v7.019c0 .138.112.25.25.25H5.5V8.25a.75.75 0 0 1 .75-.75h3.5a.75.75 0 0 1 .75.75v5.25h2.75a.25.25 0 0 0 .25-.25V6.23a.25.25 0 0 0-.094-.196Z"/></svg>
                Обзор
            </a>
            @endif

            <a href="{{ route('packages.index') }}" class="nav-link {{ request()->routeIs('packages.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="m8.878.392 5.25 3.045c.54.314.872.89.872 1.514v6.098a1.75 1.75 0 0 1-.872 1.514l-5.25 3.045a1.75 1.75 0 0 1-1.756 0l-5.25-3.045A1.75 1.75 0 0 1 1 11.049V4.951c0-.624.332-1.2.872-1.514L7.122.392a1.75 1.75 0 0 1 1.756 0ZM7.875 1.69l-4.63 2.685L8 7.133l4.755-2.758-4.63-2.685a.248.248 0 0 0-.25 0ZM2.5 5.677v5.372c0 .096.052.185.132.232l4.868 2.832V8.913Zm6.5 8.436 4.868-2.832a.269.269 0 0 0 .132-.232V5.677L9 8.913Z"/></svg>
                Пакеты
            </a>

            <a href="{{ route('builds.index') }}" class="nav-link {{ request()->routeIs('builds.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0ZM1.5 8a6.5 6.5 0 1 0 13 0 6.5 6.5 0 0 0-13 0Zm3.82 2.03 3-3a.75.75 0 0 1 1.06 0l1.25 1.25a.75.75 0 0 1-1.06 1.06L9 8.81l-2.47 2.47a.75.75 0 0 1-1.21-.28Z"/></svg>
                Сборки
            </a>

            {{-- Admin section --}}
            @if($user->isAdmin())
            @php
                $pendingVerif    = \App\Models\VerificationRequest::whereIn('status', ['pending', 'chatting'])
                    ->when(!$user->isCreator(), fn($q) => $q->where(function($q2) {
                        $q2->where('assigned_to', auth()->id())->orWhereNull('assigned_to');
                    }))->count();
                $pendingReports  = \App\Models\BuildReport::where('status', 'pending')->count()
                    + \App\Models\PackageReport::where('status', 'pending')->count();
                $pendingReviews  = \App\Models\Build::where('status', \App\Enums\BuildStatus::PendingReview)->count();
                $pendingUpgrades = \App\Models\RoleUpgradeRequest::where('status', 'pending')->count();
                $totalBadge      = $pendingVerif + $pendingReports + $pendingReviews + $pendingUpgrades;
            @endphp

            <div class="nav-section" style="margin-top:8px;">Администрирование @if($totalBadge > 0)<span class="nav-badge" style="margin-left:4px;">{{ $totalBadge }}</span>@endif</div>

            <a href="{{ route('admin.verification.index') }}" class="nav-link {{ request()->routeIs('admin.verification.*') ? 'active' : '' }}" style="justify-content:space-between;">
                <span style="display:flex; align-items:center; gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M10.561 8.073a6.005 6.005 0 0 1 3.432 5.142.75.75 0 1 1-1.498.07 4.5 4.5 0 0 0-8.99 0 .75.75 0 0 1-1.498-.07 6.004 6.004 0 0 1 3.431-5.142 3.999 3.999 0 1 1 5.123 0ZM10.5 5a2.5 2.5 0 1 0-5 0 2.5 2.5 0 0 0 5 0Z"/></svg>
                    Верификация
                </span>
                @if($pendingVerif > 0)<span class="nav-badge">{{ $pendingVerif }}</span>@endif
            </a>

            @php
                $pendingBuildReports   = \App\Models\BuildReport::where('status','pending')->count();
                $pendingPackageReports = \App\Models\PackageReport::where('status','pending')->count();
            @endphp
            <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}" style="justify-content:space-between;">
                <span style="display:flex; align-items:center; gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M6.457 1.047c.659-1.234 2.427-1.234 3.086 0l6.082 11.378A1.75 1.75 0 0 1 14.082 15H1.918a1.75 1.75 0 0 1-1.543-2.575Zm1.763.707a.25.25 0 0 0-.44 0L1.698 13.132a.25.25 0 0 0 .22.368h12.164a.25.25 0 0 0 .22-.368ZM9 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM8 5.25a.75.75 0 0 1 .75.75v2.5a.75.75 0 0 1-1.5 0V6A.75.75 0 0 1 8 5.25Z"/></svg>
                    Жалобы на сборки
                </span>
                @if($pendingBuildReports > 0)<span class="nav-badge">{{ $pendingBuildReports }}</span>@endif
            </a>
            <a href="{{ route('admin.package-reports.index') }}" class="nav-link {{ request()->routeIs('admin.package-reports.*') ? 'active' : '' }}" style="justify-content:space-between;">
                <span style="display:flex; align-items:center; gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M6.457 1.047c.659-1.234 2.427-1.234 3.086 0l6.082 11.378A1.75 1.75 0 0 1 14.082 15H1.918a1.75 1.75 0 0 1-1.543-2.575Zm1.763.707a.25.25 0 0 0-.44 0L1.698 13.132a.25.25 0 0 0 .22.368h12.164a.25.25 0 0 0 .22-.368ZM9 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM8 5.25a.75.75 0 0 1 .75.75v2.5a.75.75 0 0 1-1.5 0V6A.75.75 0 0 1 8 5.25Z"/></svg>
                    Жалобы на пакеты
                </span>
                @if($pendingPackageReports > 0)<span class="nav-badge">{{ $pendingPackageReports }}</span>@endif
            </a>

            <a href="{{ route('admin.build-review.index') }}" class="nav-link {{ request()->routeIs('admin.build-review.*') ? 'active' : '' }}" style="justify-content:space-between;">
                <span style="display:flex; align-items:center; gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4.72 1.22a.75.75 0 0 1 1.06 1.06L4.56 3.5h6.88l-1.22-1.22a.75.75 0 1 1 1.06-1.06l2.5 2.5a.75.75 0 0 1 0 1.06l-2.5 2.5a.75.75 0 0 1-1.06-1.06l1.22-1.22H4.56l1.22 1.22a.75.75 0 1 1-1.06 1.06l-2.5-2.5a.75.75 0 0 1 0-1.06Zm-1 7h1a2.5 2.5 0 0 1 2.45 2h4.66a2.5 2.5 0 1 1 0 1.5H7.17A2.5 2.5 0 0 1 3.72 10H2.72a.75.75 0 0 1 0-1.5Zm1 2.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm7 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg>
                    Проверка сборок
                </span>
                @if($pendingReviews > 0)<span class="nav-badge nav-badge-warn" style="background:#9e6a03;">{{ $pendingReviews }}</span>@endif
            </a>

            <a href="{{ route('admin.upgrade-requests.index') }}" class="nav-link {{ request()->routeIs('admin.upgrade-requests.*') ? 'active' : '' }}" style="justify-content:space-between;">
                <span style="display:flex; align-items:center; gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M0 3.75A.75.75 0 0 1 .75 3h14.5a.75.75 0 0 1 0 1.5H.75A.75.75 0 0 1 0 3.75ZM0 8a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H.75A.75.75 0 0 1 0 8Zm.75 3.5a.75.75 0 0 0 0 1.5h7.5a.75.75 0 0 0 0-1.5Z"/></svg>
                    Роли: запросы
                </span>
                @if($pendingUpgrades > 0)<span class="nav-badge" style="background:#1f6feb;">{{ $pendingUpgrades }}</span>@endif
            </a>

            <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M10.561 8.073a6.005 6.005 0 0 1 3.432 5.142.75.75 0 1 1-1.498.07 4.5 4.5 0 0 0-8.99 0 .75.75 0 0 1-1.498-.07 6.004 6.004 0 0 1 3.431-5.142 3.999 3.999 0 1 1 5.123 0ZM10.5 5a2.5 2.5 0 1 0-5 0 2.5 2.5 0 0 0 5 0Z"/></svg>
                Пользователи
            </a>

            <a href="{{ route('admin.channels.index') }}" class="nav-link {{ request()->routeIs('admin.channels.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M1 7.775V2.75C1 1.784 1.784 1 2.75 1h5.025c.464 0 .91.184 1.238.513l6.25 6.25a1.75 1.75 0 0 1 0 2.474l-5.026 5.026a1.75 1.75 0 0 1-2.474 0l-6.25-6.25A1.752 1.752 0 0 1 1 7.775Zm1.5 0c0 .066.026.13.073.177l6.25 6.25a.25.25 0 0 0 .354 0l5.025-5.025a.25.25 0 0 0 0-.354l-6.25-6.25a.25.25 0 0 0-.177-.073H2.75a.25.25 0 0 0-.25.25ZM6 5a1 1 0 1 1 0 2 1 1 0 0 1 0-2Z"/></svg>
                Каналы
            </a>

            <a href="{{ route('admin.audit.index') }}" class="nav-link {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M0 1.75A.75.75 0 0 1 .75 1h4.253c1.227 0 2.317.59 3 1.501A3.743 3.743 0 0 1 11.006 1h4.245a.75.75 0 0 1 .75.75v10.5a.75.75 0 0 1-.75.75h-4.507a2.25 2.25 0 0 0-1.591.659l-.622.621a.75.75 0 0 1-1.06 0l-.622-.621A2.25 2.25 0 0 0 5.258 13H.75a.75.75 0 0 1-.75-.75Zm7.251 10.324.004-5.073-.002-2.253A2.25 2.25 0 0 0 5.003 2.5H1.5v9h3.757a3.75 3.75 0 0 1 1.994.574ZM8.755 4.75l-.004 7.322a3.752 3.752 0 0 1 1.992-.572H14.5v-9h-3.495a2.25 2.25 0 0 0-2.25 2.25Z"/></svg>
                Журнал аудита
            </a>

            <a href="{{ route('admin.queues') }}" class="nav-link {{ request()->routeIs('admin.queues') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M1.5 2.25a.75.75 0 0 1 .75-.75h11.5a.75.75 0 0 1 0 1.5H2.25a.75.75 0 0 1-.75-.75ZM1.5 8a.75.75 0 0 1 .75-.75h11.5a.75.75 0 0 1 0 1.5H2.25A.75.75 0 0 1 1.5 8Zm.75 5.25a.75.75 0 0 0 0 1.5h11.5a.75.75 0 0 0 0-1.5Z"/></svg>
                Очереди задач
            </a>

            {{-- Online widget --}}
            @php
                $onlineAdmins = \App\Models\User::whereIn('role', ['creator', 'admin'])
                    ->whereNotNull('last_seen_at')
                    ->where('last_seen_at', '>=', now()->subMinutes(2))
                    ->get();
                $onlineAll = \App\Models\User::whereNotNull('last_seen_at')
                    ->where('last_seen_at', '>=', now()->subMinutes(2))
                    ->count();
            @endphp
            <div style="margin-top:12px; padding:8px; background:#0d1117; border-radius:6px; border:1px solid #21262d;">
                <div style="font-size:11px; font-weight:600; color:#6e7681; text-transform:uppercase; letter-spacing:.04em; margin-bottom:6px; display:flex; align-items:center; gap:5px;">
                    <span class="online-dot"></span> Онлайн ({{ $onlineAll }})
                </div>
                @foreach($onlineAdmins as $ou)
                <div style="font-size:12px; color:#8b949e; padding:2px 0; display:flex; align-items:center; gap:5px;">
                    <span style="width:6px; height:6px; border-radius:50%; background:#3fb950; display:inline-block; flex-shrink:0;"></span>
                    <span style="color:{{ $ou->isCreator() ? '#bc8cff' : '#58a6ff' }};">{{ $ou->name }}</span>
                    <span style="color:#3d444d; font-size:11px;">{{ $ou->isCreator() ? '· Создатель' : '· Адм' }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- User footer --}}
        <div style="padding:10px 12px; border-top:1px solid #21262d; flex-shrink:0;">
            @php $roleVal = $user->role->value; @endphp
            <div style="display:flex; align-items:center; gap:10px; padding:4px 6px;">
                <div style="width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:12px; font-weight:700;
                    {{ $roleVal==='creator' ? 'background:rgba(188,140,255,.15); color:#bc8cff;' : ($roleVal==='admin' ? 'background:rgba(248,81,73,.12); color:#f85149;' : ($roleVal==='publisher' ? 'background:rgba(88,166,255,.12); color:#58a6ff;' : 'background:#238636; color:#fff;')) }}">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div style="flex:1; min-width:0;">
                    <a href="{{ route('profile.show') }}" style="display:block; font-size:13px; font-weight:600; color:#e6edf3; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; text-decoration:none;">{{ $user->name }}</a>
                    <div style="font-size:11px; color:#6e7681;">
                        {{ match($roleVal) {
                            'creator'   => 'Создатель',
                            'admin'     => 'Администратор',
                            'publisher' => 'Издатель',
                            'ghost'     => 'Призрак',
                            default     => 'Наблюдатель'
                        } }}
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="flex-shrink:0;">
                    @csrf
                    <button type="submit" title="Выйти" style="background:none; border:none; cursor:pointer; color:#6e7681; padding:4px; display:flex; border-radius:4px; transition:color .12s;" onmouseenter="this.style.color='#f85149'" onmouseleave="this.style.color='#6e7681'">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M2 2.75C2 1.784 2.784 1 3.75 1h2.5a.75.75 0 0 1 0 1.5h-2.5a.25.25 0 0 0-.25.25v10.5c0 .138.112.25.25.25h2.5a.75.75 0 0 1 0 1.5h-2.5A1.75 1.75 0 0 1 2 13.25Zm10.44 4.5-1.97-1.97a.749.749 0 0 1 .326-1.275.749.749 0 0 1 .734.215l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.749.749 0 0 1-1.275-.326.749.749 0 0 1 .215-.734l1.97-1.97H6.75a.75.75 0 0 1 0-1.5Z"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- ── Main content ─────────────────────────────────────────── --}}
    <div id="main-wrapper" style="flex:1; margin-left:256px; display:flex; flex-direction:column; min-height:100vh; min-width:0;">

        {{-- Header --}}
        <header style="background:#161b22; border-bottom:1px solid #21262d; padding:0 20px; height:48px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:20; gap:12px;">
            <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                {{-- Hamburger (mobile only) --}}
                <button class="hamburger-btn" onclick="toggleSidebar()" aria-label="Меню">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M1 2.75A.75.75 0 0 1 1.75 2h12.5a.75.75 0 0 1 0 1.5H1.75A.75.75 0 0 1 1 2.75Zm0 5A.75.75 0 0 1 1.75 7h12.5a.75.75 0 0 1 0 1.5H1.75A.75.75 0 0 1 1 7.75Zm.75 4.25a.75.75 0 0 0 0 1.5h12.5a.75.75 0 0 0 0-1.5Z"/></svg>
                </button>
                {{-- Breadcrumb --}}
                <div style="display:flex; align-items:center; gap:6px; font-size:13px; color:#8b949e; min-width:0; overflow:hidden;">
                    <span class="header-title" style="white-space:nowrap;">@yield('breadcrumb', config('app.name'))</span>
                    @hasSection('breadcrumb_current')
                    <svg class="header-sep" width="14" height="14" viewBox="0 0 16 16" fill="currentColor" style="color:#6e7681; flex-shrink:0;"><path d="M6.22 3.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.751.751 0 0 1-1.042-.018.751.751 0 0 1-.018-1.042L10.19 8 6.22 4.03a.75.75 0 0 1 0-1.06Z"/></svg>
                    <span style="color:#e6edf3; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">@yield('breadcrumb_current')</span>
                    @endif
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                @yield('header_actions')
            </div>
        </header>

        {{-- Page content --}}
        <main style="flex:1; padding:20px;">

            {{-- Ghost banner --}}
            @if($user->isGhost())
            @php $ghostVr = $user->verificationRequest; @endphp
            <div class="flash-warning">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="flex-shrink:0;"><path d="M6.457 1.047c.659-1.234 2.427-1.234 3.086 0l6.082 11.378A1.75 1.75 0 0 1 14.082 15H1.918a1.75 1.75 0 0 1-1.543-2.575Zm1.763.707a.25.25 0 0 0-.44 0L1.698 13.132a.25.25 0 0 0 .22.368h12.164a.25.25 0 0 0 .22-.368Zm.53 3.996v2.5a.75.75 0 0 1-1.5 0v-2.5a.75.75 0 0 1 1.5 0ZM9 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z"/></svg>
                <span>Ваш аккаунт ожидает верификации. Скачивание будет доступно после подтверждения.</span>
                @if($ghostVr && $ghostVr->status === 'chatting')
                    <a href="{{ route('ghost.chat') }}" style="color:#d29922; font-weight:600; text-decoration:underline;">Администратор хочет поговорить →</a>
                @else
                    <a href="{{ route('ghost.pending') }}" style="color:#d29922; text-decoration:underline;">Статус верификации</a>
                @endif
            </div>
            @endif

            {{-- Flash messages --}}
            @if(session('success'))
            <div class="flash-success">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="flex-shrink:0;"><path d="M8 16A8 8 0 1 1 8 0a8 8 0 0 1 0 16Zm3.78-9.72a.751.751 0 0 0-.018-1.042.751.751 0 0 0-1.042-.018L6.75 9.19 5.28 7.72a.751.751 0 0 0-1.042.018.751.751 0 0 0-.018 1.042l2 2a.75.75 0 0 0 1.06 0Z"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="flash-error">{{ session('error') }}</div>
            @endif
            @if($errors->any())
            <div class="flash-error">
                <strong>Исправьте следующие ошибки:</strong>
                <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('sidebar-open');
    document.getElementById('sidebar-overlay').classList.toggle('overlay-open');
    document.body.style.overflow = document.getElementById('sidebar').classList.contains('sidebar-open') ? 'hidden' : '';
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('sidebar-open');
    document.getElementById('sidebar-overlay').classList.remove('overlay-open');
    document.body.style.overflow = '';
}
// Wrap all .gh-table in overflow containers (scroll on small screens, stretch on large)
document.querySelectorAll('.gh-table').forEach(function(t) {
    var w = document.createElement('div');
    w.className = 'gh-table-wrap';
    t.parentNode.insertBefore(w, t);
    w.appendChild(t);
});
// Close sidebar on resize to desktop
window.addEventListener('resize', function() {
    if (window.innerWidth >= 769) closeSidebar();
});
</script>
</body>
</html>
