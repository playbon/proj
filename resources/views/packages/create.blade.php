@extends('layouts.app')
@section('title', 'Новый пакет')
@section('breadcrumb', 'Пакеты')
@section('breadcrumb_current', 'Новый пакет')

@section('content')
<div style="max-width:640px;">
    <div class="box">
        <div class="box-header"><h3>Создать пакет</h3></div>
        <div class="box-body">
            <form method="POST" action="{{ route('packages.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="name">Название пакета <span style="color:#f85149;">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                        class="form-input" placeholder="например, nginx-config">
                    <p class="form-hint">Только строчные буквы, цифры и дефисы.</p>
                </div>
                <div class="form-group">
                    <label class="form-label" for="description">Описание</label>
                    <textarea id="description" name="description" rows="3" class="form-textarea" placeholder="Краткое описание содержимого пакета.">{{ old('description') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="channel_id">Канал распространения <span style="color:#f85149;">*</span></label>
                    @if($channels->isEmpty())
                    <div style="padding:12px; background:rgba(210,153,34,.1); border:1px solid rgba(210,153,34,.3); border-radius:6px; color:#d29922; font-size:13px;">
                        Каналы не найдены. Администратор должен создать хотя бы один канал.
                    </div>
                    @else
                    <select id="channel_id" name="channel_id" required class="form-select">
                        <option value="">Выберите канал…</option>
                        @foreach($channels as $channel)
                        <option value="{{ $channel->id }}" {{ old('channel_id') == $channel->id ? 'selected' : '' }}>{{ $channel->name }}{{ $channel->description ? ' — '.$channel->description : '' }}</option>
                        @endforeach
                    </select>
                    @endif
                </div>
                <div style="display:flex; gap:8px; padding-top:8px; border-top:1px solid #21262d; margin-top:8px;">
                    <button type="submit" class="btn btn-primary">Создать пакет</button>
                    <a href="{{ route('packages.index') }}" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
