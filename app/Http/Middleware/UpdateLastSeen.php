<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->isMethod('OPTIONS')) {
            $user = $request->user();
            // Only write if not updated within the last 60 seconds to reduce DB load
            if (!$user->last_seen_at || $user->last_seen_at->lt(now()->subMinute())) {
                DB::table('users')->where('id', $user->id)->update(['last_seen_at' => now()]);
                $user->last_seen_at = now();
            }
        }

        return $next($request);
    }
}
