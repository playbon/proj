@extends('layouts.app')
@section('title', 'Проверка сборок')
@section('breadcrumb', 'Администрирование')
@section('breadcrumb_current', 'Проверка сборок')

@section('content')
<div style="margin-bottom:16px; padding:12px 16px; background:rgba(88,166,255,.08); border:1px solid rgba(88,166,255,.3); border-radius:6px; font-size:13px; color:#8b949e; display:flex; align-items:flex-start; gap:10px;">
    <svg width="16" height="16" viewBox="0 0 16 16" fill="#58a6ff" style="flex-shrink:0; margin-top:1px;"><path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8Zm8-6.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13ZM6.5 7.75A.75.75 0 0 1 7.25 7h1a.75.75 0 0 1 .75.75v2.75h.25a.75.75 0 0 1 0 1.5h-2a.75.75 0 0 1 0-1.5h.25v-2h-.25a.75.75 0 0 1-.75-.75ZM8 6a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"/></svg>
    <span>Первая сборка нового издателя проходит ручную проверку перед публикацией. Здесь отображаются сборки, ожидающие вашего решения.</span>
</div>

<div class="box">
    <div class="box-header">
        <h3>Сборки на проверке</h3>
        @if($builds->total() > 0)
        <span class="label label-warning">{{ $builds->total() }} ожидает</span>
        @else
        <span class="label label-success">Очередь пуста</span>
        @endif
    </div>

    @if($builds->isEmpty())
    <div style="padding:48px; text-align:center; color:#6e7681;">
        <svg width="32" height="32" viewBox="0 0 16 16" fill="currentColor" style="margin-bottom:12px; opacity:.4;"><path d="M8 16A8 8 0 1 1 8 0a8 8 0 0 1 0 16Zm3.78-9.72a.751.751 0 0 0-.018-1.042.751.751 0 0 0-1.042-.018L6.75 9.19 5.28 7.72a.751.751 0 0 0-1.042.018.751.751 0 0 0-.018 1.042l2 2a.75.75 0 0 0 1.06 0Z"/></svg>
        <div style="font-size:14px; font-weight:600; color:#8b949e;">Нет сборок для проверки</div>
        <div style="font-size:12px; margin-top:4px;">Все первые сборки изданы или заблокированы.</div>
    </div>
    @else
    <table class="gh-table">
        <thead>
            <tr>
                <th>UUID сборки</th>
                <th>Пакет / Версия</th>
                <th>Издатель</th>
                <th>Завершена</th>
                <th style="text-align:right;">Действия</th>
            </tr>
        </thead>
        <tbody>
        @foreach($builds as $build)
        <tr>
            <td>
                <a href="{{ route('builds.show', $build) }}" style="color:#58a6ff; text-decoration:none; font-family:monospace; font-size:13px; font-weight:600;">
                    {{ substr($build->uuid, 0, 8) }}
                </a>
                <div style="font-size:11px; font-family:monospace; color:#6e7681; margin-top:2px;">{{ $build->uuid }}</div>
            </td>
            <td>
                @if($build->packageVersion?->package)
                <a href="{{ route('packages.show', $build->packageVersion->package) }}" style="color:#e6edf3; text-decoration:none; font-weight:600; font-size:13px;">
                    {{ $build->packageVersion->package->name }}
                </a>
                <div style="margin-top:3px;"><span class="label label-default mono">v{{ $build->packageVersion->version }}</span></div>
                @else
                <span style="color:#6e7681;">—</span>
                @endif
            </td>
            <td>
                <span style="font-size:13px; font-weight:600; color:#e6edf3;">{{ $build->user->name ?? '—' }}</span>
                <div style="font-size:11px; color:#6e7681; margin-top:2px;">первая сборка</div>
            </td>
            <td>
                <span style="font-size:12px; color:#6e7681;">{{ $build->completed_at?->format('d.m.Y H:i') ?? '—' }}</span>
            </td>
            <td style="text-align:right; white-space:nowrap;">
                <a href="{{ route('builds.show', $build) }}" class="btn btn-secondary btn-sm" style="margin-right:4px;">Просмотреть</a>
                <form method="POST" action="{{ route('admin.build-review.approve', $build) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">Одобрить</button>
                </form>
                <form method="POST" action="{{ route('admin.build-review.reject', $build) }}" style="display:inline; margin-left:4px;">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Заблокировать сборку?')">Отклонить</button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>

    @if($builds->hasPages())
    <div style="padding:16px; border-top:1px solid #21262d;">
        {{ $builds->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
