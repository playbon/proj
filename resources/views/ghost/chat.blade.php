@extends('layouts.app')
@section('title', 'Верификация — чат')
@section('breadcrumb', 'ConfigVault')
@section('breadcrumb_current', 'Чат верификации')

@section('content')
<div style="max-width:720px; display:flex; flex-direction:column; gap:16px;">

    <div class="box">
        <div class="box-header">
            <div>
                <h3>Проверка аккаунта</h3>
                <div style="font-size:12px; color:#6e7681; margin-top:2px;">Ответьте на вопросы администратора для завершения регистрации</div>
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
                    {{ $mine ? 'Вы' : $msg->sender->name }} · {{ $msg->created_at->format('H:i') }}
                </div>
                <div style="max-width:80%; padding:8px 12px; border-radius:8px; font-size:14px; line-height:1.5;
                    {{ $mine ? 'background:#1f6feb; color:#fff; border-bottom-right-radius:2px;' : 'background:#21262d; color:#e6edf3; border-bottom-left-radius:2px;' }}">
                    {{ $msg->body }}
                </div>
            </div>
            @empty
            <p style="color:#6e7681; font-size:13px; text-align:center; margin:auto;">Администратор откроет беседу. Ожидайте первого сообщения.</p>
            @endforelse
        </div>

        <div style="border-top:1px solid #21262d; padding:16px;">
            <form method="POST" action="{{ route('ghost.chat.send') }}" style="display:flex; gap:8px;">
                @csrf
                <input type="text" name="body" placeholder="Ваш ответ…" required autofocus
                    class="form-input" style="flex:1;">
                <button type="submit" class="btn btn-primary">Отправить</button>
            </form>
        </div>
    </div>
</div>

<script>
    const el = document.getElementById('messages');
    if (el) el.scrollTop = el.scrollHeight;
</script>

<meta http-equiv="refresh" content="5">
@endsection
