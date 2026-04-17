<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotGhost
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isGhost()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Аккаунт ожидает верификации.'], 403);
            }

            return redirect()->route('ghost.pending')
                ->with('error', 'Ваш аккаунт ожидает проверки. Эта функция недоступна.');
        }

        return $next($request);
    }
}
