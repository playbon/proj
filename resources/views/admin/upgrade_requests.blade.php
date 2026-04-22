@extends('layouts.app')
@section('title', 'Запросы на повышение роли')
@section('breadcrumb', 'Администрирование')
@section('breadcrumb_current', 'Повышение роли')

@section('content')
<div class="box">
    <div class="box-header">
        <h3>Запросы на повышение до издателя</h3>
        @if($requests->total() > 0)
        <span class="label label-info">{{ $requests->total() }} ожидает</span>
        @else
        <span class="label label-success">Нет запросов</span>
        @endif
    </div>

    @if($requests->isEmpty())
    <div style="padding:48px; text-align:center; color:#6e7681;">
        <svg width="32" height="32" viewBox="0 0 16 16" fill="currentColor" style="margin-bottom:12px; opacity:.4;"><path d="M10.561 8.073a6.005 6.005 0 0 1 3.432 5.142.75.75 0 1 1-1.498.07 4.5 4.5 0 0 0-8.99 0 .75.75 0 0 1-1.498-.07 6.004 6.004 0 0 1 3.431-5.142 3.999 3.999 0 1 1 5.123 0ZM10.5 5a2.5 2.5 0 1 0-5 0 2.5 2.5 0 0 0 5 0Z"/></svg>
        <div style="font-size:14px; font-weight:600; color:#8b949e;">Нет активных запросов</div>
        <div style="font-size:12px; margin-top:4px;">Наблюдатели ещё не подавали запросы на повышение.</div>
    </div>
    @else
    <table class="gh-table">
        <thead>
            <tr>
                <th>Пользователь</th>
                <th>Email</th>
                <th>Зарегистрирован</th>
                <th>Причина</th>
                <th>Дата запроса</th>
                <th style="text-align:right;">Действия</th>
            </tr>
        </thead>
        <tbody>
        @foreach($requests as $req)
        <tr>
            <td>
                <div style="font-size:13px; font-weight:600; color:#e6edf3;">{{ $req->user->name }}</div>
                <span class="label label-default" style="margin-top:4px; display:inline-block;">Наблюдатель</span>
            </td>
            <td>
                <span style="font-size:13px; color:#8b949e;">{{ $req->user->email }}</span>
            </td>
            <td>
                <span style="font-size:12px; color:#6e7681;">{{ $req->user->created_at->format('d.m.Y') }}</span>
            </td>
            <td style="max-width:320px;">
                <div style="font-size:13px; color:#8b949e; line-height:1.5;">
                    {{ Str::limit($req->reason, 120) }}
                </div>
                @if(strlen($req->reason) > 120)
                <details style="margin-top:4px;">
                    <summary style="font-size:12px; color:#58a6ff; cursor:pointer;">Читать полностью</summary>
                    <div style="font-size:13px; color:#8b949e; margin-top:6px; line-height:1.5;">{{ $req->reason }}</div>
                </details>
                @endif
            </td>
            <td>
                <span style="font-size:12px; color:#6e7681;">{{ $req->created_at->format('d.m.Y H:i') }}</span>
            </td>
            <td style="text-align:right; white-space:nowrap;">
                <form method="POST" action="{{ route('admin.upgrade-requests.approve', $req) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">Одобрить</button>
                </form>
                <form method="POST" action="{{ route('admin.upgrade-requests.reject', $req) }}" style="display:inline; margin-left:4px;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">Отклонить</button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>

    @if($requests->hasPages())
    <div style="padding:16px; border-top:1px solid #21262d;">
        {{ $requests->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
