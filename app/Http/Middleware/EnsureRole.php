<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            abort(403, 'Unauthorized.');
        }

        // Creator passes all role checks
        if ($request->user()->role->value === 'creator') {
            return $next($request);
        }

        if (!in_array($request->user()->role->value, $roles)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
