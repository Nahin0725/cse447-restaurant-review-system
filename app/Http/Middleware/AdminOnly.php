<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->attributes->get('auth_user');

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Administrator access required.');
        }

        return $next($request);
    }
}
