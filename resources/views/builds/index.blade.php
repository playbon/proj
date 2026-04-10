@extends('layouts.app')
@section('title', 'Сборки')
@section('breadcrumb', 'ConfigVault')
@section('breadcrumb_current', 'Сборки')

@section('content')
<div style="margin-bottom:16px; display:flex; gap:4px; flex-wrap:wrap;">
    @foreach([''=>'Все','queued'=>'В очереди','processing'=>'Выполняется','completed'=>'Завершено','failed'=>'Ошибка','pending_review'=>'На проверке','blocked'=>'Заблокированы'] as $val=>$label)
    <a href="{{ request()->fullUrlWithQuery(['status'=>$val]) }}"
        style="padding:5px 14px; font-size:13px; font-weight:500; border-radius:6px; border:1px solid; text-decoration:none; transition:background .12s;
        {{ (request('status')===$val || ($val===''&&!request('status'))) ? 'background:#21262d; border-color:#8b949e; color:#e6edf3;' : 'background:transparent; border-color:#30363d; color:#8b949e;' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

<div class="box">
    <table class="gh-table">
        <thead>
            <tr>
                <th>Сборка</th>
                <th>Пакет</th>
                <th>Версия</th>
                <th>Статус</th>
                <th>Запущена</th>
                <th>Длительность</th>
                <th>Создана</th>
            </tr>
        </thead>
        <tbody>
            @forelse($builds as $build)
            <tr style="cursor:pointer;" onclick="location.href='{{ route('builds.show', $build) }}'">
                <td><a href="{{ route('builds.show', $build) }}" class="mono" style="color:#58a6ff; text-decoration:none;">{{ substr($build->uuid,0,8) }}</a></td>
                <td style="font-weight:600;">{{ $build->packageVersion->package->name ?? 'Н/Д' }}</td>
                <td><span class="label label-default mono">v{{ $build->packageVersion->version ?? '—' }}</span></td>
                <td>
                    @php $s = $build->status->value; @endphp
                    <span class="label {{ match($s){'completed'=>'label-success','failed'=>'label-danger','blocked'=>'label-danger','processing'=>'label-warning','pending_review'=>'label-warning',default=>'label-info'} }}">
                        @if($s==='processing')<span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:currentColor;margin-right:4px;animation:pulse 1.5s infinite;"></span>@endif
                        {{ match($s){'completed'=>'Завершено','failed'=>'Ошибка','processing'=>'Выполняется','pending_review'=>'На проверке','blocked'=>'Заблокирована',default=>'В очереди'} }}
                    </span>
                </td>
                <td style="color:#8b949e; font-size:13px;">{{ $build->user->name ?? 'Система' }}</td>
                <td style="color:#8b949e; font-size:12px; font-family:monospace;">
                    @if($build->started_at && $build->completed_at) {{ $build->started_at->diffInSeconds($build->completed_at) }}с
                    @elseif($build->started_at) выполняется…
                    @else —@endif
                </td>
                <td style="color:#8b949e; font-size:12px;">{{ $build->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="padding:48px 16px; text-align:center; color:#6e7681;">Сборки не найдены.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($builds->hasPages())
<div style="display:flex; align-items:center; justify-content:space-between; margin-top:16px; font-size:13px; color:#8b949e;">
    <span>Показано {{ $builds->firstItem() }}–{{ $builds->lastItem() }} из {{ $builds->total() }}</span>
    <div style="display:flex; gap:4px;">
        @if(!$builds->onFirstPage())<a href="{{ $builds->previousPageUrl() }}" class="btn btn-secondary btn-sm">Назад</a>@endif
        @if($builds->hasMorePages())<a href="{{ $builds->nextPageUrl() }}" class="btn btn-secondary btn-sm">Вперёд</a>@endif
    </div>
</div>
@endif

<style>@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}</style>
@endsection
