@extends('layouts.app')
@section('title', 'Верификация')
@section('breadcrumb', 'Администрирование')
@section('breadcrumb_current', 'Верификация пользователей')

@section('content')
<div class="box">
    <div class="box-header">
        <h3>Запросы на верификацию</h3>
        <span class="label label-default">{{ $requests->count() }} активных</span>
    </div>

    @if($requests->isEmpty())
    <div style="padding:48px 16px; text-align:center; color:#8b949e;">
        <svg width="32" height="32" viewBox="0 0 16 16" fill="currentColor" style="margin-bottom:12px; opacity:.5;"><path d="M8 16A8 8 0 1 1 8 0a8 8 0 0 1 0 16Zm3.78-9.72a.751.751 0 0 0-.018-1.042.751.751 0 0 0-1.042-.018L6.75 9.19 5.28 7.72a.751.751 0 0 0-1.042.018.751.751 0 0 0-.018 1.042l2 2a.75.75 0 0 0 1.06 0Z"/></svg>
        <p>Нет активных запросов на верификацию.</p>
    </div>
    @else
    <table class="gh-table">
        <thead>
            <tr>
                <th>Пользователь</th>
                <th>Email</th>
                <th>Зарегистрирован</th>
                <th>Статус</th>
                <th>Назначен</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($requests as $req)
            <tr>
                <td>
                    <div style="font-size:14px; font-weight:600; color:#e6edf3;">{{ $req->user->name }}</div>
                </td>
                <td style="font-size:13px; color:#8b949e;">{{ $req->user->email }}</td>
                <td style="font-size:12px; color:#8b949e;">{{ $req->user->created_at->format('d.m.Y H:i') }}</td>
                <td>
                    @if($req->status === 'pending')
                        <span class="label label-warning">Ожидает</span>
                    @elseif($req->status === 'chatting')
                        <span class="label label-info">Идёт проверка</span>
                    @endif
                </td>
                <td style="font-size:12px; color:#8b949e;">
                    {{ $req->assignee?->name ?? '— очередь создателя —' }}
                </td>
                <td style="text-align:right;">
                    <div style="display:flex; gap:6px; justify-content:flex-end;">
                        @if($req->status === 'chatting')
                            <a href="{{ route('admin.verification.chat', $req) }}" class="btn btn-secondary btn-sm">Открыть чат</a>
                        @else
                            <form method="POST" action="{{ route('admin.verification.approve', $req) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Принять</button>
                            </form>
                            <form method="POST" action="{{ route('admin.verification.reject', $req) }}">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">Отклонить</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
