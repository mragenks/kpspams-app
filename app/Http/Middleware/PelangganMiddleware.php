<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PelangganMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isPelanggan()) {
            abort(403, 'Akses ditolak. Hanya Pelanggan yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}