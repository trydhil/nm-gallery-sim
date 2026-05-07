<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuestSession
{
    /**
     * Redirect ke halaman utama masing-masing role jika user sudah login.
     * Dashboard dihapus: Owner → /laporan, Karyawan → /transaksi
     */
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('user')) {
            $role = session('user')['role'] ?? 'Owner';

            if ($role === 'Karyawan') {
                return redirect()->route('transaksi.index');
            }

            // Owner langsung ke Laporan Keuangan
            return redirect()->route('laporan');
        }

        return $next($request);
    }
}