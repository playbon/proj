@extends('layouts.app')
@section('title', 'Изменить ' . $package->name)
@section('breadcrumb', 'Пакеты')
@section('breadcrumb_current', $package->name)

@section('header_actions')
<a href="{{ route('packages.show', $package) }}" class="btn btn-secondary">Назад к пакету</a>
@endsection

@section('content')
<div style="max-width:640px;">
    <div class="box">
        <div class="box-header"><h3>Редактировать пакет</h3></div>
        <div class="box-body">
            <form method="POST" action="{{ route('packages.update', $package) }}">
                @csrf @method('PUT')
                <div class="form-group">
                    <label class="form-label" for="name">Название пакета <span style="color:#f85149;">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $package->name) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label" for="description">Описание</label>
                    <textarea id="description" name="description" rows="3" class="form-textarea">{{ old('description', $package->description) }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="channel_id">Канал распространения <span style="color:#f85149;">*</span></label>
                    <select id="channel_id" name="channel_id" required class="form-select">
                        @foreach($channels as $channel)
                        <option value="{{ $channel->id }}" {{ old('channel_id', $package->channel_id) == $channel->id ? 'selected' : '' }}>{{ $channel->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex; gap:8px; padding-top:8px; border-top:1px solid #21262d; margin-top:8px;">
                    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                    <a href="{{ route('packages.show', $package) }}" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
