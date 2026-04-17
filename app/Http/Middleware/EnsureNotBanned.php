<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->banned_until && $user->banned_until->isFuture()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Аккаунт заблокирован до ' . $user->banned_until->format('d.m.Y')], 403);
            }
            return redirect()->route('banned');
        }

        return $next($request);
    }
}
