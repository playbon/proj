@extends('layouts.app')
@section('title', 'Жалобы на пакеты')
@section('breadcrumb', 'Администрирование')
@section('breadcrumb_current', 'Жалобы на пакеты')

@section('content')
<div class="box">
    <div class="box-header">
        <h3>Жалобы на пакеты</h3>
        @if($reports->total() > 0)
        <span class="label label-danger">{{ $reports->total() }} ожидает</span>
        @else
        <span class="label label-success">Всё проверено</span>
        @endif
    </div>

    @if($reports->isEmpty())
    <div style="padding:48px; text-align:center; color:#6e7681;">
        <svg width="32" height="32" viewBox="0 0 16 16" fill="currentColor" style="margin-bottom:12px; opacity:.4;"><path d="M8 16A8 8 0 1 1 8 0a8 8 0 0 1 0 16Zm3.78-9.72a.751.751 0 0 0-.018-1.042.751.751 0 0 0-1.042-.018L6.75 9.19 5.28 7.72a.751.751 0 0 0-1.042.018.751.751 0 0 0-.018 1.042l2 2a.75.75 0 0 0 1.06 0Z"/></svg>
        <div style="font-size:14px; font-weight:600; color:#8b949e;">Нет активных жалоб</div>
        <div style="font-size:12px; margin-top:4px;">Все жалобы на пакеты рассмотрены.</div>
    </div>
    @else
    <table class="gh-table">
        <thead>
            <tr>
                <th>Пакет</th>
                <th>Владелец</th>
                <th>Жалобщик</th>
                <th>Причина</th>
                <th>Дата</th>
                <th style="text-align:right;">Действия</th>
            </tr>
        </thead>
        <tbody>
        @foreach($reports as $report)
        <tr>
            <td>
                <a href="{{ route('packages.show', $report->package) }}" style="color:#58a6ff; text-decoration:none; font-weight:600; font-size:13px;">
                    {{ $report->package->name }}
                </a>
                <div style="font-size:11px; color:#6e7681; margin-top:2px; font-family:monospace;">{{ $report->package->slug }}</div>
            </td>
            <td>
                <div style="font-size:13px; font-weight:600; color:#e6edf3;">{{ $report->package->user->name ?? '—' }}</div>
                <div style="font-size:11px; color:#6e7681; margin-top:2px;">
                    @php $bans = $report->package->user->ban_count ?? 0; @endphp
                    {{ $bans }} {{ $bans === 1 ? 'блокировка' : ($bans < 5 ? 'блокировки' : 'блокировок') }}
                    @if($report->package->user->isBanned())
                    <span class="label label-danger" style="font-size:10px; margin-left:4px;">бан</span>
                    @endif
                </div>
            </td>
            <td>
                <span style="font-size:13px; color:#e6edf3;">{{ $report->reporter->name ?? '—' }}</span>
            </td>
            <td style="max-width:280px;">
                <div style="font-size:13px; color:#8b949e; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $report->reason }}">
                    {{ $report->reason }}
                </div>
            </td>
            <td>
                <span style="font-size:12px; color:#6e7681;">{{ $report->created_at->format('d.m.Y H:i') }}</span>
            </td>
            <td style="text-align:right; white-space:nowrap;">
                <form method="POST" action="{{ route('admin.package-reports.accept', $report) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Принять жалобу? Пакет будет деактивирован, владелец получит бан.')">
                        Принять
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.package-reports.reject', $report) }}" style="display:inline; margin-left:4px;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">Отклонить</button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>

    @if($reports->hasPages())
    <div style="padding:16px; border-top:1px solid #21262d;">
        {{ $reports->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
