@extends('layouts.app')
@section('title', 'Верификация — чат')
@section('breadcrumb', 'Администрирование')
@section('breadcrumb_current', 'Проверка пользователя')

@section('header_actions')
<form method="POST" action="{{ route('admin.verification.confirm', $vr) }}" onsubmit="return confirm('Подтвердить пользователя «{{ $vr->user->name }}»? Он получит роль Наблюдателя.')">
    @csrf
    <button type="submit" class="btn btn-primary btn-sm">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M8 16A8 8 0 1 1 8 0a8 8 0 0 1 0 16Zm3.78-9.72a.751.751 0 0 0-.018-1.042.751.751 0 0 0-1.042-.018L6.75 9.19 5.28 7.72a.751.751 0 0 0-1.042.018.751.751 0 0 0-.018 1.042l2 2a.75.75 0 0 0 1.06 0Z"/></svg>
        Подтвердить пользователя
    </button>
</form>
<form method="POST" action="{{ route('admin.verification.reject', $vr) }}" onsubmit="return confirm('Отклонить и передать другому администратору?')">
    @csrf
    <button type="submit" class="btn btn-danger btn-sm">Отклонить</button>
</form>
@endsection

@section('content')
<div style="max-width:720px; display:flex; flex-direction:column; gap:16px;">

    <div class="box">
        <div class="box-header">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:36px; height:36px; border-radius:50%; background:#21262d; color:#8b949e; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700;">
                    {{ strtoupper(substr($vr->user->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:14px; font-weight:600; color:#e6edf3;">{{ $vr->user->name }}</div>
                    <div style="font-size:12px; color:#6e7681;">{{ $vr->user->email }} · зарег. {{ $vr->user->created_at->format('d.m.Y') }}</div>
                </div>
            </div>
            <span class="label label-info">Идёт проверка</span>
        </div>
    </div>

    <div class="box" style="display:flex; flex-direction:column;">
        <div class="box-header"><h3>Переписка</h3></div>
        <div id="messages" style="min-height:300px; max-height:500px; overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:12px;">
            @forelse($vr->messages as $msg)
            @php $mine = $msg->user_id === auth()->id(); @endphp
            <div style="display:flex; flex-direction:column; align-items:{{ $mine ? 'flex-end' : 'flex-start' }};">
                <div style="font-size:11px; color:#6e7681; margin-bottom:3px;">
                    {{ $msg->sender->name }} · {{ $msg->created_at->format('H:i') }}
                </div>
                <div style="max-width:80%; padding:8px 12px; border-radius:8px; font-size:14px; line-height:1.5;
                    {{ $mine ? 'background:#1f6feb; color:#fff; border-bottom-right-radius:2px;' : 'background:#21262d; color:#e6edf3; border-bottom-left-radius:2px;' }}">
                    {{ $msg->body }}
                </div>
            </div>
            @empty
            <p style="color:#6e7681; font-size:13px; text-align:center; margin:auto;">Сообщений пока нет. Начните общение.</p>
            @endforelse
        </div>

        <div style="border-top:1px solid #21262d; padding:16px;">
            <form method="POST" action="{{ route('admin.verification.message', $vr) }}" style="display:flex; gap:8px;">
                @csrf
                <input type="text" name="body" placeholder="Введите сообщение…" required autofocus
                    class="form-input" style="flex:1;">
                <button type="submit" class="btn btn-primary">Отправить</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Scroll to bottom of messages on load
    const el = document.getElementById('messages');
    if (el) el.scrollTop = el.scrollHeight;
</script>

{{-- Auto-refresh every 5 seconds to fetch new messages --}}
<meta http-equiv="refresh" content="5">
@endsection
