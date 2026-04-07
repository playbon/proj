<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} — @yield('title')</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0d1117; color: #e6edf3; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Helvetica,Arial,sans-serif; font-size: 14px; line-height: 1.5; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 16px; }
        .auth-logo { margin-bottom: 16px; text-align: center; }
        .auth-logo svg { width: 48px; height: 48px; color: #e6edf3; }
        .auth-box { width: 100%; max-width: 340px; }
        .auth-title { font-size: 24px; font-weight: 300; text-align: center; color: #e6edf3; margin-bottom: 16px; }
        .auth-card { background: #161b22; border: 1px solid #30363d; border-radius: 6px; padding: 20px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 14px; font-weight: 600; color: #e6edf3; margin-bottom: 6px; }
        .form-input { display: block; width: 100%; padding: 5px 12px; font-size: 14px; line-height: 20px; color: #e6edf3; background: #0d1117; border: 1px solid #30363d; border-radius: 6px; outline: none; transition: border-color .15s, box-shadow .15s; }
        .form-input:focus { border-color: #58a6ff; box-shadow: 0 0 0 3px rgba(31,111,235,.15); }
        .form-input::placeholder { color: #6e7681; }
        .btn-primary { display: block; width: 100%; padding: 5px 16px; font-size: 14px; font-weight: 500; line-height: 20px; text-align: center; color: #fff; background: #238636; border: 1px solid rgba(240,246,252,.1); border-radius: 6px; cursor: pointer; transition: background .12s; }
        .btn-primary:hover { background: #2ea043; }
        .auth-footer { margin-top: 16px; padding: 16px; background: #161b22; border: 1px solid #30363d; border-radius: 6px; text-align: center; font-size: 14px; color: #8b949e; }
        .auth-footer a { color: #58a6ff; text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }
        .flash-error { padding: 12px 16px; background: rgba(218,54,51,.15); border: 1px solid rgba(248,81,73,.3); border-radius: 6px; color: #f85149; font-size: 14px; margin-bottom: 16px; }
        .flash-error ul { margin: 4px 0 0 16px; }
        .flash-success { padding: 12px 16px; background: rgba(35,134,54,.15); border: 1px solid rgba(63,185,80,.3); border-radius: 6px; color: #3fb950; font-size: 14px; margin-bottom: 16px; }
        .form-checkbox-row { display: flex; align-items: center; gap: 8px; }
        .form-checkbox { width: 16px; height: 16px; accent-color: #238636; cursor: pointer; }
    </style>
</head>
<body>
    <div class="auth-box">
        <div class="auth-logo">
            <svg viewBox="0 0 16 16" fill="#3fb950" xmlns="http://www.w3.org/2000/svg">
                <path d="m8.878.392 5.25 3.045c.54.314.872.89.872 1.514v6.098a1.75 1.75 0 0 1-.872 1.514l-5.25 3.045a1.75 1.75 0 0 1-1.756 0l-5.25-3.045A1.75 1.75 0 0 1 1 11.049V4.951c0-.624.332-1.2.872-1.514L7.122.392a1.75 1.75 0 0 1 1.756 0ZM7.875 1.69l-4.63 2.685L8 7.133l4.755-2.758-4.63-2.685a.248.248 0 0 0-.25 0ZM2.5 5.677v5.372c0 .096.052.185.132.232l4.868 2.832V8.913Zm6.5 8.436 4.868-2.832a.269.269 0 0 0 .132-.232V5.677L9 8.913Z"/>
            </svg>
        </div>

        <h1 class="auth-title">@yield('auth_title', 'Войти в ConfigVault')</h1>

        @if($errors->any())
        <div class="flash-error">
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        @if(session('success'))
        <div class="flash-success">{{ session('success') }}</div>
        @endif

        <div class="auth-card">
            @yield('content')
        </div>

        @yield('auth_footer')
    </div>
</body>
</html>
