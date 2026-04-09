@extends('layouts.app')
@section('title', 'Обзор')
@section('breadcrumb', 'ConfigVault')
@section('breadcrumb_current', 'Обзор')

@section('header_actions')
@can('create', \App\Models\Package::class)
<a href="{{ route('packages.create') }}" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M7.75 2a.75.75 0 0 1 .75.75V7h4.25a.75.75 0 0 1 0 1.5H8.5v4.25a.75.75 0 0 1-1.5 0V8.5H2.75a.75.75 0 0 1 0-1.5H7V2.75A.75.75 0 0 1 7.75 2Z"/></svg>
    Новый пакет
</a>
@endcan
@endsection

@section('content')
<div class="cv-grid" style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-label">Пакеты</div>
        <div class="stat-value">{{ $totalPackages }}</div>
        <div class="stat-sub">всего зарегистрировано</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Активные сборки</div>
        <div class="stat-value" style="{{ $activeBuilds > 0 ? 'color:#d29922' : '' }}">{{ $activeBuilds }}</div>
        <div class="stat-sub">в очереди или выполняются</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Ошибки проверки</div>
        <div class="stat-value" style="{{ $verificationErrors > 0 ? 'color:#f85149' : '' }}">{{ $verificationErrors }}</div>
        <div class="stat-sub">неверные контрольные суммы</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Загрузки</div>
        <div class="stat-value">{{ $weeklyDownloads }}</div>
        <div class="stat-sub">за 7 дней</div>
    </div>
</div>

<div class="cv-grid" style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:16px;">
    <div class="box">
        <div class="box-header">
            <h3>Активность сборок</h3>
            <span class="label label-default">30 дней</span>
        </div>
        <div class="box-body">
            <canvas id="buildChart" height="140"></canvas>
        </div>
    </div>

    <div class="box">
        <div class="box-header">
            <h3>Последние сборки</h3>
            <a href="{{ route('builds.index') }}" style="font-size:12px; color:#58a6ff; text-decoration:none;">Все сборки</a>
        </div>
        <div>
            @forelse($recentBuilds as $build)
            <a href="{{ route('builds.show', $build) }}" style="display:flex; align-items:center; gap:10px; padding:8px 16px; border-bottom:1px solid #21262d; text-decoration:none; transition:background .1s;" onmouseenter="this.style.background='rgba(177,186,196,.04)'" onmouseleave="this.style.background=''">
                <span style="width:8px; height:8px; border-radius:50%; flex-shrink:0; background:{{ match($build->status->value) { 'completed' => '#3fb950', 'failed' => '#f85149', 'processing' => '#d29922', default => '#6e7681' } }};"></span>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; color:#e6edf3; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $build->packageVersion->package->name ?? 'Неизвестно' }}</div>
                    <div style="font-size:12px; color:#8b949e;">v{{ $build->packageVersion->version ?? '—' }}</div>
                </div>
                <div style="font-size:11px; color:#6e7681; flex-shrink:0;">{{ $build->created_at->diffForHumans(null, true) }}</div>
            </a>
            @empty
            <div style="padding:32px 16px; text-align:center; color:#6e7681; font-size:13px;">Сборок пока нет.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <h3>Недавно загруженные файлы</h3>
        <a href="{{ route('packages.index') }}" style="font-size:12px; color:#58a6ff; text-decoration:none;">Все пакеты</a>
    </div>
    <table class="gh-table">
        <thead>
            <tr>
                <th>Файл</th>
                <th>Пакет</th>
                <th>Версия</th>
                <th>Статус</th>
                <th>Загружен</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentFiles as $file)
            <tr>
                <td>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="color:#8b949e; flex-shrink:0;"><path d="M2 1.75C2 .784 2.784 0 3.75 0h6.586c.464 0 .909.184 1.237.513l2.914 2.914c.329.328.513.773.513 1.237v9.586A1.75 1.75 0 0 1 13.25 16h-9.5A1.75 1.75 0 0 1 2 14.25Zm1.75-.25a.25.25 0 0 0-.25.25v12.5c0 .138.112.25.25.25h9.5a.25.25 0 0 0 .25-.25V6h-2.75A1.75 1.75 0 0 1 9 4.25V1.5Zm6.75.062V4.25c0 .138.112.25.25.25h2.688Z"/></svg>
                        <span class="mono" style="color:#e6edf3;">{{ $file->original_name }}</span>
                    </div>
                </td>
                <td style="color:#8b949e;">{{ $file->packageVersion->package->name ?? '—' }}</td>
                <td><span class="mono label label-default">v{{ $file->packageVersion->version ?? '—' }}</span></td>
                <td>
                    @if($file->verification_status->value === 'valid')
                        <span class="label label-success">Проверено</span>
                    @elseif($file->verification_status->value === 'invalid')
                        <span class="label label-danger">Ошибка</span>
                    @else
                        <span class="label label-warning">Ожидание</span>
                    @endif
                </td>
                <td style="color:#8b949e; font-size:12px;">{{ $file->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center; color:#6e7681; padding:32px 16px;">Файлы ещё не загружены. <a href="{{ route('packages.create') }}" style="color:#58a6ff; text-decoration:none;">Создайте пакет</a>, чтобы начать.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
(function() {
    var ctx = document.getElementById('buildChart').getContext('2d');
    var data = @json($buildChartData);
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(function(d) {
                var p = d.date.split('-'); var dt = new Date(+p[0], +p[1]-1, +p[2]);
                return dt.toLocaleDateString('ru', {month:'short', day:'numeric'});
            }),
            datasets: [{ data: data.map(function(d){return d.count;}), backgroundColor:'rgba(63,185,80,.2)', borderColor:'rgba(63,185,80,.8)', borderWidth:1, borderRadius:3 }]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins:{legend:{display:false}},
            scales:{
                x:{ticks:{color:'#6e7681', maxTicksLimit:8, font:{size:11}}, grid:{display:false}, border:{display:false}},
                y:{ticks:{color:'#6e7681', stepSize:1, font:{size:11}}, grid:{color:'rgba(48,54,61,.8)'}, border:{display:false}, beginAtZero:true}
            }
        }
    });
})();
</script>
@endsection
