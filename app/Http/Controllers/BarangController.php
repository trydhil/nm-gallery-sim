<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    /**
     * Halaman utama inventaris — sekarang dengan UI split-panel ala POS.
     * Data barang di-pass sebagai JSON ke JavaScript agar catalog
     * bisa di-filter/search secara client-side tanpa round-trip ke server.
     */
    public function index()
    {
        $barang = Barang::with(['detailTransaksis.transaksi'])->get();

        // Statistik ringkasan untuk chips filter
        $barangJson = $barang->map(function ($b) {
            $stok = [];
            if ($b->stok) {
                $decoded = json_decode($b->stok, true);
                if (is_array($decoded)) $stok = $decoded;
            }

            $totalStok = array_sum($stok);
            $activeRental = $b->detailTransaksis->contains(function ($dt) {
                return ($dt->transaksi?->status_transaksi) === 'Diproses';
            });

            return [
                'id'            => $b->id_barang,
                'nama'          => $b->nama_barang,
                'ukuran'        => $b->ukuran ?? '',
                'harga'         => (float) $b->harga_sewa,
                'status'        => $b->status_barang,
                'stok'          => $stok,
                'total_stok'    => $totalStok,
                'available'     => $totalStok > 0 && $b->status_barang === 'Tersedia',
                'active_rental' => $activeRental,
                'foto'          => $b->foto,
            ];
        })->values();

        $totalBarang    = $barangJson->count();
        $barangTersedia = $barangJson->where('available', true)->count();
        $barangDisewa   = $barangJson->where('active_rental', true)->count();
        $barangLaundry  = $barang->where('status_barang', 'Laundry')->count();
        $barangRusak    = $barang->where('status_barang', 'Rusak')->count();

        /*
         * Kita siapkan data JSON untuk JavaScript catalog.
         * Ini menghindari re-request saat user memfilter/mencari —
         * semua data sudah ada di browser, filter bekerja instan.
         */

        return view('barang.index', compact(
            'barang', 'barangJson',
            'totalBarang', 'barangTersedia', 'barangDisewa',
            'barangLaundry', 'barangRusak'
        ));
    }

    /**
     * Simpan barang baru ke inventaris.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_barang' => 'required',
                'harga_sewa'  => 'required|numeric',
                'ukuran'      => 'required',
                'stok'        => 'required',
                'foto'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $file     = $request->file('foto');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $file->move(public_path('uploads/barang'), $filename);
                $fotoPath = 'uploads/barang/' . $filename;
            }

            Barang::create([
                'nama_barang'  => $request->nama_barang,
                'ukuran'       => $request->ukuran,
                'harga_sewa'   => $request->harga_sewa,
                'stok'         => $request->stok, // JSON string dari form
                'status_barang' => 'Tersedia',
                'foto'         => $fotoPath,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update data barang secara menyeluruh (nama, harga, ukuran, foto).
     * Untuk perubahan stok saja, gunakan adjustStok() yang lebih ringan.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'nama_barang' => 'required',
                'harga_sewa'  => 'required|numeric',
                'ukuran'      => 'required',
                'stok'        => 'required',
                'foto'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $barang   = Barang::findOrFail($id);
            $fotoPath = $barang->foto;

            if ($request->hasFile('foto')) {
                if ($barang->foto && file_exists(public_path($barang->foto))) {
                    unlink(public_path($barang->foto));
                }
                $file     = $request->file('foto');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $file->move(public_path('uploads/barang'), $filename);
                $fotoPath = 'uploads/barang/' . $filename;
            }

            if ($request->has('hapus_foto') && $request->hapus_foto == '1') {
                if ($barang->foto && file_exists(public_path($barang->foto))) {
                    unlink(public_path($barang->foto));
                }
                $fotoPath = null;
            }

            $barang->update([
                'nama_barang' => $request->nama_barang,
                'ukuran'      => $request->ukuran,
                'harga_sewa'  => $request->harga_sewa,
                'stok'        => $request->stok,
                'status_barang' => $request->status_barang,
                'foto'        => $fotoPath,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Operasi KHUSUS untuk penyesuaian stok cepat dari panel kanan.
     *
     * Menerima objek stok lengkap (bukan delta/selisih) dari frontend,
     * lalu langsung menimpa nilai stok di database.
     * Juga memperbarui status_barang secara otomatis:
     *   - Jika total stok = 0 → status tetap (mungkin Disewa/Laundry)
     *   - Jika stok > 0 dan status sebelumnya Tersedia → tetap Tersedia
     *   - Status lain (Disewa, Laundry, Rusak) diperbarui sesuai request
     *
     * @param Request $request  { stok: {S:2, M:3}, status_barang: 'Tersedia' }
     * @param int     $id       id_barang
     */
    public function adjustStok(Request $request, $id)
    {
        try {
            $request->validate([
                'stok'          => 'required|string', // JSON
                'status_barang' => 'required|in:Tersedia,Disewa,Laundry,Rusak',
            ]);

            $barang = Barang::findOrFail($id);

            // Validasi bahwa stok adalah JSON valid
            $stokArray = json_decode($request->stok, true);
            if (!is_array($stokArray)) {
                return response()->json(['success' => false, 'message' => 'Format stok tidak valid.'], 422);
            }

            // Pastikan semua nilai stok non-negatif
            foreach ($stokArray as $ukuran => $jumlah) {
                $stokArray[$ukuran] = max(0, (int) $jumlah);
            }

            $barang->update([
                'stok'          => json_encode($stokArray),
                'status_barang' => $request->status_barang,
            ]);

            $totalStok = array_sum($stokArray);

            return response()->json([
                'success'    => true,
                'stok'       => $stokArray,
                'total_stok' => $totalStok,
                'status'     => $request->status_barang,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Hapus barang dari inventaris.
     * Barang yang sedang disewa tidak bisa dihapus.
     */
    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);

        if ($barang->status_barang === 'Disewa') {
            return response()->json([
                'success' => false,
                'message' => 'Barang sedang disewa, tidak bisa dihapus!',
            ]);
        }

        // Cek apakah barang pernah disewa (ada di tabel detail_transaksi)
        $hasTransactions = \App\Models\DetailTransaksi::where('id_barang', $id)->exists();
        if ($hasTransactions) {
            return response()->json([
                'success' => false,
                'message' => 'Barang ini sudah memiliki riwayat transaksi dan tidak bisa dihapus!',
            ]);
        }

        if ($barang->foto && file_exists(public_path($barang->foto))) {
            unlink(public_path($barang->foto));
        }

        $barang->delete();

        return response()->json(['success' => true]);
    }
}