<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    /**
     * Tampilkan halaman Pengaturan yang sudah disederhanakan.
     * Konten yang tersisa: Tarif & Ketentuan Denda saja.
     * Manajemen akun & profil toko dihapus dari scope pengaturan ini.
     */
    public function index()
    {
        // Baca tarif dari file JSON storage
        $tarifFile = storage_path('app/tarif.json');

        if (file_exists($tarifFile)) {
            $tarif = json_decode(file_get_contents($tarifFile), true);
        } else {
            // Nilai default jika file belum ada
            $tarif = [
                'tarif_dasar'   => 150000,
                'tarif_fullset' => 650000,
                'jaminan'       => 200000,
                'denda'         => 50000,
            ];
        }

        return view('pengaturan.index', compact('tarif'));
    }

    /**
     * Simpan perubahan tarif ke file JSON.
     * Menggunakan file JSON (bukan DB) agar tidak perlu migrasi tambahan
     * dan bisa dibaca lintas controller (TransaksiController, dll).
     */
    public function updateTarif(Request $request)
    {
        $request->validate([
            'tarif_dasar'   => 'required|numeric|min:0',
            'tarif_fullset' => 'required|numeric|min:0',
            'jaminan'       => 'required|numeric|min:0',
            'denda'         => 'required|numeric|min:0',
        ]);

        $tarif = [
            'tarif_dasar'   => (int) $request->tarif_dasar,
            'tarif_fullset' => (int) $request->tarif_fullset,
            'jaminan'       => (int) $request->jaminan,
            'denda'         => (int) $request->denda,
        ];

        file_put_contents(
            storage_path('app/tarif.json'),
            json_encode($tarif, JSON_PRETTY_PRINT)
        );

        return response()->json(['success' => true, 'tarif' => $tarif]);
    }
}