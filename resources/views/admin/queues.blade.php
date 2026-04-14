@extends('layouts.app')
@section('title', 'Очереди задач')
@section('breadcrumb', 'Администрирование')
@section('breadcrumb_current', 'Очереди задач')

@section('content')
<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

    <div class="box">
        <div class="box-header">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:32px; height:32px; background:rgba(210,153,34,.1); border:1px solid rgba(210,153,34,.2); border-radius:6px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="color:#d29922;"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0ZM1.5 8a6.5 6.5 0 1 0 13 0 6.5 6.5 0 0 0-13 0Zm7-3.25v2.992l2.028.812a.75.75 0 0 1-.557 1.392l-2.5-1A.751.751 0 0 1 7 8.25v-3.5a.75.75 0 0 1 1.5 0Z"/></svg>
                </div>
                <div>
                    <h3>Ожидающие задачи</h3>
                    <div style="font-size:12px; color:#6e7681; margin-top:1px;">Ожидают обработки</div>
                </div>
            </div>
        </div>

        @if($pendingJobs->count() > 0)
        <div style="padding:12px 16px; display:flex; flex-direction:column; gap:8px;">
            @foreach($pendingJobs as $job)
            <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 12px; background:#0d1117; border:1px solid #30363d; border-radius:6px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="width:8px; height:8px; border-radius:50%; background:#d29922; display:inline-block; animation:pulse 1.5s infinite;"></span>
                    <span class="mono" style="font-size:13px; color:#e6edf3;">{{ $job->queue }}</span>
                </div>
                <span class="label label-warning">{{ $job->count }} в очереди</span>
            </div>
            @endforeach
        </div>
        <div style="margin:0 16px 16px; padding:12px; background:#0d1117; border:1px solid #30363d; border-radius:6px;">
            <div style="font-size:12px; color:#8b949e; margin-bottom:6px;">Запустите обработчик очереди:</div>
            <pre style="font-size:12px; color:#3fb950; font-family:monospace; margin:0; white-space:pre-wrap;">php artisan queue:work --queue=builds,verification</pre>
        </div>
        @else
        <div style="padding:48px 16px; text-align:center; color:#6e7681;">
            <svg width="24" height="24" viewBox="0 0 16 16" fill="currentColor" style="margin:0 auto 12px; display:block; color:#238636;"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0ZM1.5 8a6.5 6.5 0 1 0 13 0 6.5 6.5 0 0 0-13 0Zm10.28-1.72-4.5 4.5a.75.75 0 0 1-1.06 0l-2-2a.751.751 0 0 1 .018-1.042.751.751 0 0 1 1.042-.018l1.47 1.47 3.97-3.97a.751.751 0 0 1 1.042.018.751.751 0 0 1 .018 1.042Z"/></svg>
            Очередь пуста — задач нет
        </div>
        @endif
    </div>

    <div class="box">
        <div class="box-header">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:32px; height:32px; background:rgba(248,81,73,.1); border:1px solid rgba(248,81,73,.2); border-radius:6px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="color:#f85149;"><path d="M6.457 1.047c.659-1.234 2.427-1.234 3.086 0l6.082 11.378A1.75 1.75 0 0 1 14.082 15H1.918a1.75 1.75 0 0 1-1.543-2.575Zm1.763.707a.25.25 0 0 0-.44 0L1.698 13.132a.25.25 0 0 0 .22.368h12.164a.25.25 0 0 0 .22-.368Zm.53 3.996v2.5a.75.75 0 0 1-1.5 0v-2.5a.75.75 0 0 1 1.5 0ZM9 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z"/></svg>
                </div>
                <div>
                    <h3>Упавшие задачи</h3>
                    <div style="font-size:12px; color:#6e7681; margin-top:1px;">{{ $failedJobs->count() }} {{ $failedJobs->count()===1?'ошибка':'ошибок' }} зафиксировано</div>
                </div>
            </div>
        </div>

        @if($failedJobs->count() > 0)
        <div style="padding:12px 16px; display:flex; flex-direction:column; gap:8px;">
            @foreach($failedJobs->take(5) as $job)
            @php
                $payload = json_decode($job->payload, true);
                $displayName = class_basename($payload['displayName'] ?? 'Unknown');
            @endphp
            <div style="padding:12px; background:rgba(218,54,51,.05); border:1px solid rgba(248,81,73,.2); border-radius:6px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                    <span class="mono" style="font-size:13px; color:#e6edf3;">{{ $job->queue }}</span>
                    <span style="font-size:11px; color:#6e7681;">{{ $job->failed_at }}</span>
                </div>
                <div style="font-size:12px; color:#f85149; margin-bottom:4px;">{{ $displayName }}</div>
                @if($job->exception)
                <pre style="font-size:11px; color:#8b949e; font-family:monospace; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100%;">{{ substr($job->exception, 0, 140) }}</pre>
                @endif
            </div>
            @endforeach
        </div>
        <div style="margin:0 16px 16px; padding:12px; background:#0d1117; border:1px solid #30363d; border-radius:6px;">
            <div style="font-size:12px; color:#8b949e; margin-bottom:6px;">Повторить упавшие задачи:</div>
            <pre style="font-size:12px; color:#3fb950; font-family:monospace; margin:0;">php artisan queue:retry all</pre>
        </div>
        @else
        <div style="padding:48px 16px; text-align:center; color:#6e7681;">
            <svg width="24" height="24" viewBox="0 0 16 16" fill="currentColor" style="margin:0 auto 12px; display:block; color:#238636;"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0ZM1.5 8a6.5 6.5 0 1 0 13 0 6.5 6.5 0 0 0-13 0Zm10.28-1.72-4.5 4.5a.75.75 0 0 1-1.06 0l-2-2a.751.751 0 0 1 .018-1.042.751.751 0 0 1 1.042-.018l1.47 1.47 3.97-3.97a.751.751 0 0 1 1.042.018.751.751 0 0 1 .018 1.042Z"/></svg>
            Упавших задач нет — система работает нормально
        </div>
        @endif
    </div>

</div>
<style>@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}</style>
@endsection
