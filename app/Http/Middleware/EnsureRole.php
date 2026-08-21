<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if ($request->is('teacher*') && $request->user() && in_array('teacher', $roles, true) && $request->user()->role !== 'teacher') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('teacher.login');
        }

        abort_unless($request->user() && in_array($request->user()->role, $roles, true), 403);

        return $next($request);
    }
}