<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class KetuaMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isKetua()) {
            abort(403, 'Akses ditolak. Hanya Ketua yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}