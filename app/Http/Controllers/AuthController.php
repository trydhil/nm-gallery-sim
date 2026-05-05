<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Pengguna;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = Pengguna::where('username', $request->username)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            session(['user' => [
                'id_user'      => $user->id_user,
                'nama_lengkap' => $user->nama_lengkap,
                'username'     => $user->username,
                'role'         => $user->role,
                'foto'         => $user->foto,
            ]]);

            // Karyawan → langsung ke POS Transaksi
            if ($user->role === 'Karyawan') {
                return redirect()->route('transaksi.index');
            }

            // Owner → langsung ke Laporan Keuangan (dashboard dihapus)
            return redirect()->route('laporan');
        }

        return back()->withErrors(['message' => 'Username atau password salah!']);
    }

    public function logout()
    {
        session()->forget('user');
        return redirect()->route('login');
    }
}