<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PencatatMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isPencatat()) {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk Pencatat Meter.');
        }

        return $next($request);
    }
}
