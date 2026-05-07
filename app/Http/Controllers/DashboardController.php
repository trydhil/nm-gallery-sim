<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    /**
     * Halaman Dashboard dihapus dari aplikasi.
     * Controller ini dipertahankan agar rute /dashboard tidak 404,
     * namun langsung mengalihkan ke halaman utama sesuai role.
     */
    public function index()
    {
        $role = session('user')['role'] ?? '';

        if ($role === 'Karyawan') {
            return redirect()->route('transaksi.index');
        }

        // Owner → Laporan Keuangan sebagai halaman utama
        return redirect()->route('laporan');
    }
}