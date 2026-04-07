<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аккаунт заблокирован — ConfigVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { box-sizing: border-box; }
        body { background: #0d1117; color: #e6edf3; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Helvetica,Arial,sans-serif; font-size: 14px; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 24px; }
    </style>
</head>
<body>
<div style="max-width:480px; width:100%; text-align:center;">

    <!-- Logo -->
    <a href="{{ route('dashboard') }}" style="display:inline-flex; align-items:center; gap:10px; text-decoration:none; margin-bottom:32px;">
        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="20" cy="20" r="18" fill="#161b22" stroke="#da3633" stroke-width="1.5"/>
            <circle cx="20" cy="20" r="12" fill="none" stroke="#b91c1c" stroke-width="1"/>
            <rect x="18" y="3" width="4" height="5" rx="2" fill="#da3633"/>
            <rect x="18" y="32" width="4" height="5" rx="2" fill="#da3633"/>
            <rect x="3" y="18" width="5" height="4" rx="2" fill="#da3633"/>
            <rect x="32" y="18" width="5" height="4" rx="2" fill="#da3633"/>
            <circle cx="20" cy="19" r="4" fill="none" stroke="#da3633" stroke-width="1.5"/>
            <circle cx="20" cy="18.5" r="2" fill="#da3633"/>
            <rect x="19" y="19.5" width="2" height="4" rx="1" fill="#da3633"/>
        </svg>
        <div style="text-align:left;">
            <div style="font-size:18px; font-weight:700; color:#e6edf3;">ConfigVault</div>
            <div style="font-size:12px; color:#6e7681;">Реестр пакетов</div>
        </div>
    </a>

    <!-- Ban card -->
    <div style="background:#161b22; border:1px solid #da3633; border-radius:12px; padding:40px 32px;">
        <div style="width:64px; height:64px; border-radius:50%; background:rgba(218,54,51,.15); display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#da3633" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
            </svg>
        </div>

        <h1 style="font-size:22px; font-weight:700; color:#f85149; margin:0 0 8px;">Аккаунт заблокирован</h1>
        <p style="color:#8b949e; font-size:14px; margin:0 0 24px; line-height:1.6;">
            Ваш аккаунт был заблокирован за нарушение правил платформы.
        </p>

        @if(auth()->user()->isBanned())
        <div style="background:#0d1117; border:1px solid #30363d; border-radius:8px; padding:16px; margin-bottom:24px;">
            @php $until = auth()->user()->banned_until; @endphp
            @if($until->year >= now()->year + 50)
            <div style="font-size:13px; color:#8b949e; margin-bottom:4px;">Срок блокировки</div>
            <div style="font-size:20px; font-weight:700; color:#f85149;">Перманентная</div>
            @else
            <div style="font-size:13px; color:#8b949e; margin-bottom:4px;">Заблокирован до</div>
            <div style="font-size:20px; font-weight:700; color:#e6edf3;">{{ $until->format('d.m.Y') }}</div>
            <div style="font-size:12px; color:#6e7681; margin-top:4px;">
                Осталось: {{ now()->diffForHumans($until, ['parts' => 2, 'short' => false]) }}
            </div>
            @endif
        </div>

        <div style="font-size:12px; color:#6e7681; margin-bottom:24px;">
            Блокировка № {{ auth()->user()->ban_count }}
            @if(auth()->user()->ban_count === 1) · Следующая блокировка: 90 дней
            @elseif(auth()->user()->ban_count === 2) · Следующая блокировка: перманентная
            @endif
        </div>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="display:inline-flex; align-items:center; gap:8px; padding:8px 20px; background:#21262d; border:1px solid #30363d; color:#c9d1d9; border-radius:6px; cursor:pointer; font-size:14px; text-decoration:none; transition:background .12s;" onmouseenter="this.style.background='#30363d'" onmouseleave="this.style.background='#21262d'">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M2 2.75C2 1.784 2.784 1 3.75 1h2.5a.75.75 0 0 1 0 1.5h-2.5a.25.25 0 0 0-.25.25v10.5c0 .138.112.25.25.25h2.5a.75.75 0 0 1 0 1.5h-2.5A1.75 1.75 0 0 1 2 13.25Zm10.44 4.5-1.97-1.97a.749.749 0 0 1 .326-1.275.749.749 0 0 1 .734.215l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.749.749 0 0 1-1.275-.326.749.749 0 0 1 .215-.734l1.97-1.97H6.75a.75.75 0 0 1 0-1.5Z"/></svg>
                Выйти из аккаунта
            </button>
        </form>
    </div>

    <p style="margin-top:20px; font-size:12px; color:#6e7681;">
        Если вы считаете, что блокировка ошибочна, обратитесь к администратору.
    </p>
</div>
</body>
</html>
