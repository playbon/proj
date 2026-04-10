@extends('layouts.app')
@section('title', 'Сборка ' . substr($build->uuid, 0, 8))
@section('breadcrumb', 'Сборки')
@section('breadcrumb_current', substr($build->uuid, 0, 8))

@section('header_actions')
<a href="{{ route('builds.index') }}" class="btn btn-secondary">Все сборки</a>
@endsection

@section('content')
@php
    $s = $build->status->value;
    $statusMap = [
        'completed'      => ['label' => 'Завершено',         'class' => 'label-success'],
        'failed'         => ['label' => 'Ошибка',            'class' => 'label-danger'],
        'processing'     => ['label' => 'Выполняется',       'class' => 'label-warning'],
        'queued'         => ['label' => 'В очереди',         'class' => 'label-info'],
        'pending_review' => ['label' => 'Ожидает проверки',  'class' => 'label-warning'],
        'blocked'        => ['label' => 'Заблокирована',     'class' => 'label-danger'],
    ];
    $st = $statusMap[$s] ?? ['label' => $s, 'class' => 'label-default'];
    $isSettled = in_array($s, ['completed', 'failed', 'blocked', 'pending_review']);

    // Report eligibility
    $canReport = !auth()->user()->isGhost()
        && !auth()->user()->isAdmin()
        && !auth()->user()->isCreator()
        && !$build->user->isCreator()
        && auth()->id() !== $build->user_id
        && $s !== 'blocked';

    $alreadyReported = $canReport
        ? \App\Models\BuildReport::where('build_id', $build->id)->where('reporter_id', auth()->id())->exists()
        : false;

    $canDownload = $s === 'completed' && !auth()->user()->isGhost();
@endphp

{{-- Blocked / PendingReview banners --}}
@if($s === 'blocked')
<div class="flash-error" style="margin-bottom:16px; display:flex; align-items:center; gap:10px;">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
    Сборка заблокирована по результатам жалобы. Скачивание недоступно.
</div>
@elseif($s === 'pending_review')
<div class="flash-warning" style="margin-bottom:16px;">
    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M6.457 1.047c.659-1.234 2.427-1.234 3.086 0l6.082 11.378A1.75 1.75 0 0 1 14.082 15H1.918a1.75 1.75 0 0 1-1.543-2.575Zm1.763.707a.25.25 0 0 0-.44 0L1.698 13.132a.25.25 0 0 0 .22.368h12.164a.25.25 0 0 0 .22-.368ZM9 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM8 5.25a.75.75 0 0 1 .75.75v2.5a.75.75 0 0 1-1.5 0V6A.75.75 0 0 1 8 5.25Z"/></svg>
    <span>Сборка ожидает проверки администратором перед публикацией. Это первая сборка издателя.</span>
    @if(auth()->user()->isAdmin())
    <a href="{{ route('admin.build-review.index') }}" style="color:#d29922; font-weight:600; text-decoration:underline;">Перейти к проверке →</a>
    @endif
</div>
@endif

<div class="cv-grid" style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:16px;">
    <div class="stat-card" style="grid-column:span 2;">
        <div class="stat-label">UUID сборки</div>
        <div class="mono" style="font-size:12px; color:#e6edf3; margin-top:4px; word-break:break-all;">{{ $build->uuid }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Статус</div>
        <div style="margin-top:8px;">
            <span class="label {{ $st['class'] }}" style="font-size:13px; padding:3px 10px;">{{ $st['label'] }}</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Длительность</div>
        <div class="stat-value" style="font-size:20px; margin-top:8px;">
            @if($build->started_at && $build->completed_at) {{ $build->started_at->diffInSeconds($build->completed_at) }}с
            @elseif($build->started_at) <span style="font-size:14px; color:#d29922;">Выполняется…</span>
            @else <span style="font-size:14px; color:#6e7681;">В очереди</span>
            @endif
        </div>
    </div>
</div>

<div class="cv-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
    <div class="box">
        <div class="box-header"><h3>Детали сборки</h3></div>
        <table style="width:100%; border-collapse:collapse;">
            @foreach([
                ['Пакет',        '<a href="'.route('packages.show',$build->packageVersion->package).'" style="color:#58a6ff;text-decoration:none;">'.e($build->packageVersion->package->name ?? '—').'</a>'],
                ['Версия',       '<span class="label label-default mono">v'.e($build->packageVersion->version ?? '—').'</span>'],
                ['Запущена',     e($build->user->name ?? 'Система')],
                ['В очередь',    $build->created_at->format('d.m.Y H:i:s')],
                ['Начата',       $build->started_at?->format('d.m.Y H:i:s') ?? '—'],
                ['Завершена',    $build->completed_at?->format('d.m.Y H:i:s') ?? '—'],
            ] as [$label, $value])
            <tr style="border-bottom:1px solid #21262d;">
                <td style="padding:10px 16px; font-size:12px; color:#8b949e; font-weight:600; width:120px; white-space:nowrap;">{{ $label }}</td>
                <td style="padding:10px 16px; font-size:13px; color:#e6edf3;">{!! $value !!}</td>
            </tr>
            @endforeach
        </table>
        @if($build->error_message)
        <div style="margin:16px; padding:12px; background:rgba(218,54,51,.1); border:1px solid rgba(248,81,73,.3); border-radius:6px;">
            <div style="font-size:12px; font-weight:600; color:#f85149; margin-bottom:4px;">Ошибка</div>
            <pre style="font-size:12px; color:#f85149; white-space:pre-wrap; margin:0; font-family:monospace;">{{ $build->error_message }}</pre>
        </div>
        @endif
    </div>

    <div class="box">
        <div class="box-header">
            <h3>Артефакты</h3>
            @if($build->artifacts->count())
            <span class="label label-success">{{ $build->artifacts->count() }} {{ $build->artifacts->count() === 1 ? 'файл' : 'файла' }}</span>
            @endif
        </div>
        <div id="artifacts-list" style="padding:8px 0;">
            @forelse($build->artifacts as $artifact)
            <div style="display:flex; align-items:center; gap:12px; padding:10px 16px; border-bottom:1px solid #21262d;">
                @if($artifact->type->value === 'zip')
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="color:#58a6ff; flex-shrink:0;"><path d="M3.5 1.75v11.5c0 .138.112.25.25.25h3.17a.75.75 0 0 1 0 1.5H3.75A1.75 1.75 0 0 1 2 13.25V1.75C2 .784 2.784 0 3.75 0h5.586c.464 0 .909.184 1.237.513l2.914 2.914c.329.328.513.773.513 1.237v4.286a.75.75 0 0 1-1.5 0V4.664h-2.75A1.75 1.75 0 0 1 8 2.914V1.5H3.75a.25.25 0 0 0-.25.25ZM9.5 2.914V4.25c0 .138.112.25.25.25h1.336Z"/></svg>
                @else
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="color:#d29922; flex-shrink:0;"><path d="M2 1.75C2 .784 2.784 0 3.75 0h6.586c.464 0 .909.184 1.237.513l2.914 2.914c.329.328.513.773.513 1.237v9.586A1.75 1.75 0 0 1 13.25 16h-9.5A1.75 1.75 0 0 1 2 14.25Zm1.75-.25a.25.25 0 0 0-.25.25v12.5c0 .138.112.25.25.25h9.5a.25.25 0 0 0 .25-.25V6h-2.75A1.75 1.75 0 0 1 9 4.25V1.5Zm6.75.062V4.25c0 .138.112.25.25.25h2.688Z"/></svg>
                @endif
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13px; font-weight:600; color:#e6edf3; text-transform:uppercase;">{{ $artifact->type->value }}</div>
                    @if($artifact->file_size)
                    <div style="font-size:12px; color:#8b949e;">{{ number_format($artifact->file_size / 1024, 1) }} КБ</div>
                    @endif
                </div>
                @if($canDownload)
                <a href="{{ route('artifacts.download', $artifact) }}" class="btn btn-secondary btn-sm">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M2.75 14A1.75 1.75 0 0 1 1 12.25v-2.5a.75.75 0 0 1 1.5 0v2.5c0 .138.112.25.25.25h10.5a.25.25 0 0 0 .25-.25v-2.5a.75.75 0 0 1 1.5 0v2.5A1.75 1.75 0 0 1 13.25 14Zm-1-5.689 3.22 3.22a.749.749 0 0 0 1.06 0l3.22-3.22a.749.749 0 1 0-1.06-1.06L8.75 8.44V1.75a.75.75 0 0 0-1.5 0V8.44L5.81 7.251a.749.749 0 1 0-1.06 1.06Z"/></svg>
                    Скачать
                </a>
                @elseif(auth()->user()->isGhost())
                <span style="font-size:12px; color:#6e7681; padding:3px 8px; background:#21262d; border-radius:4px;">Нужна верификация</span>
                @elseif($s === 'blocked' || $s === 'pending_review')
                <span style="font-size:12px; color:#6e7681; padding:3px 8px; background:#21262d; border-radius:4px;">Недоступно</span>
                @endif
            </div>
            @empty
            <div style="padding:32px 16px; text-align:center; color:#6e7681; font-size:13px;">
                @if(in_array($s, ['queued', 'processing']))
                <div style="width:24px;height:24px;border:2px solid #30363d;border-top-color:#d29922;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 12px;"></div>
                Сборка выполняется…
                @else
                Артефакты не созданы.
                @endif
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Report section --}}
@if($canReport)
<div class="box" style="margin-bottom:16px;">
    <div class="box-header">
        <h3 style="display:flex; align-items:center; gap:8px;">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="#d29922"><path d="M6.457 1.047c.659-1.234 2.427-1.234 3.086 0l6.082 11.378A1.75 1.75 0 0 1 14.082 15H1.918a1.75 1.75 0 0 1-1.543-2.575Zm1.763.707a.25.25 0 0 0-.44 0L1.698 13.132a.25.25 0 0 0 .22.368h12.164a.25.25 0 0 0 .22-.368ZM9 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM8 5.25a.75.75 0 0 1 .75.75v2.5a.75.75 0 0 1-1.5 0V6A.75.75 0 0 1 8 5.25Z"/></svg>
            Пожаловаться на сборку
        </h3>
    </div>
    <div style="padding:16px;">
        @if($alreadyReported)
        <p style="font-size:13px; color:#8b949e; margin:0;">Вы уже отправили жалобу на эту сборку. Ожидайте рассмотрения администратором.</p>
        @else
        <form method="POST" action="{{ route('builds.report', $build) }}">
            @csrf
            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label" for="report_reason">Причина жалобы</label>
                <textarea id="report_reason" name="reason" rows="3" class="form-textarea" required
                    placeholder="Опишите, что именно нарушает правила платформы..."></textarea>
            </div>
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Отправить жалобу администратору?')">
                Отправить жалобу
            </button>
        </form>
        @endif
    </div>
</div>
@endif

{{-- Manifest --}}
@if($manifest)
<div class="box">
    <div class="box-header">
        <h3>Манифест</h3>
        <span class="label label-default mono">manifest.json</span>
    </div>
    <div style="background:#010409; border-top:1px solid #30363d; border-radius:0 0 6px 6px; overflow:hidden;">
        <div style="display:flex; gap:6px; padding:8px 12px; background:#161b22; border-bottom:1px solid #21262d;">
            <span style="width:12px;height:12px;border-radius:50%;background:#ff5f57;"></span>
            <span style="width:12px;height:12px;border-radius:50%;background:#febc2e;"></span>
            <span style="width:12px;height:12px;border-radius:50%;background:#28c840;"></span>
        </div>
        <pre style="padding:16px; font-family:monospace; font-size:12px; color:#3fb950; margin:0; overflow-x:auto; line-height:1.6;">{{ json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
</div>
@endif

<style>@keyframes spin { to { transform: rotate(360deg) } }</style>
<script>
var status = '{{ $s }}';
if (status !== 'completed' && status !== 'failed' && status !== 'blocked' && status !== 'pending_review') {
    setTimeout(function () { location.reload(); }, 3000);
}
</script>
@endsection
