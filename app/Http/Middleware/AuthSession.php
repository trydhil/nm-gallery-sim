<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthSession
{
    /**
     * Cek apakah user sudah login via session manual.
     * Ini menggantikan auth.session middleware Laravel default
     * karena project ini menggunakan session-based auth sendiri.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('user')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}
