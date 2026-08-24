<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAdminToDashboard
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role === 'admin' && ! $request->is('admin', 'admin/*')) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
