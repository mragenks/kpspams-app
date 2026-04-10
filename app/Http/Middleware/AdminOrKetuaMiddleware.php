<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOrKetuaMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (
            !auth()->check() ||
            (!auth()->user()->isAdmin() && !auth()->user()->isKetua())
        ) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
