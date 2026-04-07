@extends('layouts.guest')
@section('title', 'Создать аккаунт')
@section('auth_title', 'Создание аккаунта')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf
    <div class="form-group">
        <label class="form-label" for="name">Имя пользователя</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
            class="form-input">
    </div>
    <div class="form-group">
        <label class="form-label" for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"
            class="form-input">
    </div>
    <div class="form-group">
        <label class="form-label" for="password">Пароль</label>
        <input type="password" id="password" name="password" required autocomplete="new-password"
            class="form-input">
        <p style="font-size:12px; color:#8b949e; margin-top:4px;">Минимум 8 символов.</p>
    </div>
    <div class="form-group">
        <label class="form-label" for="password_confirmation">Подтвердите пароль</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
            class="form-input">
    </div>
    <button type="submit" class="btn-primary">Создать аккаунт</button>
</form>
@endsection

@section('auth_footer')
<div class="auth-footer">
    Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a>.
</div>
@endsection
