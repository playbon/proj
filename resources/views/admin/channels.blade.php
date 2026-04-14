@extends('layouts.app')
@section('title', 'Каналы')
@section('breadcrumb', 'Администрирование')
@section('breadcrumb_current', 'Каналы')

@section('content')
<div class="box" style="margin-bottom:16px;">
    <div class="box-header"><h3>Создать канал</h3></div>
    <div class="box-body">
        <form method="POST" action="{{ route('admin.channels.store') }}" style="display:flex; flex-wrap:wrap; gap:8px; align-items:flex-end;">
            @csrf
            <div class="form-group" style="margin:0; flex-shrink:0;">
                <label class="form-label" style="font-size:12px;">Название <span style="color:#f85149;">*</span></label>
                <input type="text" name="name" placeholder="Stable" required class="form-input" style="width:130px;" value="{{ old('name') }}">
            </div>
            <div class="form-group" style="margin:0; flex-shrink:0;">
                <label class="form-label" style="font-size:12px;">Slug <span style="color:#f85149;">*</span></label>
                <input type="text" name="slug" placeholder="stable" required class="form-input mono" style="width:130px;" value="{{ old('slug') }}">
            </div>
            <div class="form-group" style="margin:0; flex:1; min-width:200px;">
                <label class="form-label" style="font-size:12px;">Описание</label>
                <input type="text" name="description" placeholder="Стабильные релизы для продакшн" class="form-input" value="{{ old('description') }}">
            </div>
            <button type="submit" class="btn btn-primary" style="flex-shrink:0;">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M7.75 2a.75.75 0 0 1 .75.75V7h4.25a.75.75 0 0 1 0 1.5H8.5v4.25a.75.75 0 0 1-1.5 0V8.5H2.75a.75.75 0 0 1 0-1.5H7V2.75A.75.75 0 0 1 7.75 2Z"/></svg>
                Создать канал
            </button>
        </form>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <h3>Каналы</h3>
        <span class="label label-default">{{ $channels->count() }} всего</span>
    </div>
    <table class="gh-table">
        <thead>
            <tr>
                <th>Название</th>
                <th>Slug</th>
                <th>Описание</th>
                <th style="text-align:center;">Пакеты</th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($channels as $channel)
            <tr>
                <td style="font-weight:600; color:#e6edf3;">{{ $channel->name }}</td>
                <td><code class="mono" style="font-size:12px; color:#8b949e;">{{ $channel->slug }}</code></td>
                <td style="font-size:13px; color:#8b949e;">{{ $channel->description ?? '—' }}</td>
                <td style="text-align:center; font-weight:600; color:#e6edf3;">{{ $channel->packages_count }}</td>
                <td>
                    @if($channel->is_default)
                    <span class="label label-success">По умолчанию</span>
                    @endif
                </td>
                <td style="text-align:right;">
                    @if(!$channel->is_default)
                    <form method="POST" action="{{ route('admin.channels.destroy', $channel) }}" onsubmit="return confirm('Удалить канал {{ $channel->name }}?')" style="margin:0; display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                    </form>
                    @else
                    <span style="font-size:12px; color:#6e7681;">Защищён</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding:48px 16px; text-align:center; color:#6e7681;">Каналов пока нет. Создайте выше.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
