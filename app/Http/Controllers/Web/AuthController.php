<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $login = $request->input('email');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if (Auth::attempt([$field => $login, 'password' => $request->password], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Неверный email/имя или пароль.'])->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => UserRole::Ghost,
        ]);

        // Assign to a random online admin or fall back to creator
        $assignee = User::where('role', UserRole::Admin)
            ->where('last_seen_at', '>=', now()->subMinutes(2))
            ->inRandomOrder()
            ->first()
            ?? User::where('role', UserRole::Creator)
                ->where('last_seen_at', '>=', now()->subMinutes(2))
                ->first();

        VerificationRequest::create([
            'user_id'     => $user->id,
            'assigned_to' => $assignee?->id,
            'status'      => 'pending',
        ]);

        Auth::login($user);

        return redirect()->route('ghost.pending')
            ->with('success', 'Добро пожаловать! Ваш аккаунт ожидает проверки администратором.');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
