@extends('layouts.app')
@section('title', 'Ожидание верификации')
@section('breadcrumb', 'ConfigVault')
@section('breadcrumb_current', 'Верификация')

@section('content')
<div style="max-width:560px; display:flex; flex-direction:column; gap:16px;">
    <div class="box">
        <div class="box-header">
            <h3>Ожидание проверки аккаунта</h3>
            @if($vr && $vr->status === 'chatting')
                <span class="label label-info">Администратор готов</span>
            @else
                <span class="label label-warning">Ожидает</span>
            @endif
        </div>
        <div class="box-body" style="display:flex; flex-direction:column; gap:16px;">
            <p style="color:#8b949e; font-size:14px; line-height:1.6;">
                Ваш аккаунт зарегистрирован и ожидает проверки администратором.
                До завершения проверки вы можете <strong style="color:#e6edf3;">просматривать</strong> пакеты,
                но не можете скачивать, создавать или изменять контент.
            </p>

            @if($vr && $vr->status === 'chatting')
            <div style="padding:16px; background:rgba(31,111,235,.1); border:1px solid rgba(88,166,255,.3); border-radius:6px;">
                <div style="font-size:14px; font-weight:600; color:#58a6ff; margin-bottom:6px;">
                    Администратор хочет с вами поговорить
                </div>
                <p style="font-size:13px; color:#8b949e; margin-bottom:12px;">
                    Администратор открыл чат для верификации вашего аккаунта. Перейдите в чат и ответьте на вопросы.
                </p>
                <a href="{{ route('ghost.chat') }}" class="btn btn-primary">Открыть чат</a>
            </div>
            @elseif($vr)
            <div style="padding:16px; background:#161b22; border:1px solid #30363d; border-radius:6px;">
                <div style="font-size:13px; color:#8b949e;">
                    Запрос создан {{ $vr->created_at->format('d.m.Y в H:i') }}.
                    @if($vr->assignee)
                        Назначен администратору <strong style="color:#e6edf3;">{{ $vr->assignee->name }}</strong>.
                    @else
                        Ожидает назначения администратора (очередь создателя).
                    @endif
                </div>
            </div>
            @endif

            <a href="{{ route('packages.index') }}" class="btn btn-secondary" style="align-self:flex-start;">
                Перейти к пакетам
            </a>
        </div>
    </div>
</div>

{{-- Refresh only when chat opens (status=chatting) to notify user --}}
@if($vr && $vr->status === 'chatting')
<meta http-equiv="refresh" content="5">
@endif
@endsection
