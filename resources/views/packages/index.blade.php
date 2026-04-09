@extends('layouts.app')
@section('title', 'Пакеты')
@section('breadcrumb', 'ConfigVault')
@section('breadcrumb_current', 'Пакеты')

@section('header_actions')
@can('create', \App\Models\Package::class)
<a href="{{ route('packages.create') }}" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M7.75 2a.75.75 0 0 1 .75.75V7h4.25a.75.75 0 0 1 0 1.5H8.5v4.25a.75.75 0 0 1-1.5 0V8.5H2.75a.75.75 0 0 1 0-1.5H7V2.75A.75.75 0 0 1 7.75 2Z"/></svg>
    Новый пакет
</a>
@endcan
@endsection

@section('content')
<div style="margin-bottom:16px;">
    <form method="GET" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <div style="position:relative; flex:1; min-width:200px;">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="position:absolute; left:8px; top:50%; transform:translateY(-50%); color:#6e7681; pointer-events:none;"><path d="M10.68 11.74a6 6 0 0 1-7.922-8.982 6 6 0 0 1 8.982 7.922l3.04 3.04a.749.749 0 0 1-.326 1.275.749.749 0 0 1-.734-.215ZM11.5 7a4.499 4.499 0 1 0-8.997 0A4.499 4.499 0 0 0 11.5 7Z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Поиск пакетов…"
                class="form-input" style="padding-left:32px;">
        </div>
        <select name="channel" class="form-select" style="width:180px;">
            <option value="">Все каналы</option>
            @foreach($channels as $channel)
            <option value="{{ $channel->id }}" {{ request('channel') == $channel->id ? 'selected' : '' }}>{{ $channel->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary">Найти</button>
        @if(request('search') || request('channel'))
        <a href="{{ route('packages.index') }}" style="font-size:14px; color:#8b949e; text-decoration:none;">Сбросить</a>
        @endif
    </form>
</div>

<div class="box">
    <table class="gh-table">
        <thead>
            <tr>
                <th>Пакет</th>
                <th>Канал</th>
                <th>Владелец</th>
                <th style="text-align:center;">Версии</th>
                <th>Статус</th>
                <th>Изменён</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($packages as $package)
            <tr>
                <td>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="color:#8b949e; flex-shrink:0;"><path d="m8.878.392 5.25 3.045c.54.314.872.89.872 1.514v6.098a1.75 1.75 0 0 1-.872 1.514l-5.25 3.045a1.75 1.75 0 0 1-1.756 0l-5.25-3.045A1.75 1.75 0 0 1 1 11.049V4.951c0-.624.332-1.2.872-1.514L7.122.392a1.75 1.75 0 0 1 1.756 0ZM7.875 1.69l-4.63 2.685L8 7.133l4.755-2.758-4.63-2.685a.248.248 0 0 0-.25 0ZM2.5 5.677v5.372c0 .096.052.185.132.232l4.868 2.832V8.913Zm6.5 8.436 4.868-2.832a.269.269 0 0 0 .132-.232V5.677L9 8.913Z"/></svg>
                        <div>
                            <a href="{{ route('packages.show', $package) }}" style="font-size:14px; font-weight:600; color:#58a6ff; text-decoration:none;">{{ $package->name }}</a>
                            @if($package->description)
                            <div style="font-size:12px; color:#8b949e; margin-top:2px; max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $package->description }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td>
                    @if($package->channel)
                    <span class="label label-default mono">{{ $package->channel->name }}</span>
                    @else<span style="color:#6e7681;">—</span>@endif
                </td>
                <td style="color:#8b949e; font-size:13px;">{{ $package->user->name ?? '—' }}</td>
                <td style="text-align:center; font-weight:600;">{{ $package->versions->count() }}</td>
                <td>
                    @if($package->is_active)
                    <span class="label label-success">Активен</span>
                    @else
                    <span class="label label-default">Неактивен</span>
                    @endif
                </td>
                <td style="color:#8b949e; font-size:12px;">{{ $package->updated_at->diffForHumans() }}</td>
                <td style="white-space:nowrap;">
                    <div style="display:flex; align-items:center; gap:4px; justify-content:flex-end;">
                        @can('update', $package)
                        <a href="{{ route('packages.edit', $package) }}" class="btn btn-secondary btn-sm">Изменить</a>
                        @endcan
                        @can('delete', $package)
                        <form method="POST" action="{{ route('packages.destroy', $package) }}" onsubmit="return confirm('Удалить пакет {{ $package->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; padding:48px 16px; color:#6e7681;">
                    @if(request('search') || request('channel'))
                    Пакеты не найдены.
                    @else
                    Пакетов пока нет. @can('create', \App\Models\Package::class)<a href="{{ route('packages.create') }}" style="color:#58a6ff; text-decoration:none;">Создайте первый пакет</a>.@endcan
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($packages->hasPages())
<div style="display:flex; align-items:center; justify-content:space-between; margin-top:16px; font-size:13px; color:#8b949e;">
    <span>Показано {{ $packages->firstItem() }}–{{ $packages->lastItem() }} из {{ $packages->total() }}</span>
    <div style="display:flex; gap:4px;">
        @if(!$packages->onFirstPage())
        <a href="{{ $packages->previousPageUrl() }}" class="btn btn-secondary btn-sm">Назад</a>
        @endif
        @if($packages->hasMorePages())
        <a href="{{ $packages->nextPageUrl() }}" class="btn btn-secondary btn-sm">Вперёд</a>
        @endif
    </div>
</div>
@endif
@endsection
