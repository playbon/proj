@extends('layouts.app')
@section('title', 'Профиль')
@section('breadcrumb', 'ConfigVault')
@section('breadcrumb_current', 'Профиль')

@section('content')
@php
    $roleVal  = $user->role->value;
    $roleColor = match($roleVal) {
        'creator'   => ['bg'=>'rgba(188,140,255,.12)','border'=>'rgba(188,140,255,.4)','fg'=>'#bc8cff','label'=>'Создатель'],
        'admin'     => ['bg'=>'rgba(248,81,73,.10)','border'=>'rgba(248,81,73,.4)','fg'=>'#f85149','label'=>'Администратор'],
        'publisher' => ['bg'=>'rgba(88,166,255,.10)','border'=>'rgba(88,166,255,.4)','fg'=>'#58a6ff','label'=>'Издатель'],
        'ghost'     => ['bg'=>'rgba(110,118,129,.12)','border'=>'rgba(110,118,129,.4)','fg'=>'#8b949e','label'=>'Призрак'],
        default     => ['bg'=>'rgba(35,134,54,.10)','border'=>'rgba(63,185,80,.4)','fg'=>'#3fb950','label'=>'Наблюдатель'],
    };
    $pkgCount   = $user->packages()->count();
    $buildCount = $user->builds()->count();
    $hasPendingUpgrade = \App\Models\RoleUpgradeRequest::where('user_id', $user->id)->where('status','pending')->exists();
@endphp

<div style="max-width:720px; display:flex; flex-direction:column; gap:20px;">

    {{-- ── Profile header card ────────────────────────────────── --}}
    <div style="background:#161b22; border:1px solid #30363d; border-radius:10px; overflow:hidden;">
        {{-- Cover strip --}}
        <div style="height:6px; background:linear-gradient(90deg, {{ $roleColor['fg'] }}44 0%, {{ $roleColor['fg'] }}22 100%);"></div>

        <div style="padding:24px; display:flex; align-items:flex-start; gap:20px; flex-wrap:wrap;">
            {{-- Avatar --}}
            <div style="width:72px; height:72px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:700; flex-shrink:0;
                background:{{ $roleColor['bg'] }}; border:2px solid {{ $roleColor['border'] }}; color:{{ $roleColor['fg'] }};">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>

            <div style="flex:1; min-width:0;">
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:6px;">
                    <h2 style="font-size:20px; font-weight:700; color:#e6edf3; margin:0;">{{ $user->name }}</h2>
                    <span style="padding:2px 10px; font-size:12px; font-weight:600; border-radius:2em; border:1px solid {{ $roleColor['border'] }}; color:{{ $roleColor['fg'] }}; background:{{ $roleColor['bg'] }};">
                        {{ $roleColor['label'] }}
                    </span>
                    @if($user->isOnline())
                    <span style="display:inline-flex; align-items:center; gap:4px; font-size:12px; color:#3fb950;">
                        <span style="width:7px; height:7px; border-radius:50%; background:#3fb950; display:inline-block;"></span>
                        В сети
                    </span>
                    @endif
                </div>
                <div style="font-size:13px; color:#8b949e;">{{ $user->email }}</div>
                <div style="font-size:12px; color:#6e7681; margin-top:4px;">
                    Зарегистрирован {{ $user->created_at->format('d.m.Y') }} · {{ $user->created_at->diffForHumans() }}
                </div>
            </div>
        </div>

        {{-- Stats row --}}
        <div class="cv-grid" style="display:grid; grid-template-columns:repeat(3, 1fr); border-top:1px solid #21262d;">
            <div style="padding:16px; text-align:center; border-right:1px solid #21262d;">
                <div style="font-size:22px; font-weight:700; color:#e6edf3;">{{ $pkgCount }}</div>
                <div style="font-size:12px; color:#6e7681; margin-top:2px;">Пакетов</div>
            </div>
            <div style="padding:16px; text-align:center; border-right:1px solid #21262d;">
                <div style="font-size:22px; font-weight:700; color:#e6edf3;">{{ $buildCount }}</div>
                <div style="font-size:12px; color:#6e7681; margin-top:2px;">Сборок</div>
            </div>
            <div style="padding:16px; text-align:center;">
                <div style="font-size:22px; font-weight:700; color:{{ $user->ban_count > 0 ? '#f85149' : '#e6edf3' }};">{{ $user->ban_count }}</div>
                <div style="font-size:12px; color:#6e7681; margin-top:2px;">Блокировок</div>
            </div>
        </div>
    </div>

    {{-- ── Role upgrade request (viewers only) ────────────────── --}}
    @if($roleVal === 'viewer')
    <div style="background:#161b22; border:1px solid #30363d; border-radius:10px; overflow:hidden;">
        <div style="padding:16px 20px; border-bottom:1px solid #21262d; display:flex; align-items:center; gap:10px;">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="#58a6ff"><path d="M0 3.75A.75.75 0 0 1 .75 3h14.5a.75.75 0 0 1 0 1.5H.75A.75.75 0 0 1 0 3.75Zm0 4A.75.75 0 0 1 .75 7h14.5a.75.75 0 0 1 0 1.5H.75A.75.75 0 0 1 0 7.75Zm0 4a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5H.75a.75.75 0 0 1-.75-.75Z"/></svg>
            <h3 style="font-size:14px; font-weight:600; color:#e6edf3; margin:0;">Запросить роль издателя</h3>
        </div>
        <div style="padding:20px;">
            @if($hasPendingUpgrade)
            <div style="padding:12px 16px; background:rgba(88,166,255,.08); border:1px solid rgba(88,166,255,.3); border-radius:6px; color:#58a6ff; font-size:13px; display:flex; align-items:center; gap:8px;">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M8 16A8 8 0 1 1 8 0a8 8 0 0 1 0 16Zm-.75-4.75v-5.5a.75.75 0 0 1 1.5 0v5.5a.75.75 0 0 1-1.5 0Zm.75-8a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z"/></svg>
                Ваш запрос отправлен и ожидает рассмотрения администратором.
            </div>
            @else
            <p style="font-size:13px; color:#8b949e; margin:0 0 16px; line-height:1.6;">
                Как наблюдатель, вы можете просматривать и скачивать пакеты, но не публиковать. Чтобы создавать пакеты и сборки — запросите роль издателя.
            </p>
            <form method="POST" action="{{ route('upgrade-request.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="reason">Почему вы хотите стать издателем?</label>
                    <textarea id="reason" name="reason" rows="4" class="form-textarea" required
                        placeholder="Расскажите, что планируете публиковать и для каких целей...">{{ old('reason') }}</textarea>
                    <p class="form-hint">Кратко опишите свои намерения. Администратор рассмотрит запрос.</p>
                </div>
                <button type="submit" class="btn btn-primary">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M.989 8 .064 2.68a1.342 1.342 0 0 1 1.85-1.462l13.402 5.744a1.13 1.13 0 0 1 0 2.076L1.913 14.782a1.343 1.343 0 0 1-1.85-1.463L.99 8Zm.603-5.288L2.38 7.25h4.87a.75.75 0 0 1 0 1.5H2.38l-.788 4.538L13.929 8Z"/></svg>
                    Отправить запрос
                </button>
            </form>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Edit profile ─────────────────────────────────────────── --}}
    <div style="background:#161b22; border:1px solid #30363d; border-radius:10px; overflow:hidden;">
        <div style="padding:16px 20px; border-bottom:1px solid #21262d;">
            <h3 style="font-size:14px; font-weight:600; color:#e6edf3; margin:0;">Основная информация</h3>
        </div>
        <div style="padding:20px;">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PUT')
                <div class="form-group">
                    <label class="form-label" for="name">Имя пользователя</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="form-input" style="max-width:360px;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input" style="max-width:360px;">
                </div>
                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
            </form>
        </div>
    </div>

    {{-- ── Change password ──────────────────────────────────────── --}}
    <div style="background:#161b22; border:1px solid #30363d; border-radius:10px; overflow:hidden;">
        <div style="padding:16px 20px; border-bottom:1px solid #21262d;">
            <h3 style="font-size:14px; font-weight:600; color:#e6edf3; margin:0;">Изменить пароль</h3>
        </div>
        <div style="padding:20px;">
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf @method('PUT')
                <div class="form-group">
                    <label class="form-label" for="current_password">Текущий пароль</label>
                    <input type="password" id="current_password" name="current_password" required class="form-input" style="max-width:360px;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Новый пароль</label>
                    <input type="password" id="password" name="password" required class="form-input" style="max-width:360px;">
                    <p class="form-hint">Минимум 8 символов.</p>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Подтвердите пароль</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required class="form-input" style="max-width:360px;">
                </div>
                <button type="submit" class="btn btn-primary">Изменить пароль</button>
            </form>
        </div>
    </div>

    {{-- ── Ban info (if applicable) ─────────────────────────────── --}}
    @if($user->ban_count > 0)
    <div style="background:#161b22; border:1px solid #30363d; border-radius:10px; overflow:hidden;">
        <div style="padding:16px 20px; border-bottom:1px solid #21262d; display:flex; align-items:center; gap:8px;">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="#d29922"><path d="M6.457 1.047c.659-1.234 2.427-1.234 3.086 0l6.082 11.378A1.75 1.75 0 0 1 14.082 15H1.918a1.75 1.75 0 0 1-1.543-2.575Zm1.763.707a.25.25 0 0 0-.44 0L1.698 13.132a.25.25 0 0 0 .22.368h12.164a.25.25 0 0 0 .22-.368ZM9 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM8 5.25a.75.75 0 0 1 .75.75v2.5a.75.75 0 0 1-1.5 0V6A.75.75 0 0 1 8 5.25Z"/></svg>
            <h3 style="font-size:14px; font-weight:600; color:#d29922; margin:0;">История блокировок</h3>
        </div>
        <div style="padding:20px;">
            <div style="display:flex; flex-direction:column; gap:10px;">
                @for($i = 1; $i <= $user->ban_count; $i++)
                <div style="display:flex; align-items:center; gap:12px; padding:10px 14px; background:#0d1117; border:1px solid #21262d; border-radius:6px;">
                    <span style="width:24px; height:24px; border-radius:50%; background:rgba(248,81,73,.15); color:#f85149; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0;">{{ $i }}</span>
                    <span style="font-size:13px; color:#8b949e;">
                        @if($i === 1) 30 дней
                        @elseif($i === 2) 90 дней
                        @else Перманентная
                        @endif
                    </span>
                </div>
                @endfor
            </div>
            @if($user->isBanned())
            <div style="margin-top:12px; padding:10px 14px; background:rgba(218,54,51,.08); border:1px solid rgba(248,81,73,.3); border-radius:6px; font-size:13px; color:#f85149;">
                Текущая блокировка активна до {{ $user->banned_until->format('d.m.Y') }}
            </div>
            @endif
        </div>
    </div>
    @endif

</div>
@endsection
