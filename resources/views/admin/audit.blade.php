@extends('layouts.app')
@section('title', 'Журнал аудита')
@section('breadcrumb', 'Администрирование')
@section('breadcrumb_current', 'Журнал аудита')

@section('header_actions')
<a href="{{ route('admin.audit.export', request()->all()) }}" class="btn btn-secondary">
    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M2.75 14A1.75 1.75 0 0 1 1 12.25v-2.5a.75.75 0 0 1 1.5 0v2.5c0 .138.112.25.25.25h10.5a.25.25 0 0 0 .25-.25v-2.5a.75.75 0 0 1 1.5 0v2.5A1.75 1.75 0 0 1 13.25 14Zm-1-5.689 3.22 3.22a.749.749 0 0 0 1.06 0l3.22-3.22a.749.749 0 1 0-1.06-1.06L8.75 8.44V1.75a.75.75 0 0 0-1.5 0V8.44L5.81 7.251a.749.749 0 1 0-1.06 1.06Z"/></svg>
    Экспорт CSV
</a>
@endsection

@section('content')
<div class="box" style="margin-bottom:16px;">
    <div class="box-body" style="padding:16px;">
        <form method="GET" style="display:flex; flex-wrap:wrap; gap:8px; align-items:flex-end;">
            <div>
                <label class="form-label" style="font-size:12px; display:block; margin-bottom:4px;">Пользователь</label>
                <select name="user_id" class="form-select" style="width:160px;">
                    <option value="">Все пользователи</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" style="font-size:12px; display:block; margin-bottom:4px;">Действие</label>
                <select name="action" class="form-select" style="width:180px;">
                    <option value="">Все действия</option>
                    @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" style="font-size:12px; display:block; margin-bottom:4px;">С</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-input" style="width:140px;">
            </div>
            <div>
                <label class="form-label" style="font-size:12px; display:block; margin-bottom:4px;">По</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input" style="width:140px;">
            </div>
            <button type="submit" class="btn btn-secondary">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M10.68 11.74a6 6 0 0 1-7.922-8.982 6 6 0 0 1 8.982 7.922l3.04 3.04a.749.749 0 0 1-.326 1.275.749.749 0 0 1-.734-.215ZM11.5 7a4.499 4.499 0 1 0-8.997 0A4.499 4.499 0 0 0 11.5 7Z"/></svg>
                Найти
            </button>
            @if(request()->hasAny(['user_id','action','from','to']))
            <a href="{{ route('admin.audit.index') }}" class="btn btn-secondary">Сбросить</a>
            @endif
        </form>
    </div>
</div>

<div class="box">
    <table class="gh-table">
        <thead>
            <tr>
                <th>Пользователь</th>
                <th>Действие</th>
                <th>Объект</th>
                <th>IP-адрес</th>
                <th>Время</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:24px; height:24px; border-radius:50%; background:#21262d; border:1px solid #30363d; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:11px; font-weight:700; color:#8b949e;">
                            {{ strtoupper(substr($log->user->name ?? 'S', 0, 1)) }}
                        </div>
                        <span style="font-size:13px; color:#e6edf3;">{{ $log->user->name ?? 'Система' }}</span>
                    </div>
                </td>
                <td><span class="label label-default mono" style="font-size:11px;">{{ $log->action }}</span></td>
                <td style="font-size:13px; color:#8b949e;">
                    @if($log->auditable_type)
                    <span style="color:#e6edf3;">{{ class_basename($log->auditable_type) }}</span>
                    <span style="color:#6e7681;"> #{{ $log->auditable_id }}</span>
                    @else
                    —
                    @endif
                </td>
                <td><span class="mono" style="font-size:12px; color:#6e7681;">{{ $log->ip_address ?? '—' }}</span></td>
                <td style="font-size:12px; color:#8b949e;">{{ $log->created_at?->format('d.m.Y H:i:s') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:48px 16px; text-align:center; color:#6e7681;">
                    <svg width="24" height="24" viewBox="0 0 16 16" fill="currentColor" style="margin:0 auto 12px; display:block; color:#30363d;"><path d="M0 1.75A.75.75 0 0 1 .75 1h4.253c1.227 0 2.317.59 3 1.501A3.743 3.743 0 0 1 11.006 1h4.245a.75.75 0 0 1 .75.75v10.5a.75.75 0 0 1-.75.75h-4.507a2.25 2.25 0 0 0-1.591.659l-.622.621a.75.75 0 0 1-1.06 0l-.622-.621A2.25 2.25 0 0 0 5.258 13H.75a.75.75 0 0 1-.75-.75Zm7.251 10.324.004-5.073-.002-2.253A2.25 2.25 0 0 0 5.003 2.5H1.5v9h3.757a3.75 3.75 0 0 1 1.994.574ZM8.755 4.75l-.004 7.322a3.752 3.752 0 0 1 1.992-.572H14.5v-9h-3.495a2.25 2.25 0 0 0-2.25 2.25Z"/></svg>
                    Записи журнала не найдены.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($logs->hasPages())
    <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-top:1px solid #21262d; font-size:13px; color:#8b949e;">
        <span>Показано {{ $logs->firstItem() }}–{{ $logs->lastItem() }} из {{ $logs->total() }}</span>
        <div style="display:flex; gap:4px;">
            @if(!$logs->onFirstPage())<a href="{{ $logs->previousPageUrl() }}" class="btn btn-secondary btn-sm">Назад</a>@endif
            @if($logs->hasMorePages())<a href="{{ $logs->nextPageUrl() }}" class="btn btn-secondary btn-sm">Вперёд</a>@endif
        </div>
    </div>
    @endif
</div>
@endsection
