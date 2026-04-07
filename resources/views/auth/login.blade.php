@extends('layouts.guest')
@section('title', 'Войти')
@section('auth_title', 'Войти в ConfigVault')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="form-group">
        <label class="form-label" for="email">Email или имя пользователя</label>
        <input type="text" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
            class="form-input" placeholder="">
    </div>
    <div class="form-group" style="margin-bottom:8px;">
        <label class="form-label" for="password" style="margin:0 0 6px;">Пароль</label>
        <input type="password" id="password" name="password" required autocomplete="current-password"
            class="form-input" placeholder="">
    </div>
    <div style="margin-bottom:16px;">
        <label class="form-checkbox-row">
            <input type="checkbox" name="remember" id="remember" class="form-checkbox">
            <span style="font-size:14px; color:#e6edf3;">Запомнить меня</span>
        </label>
    </div>
    <button type="submit" class="btn-primary">Войти</button>
</form>
@endsection

@section('auth_footer')
<div class="auth-footer">
    Нет аккаунта? <a href="{{ route('register') }}">Зарегистрироваться</a>.
</div>
@endsection
