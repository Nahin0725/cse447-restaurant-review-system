<?php

namespace App\Http\Middleware;

use App\Models\SessionTable;
use Closure;
use Illuminate\Http\Request;

class AuthenticateSession
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->cookie('auth_token');

        if (!$token) {
            return redirect('/login');
        }

        $session = SessionTable::findByToken($token);

        if (!$session || $session->isExpired()) {
            if ($session) {
                $session->delete();
            }
            return redirect('/login');
        }

        // Update last activity
        $session->update(['last_activity' => now()]);

        $request->attributes->set('auth_user', $session->user);
        $request->attributes->set('session_id', $session->session_id);

        return $next($request);
    }
}
