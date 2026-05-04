<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Pelanggan;
use App\Models\Barang;
use App\Models\DetailTransaksi;
use App\Models\DraftTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    // =====================================================================
    // HALAMAN FORM TRANSAKSI BARU
    // =====================================================================
    public function index()
    {
        return $this->create();
    }
 
    public function create()
    {
        // Barang yang tersedia untuk disewa (untuk katalog POS)
        $barangs    = Barang::where('status_barang', 'Tersedia')->get();
        $pelanggans = Pelanggan::all()->map(fn($p) => [
            'id'    => $p->id_pelanggan,
            'nama'  => $p->nama_pelanggan,
            'telp'  => $p->no_telp,
            'alamat'=> $p->alamat ?? '',
        ])->values();
 
        // Transaksi yang sedang berjalan (untuk tab Pengembalian)
        $transaksiAktif = \App\Models\Transaksi::with(['pelanggan', 'detailTransaksis.barang'])
            ->where('status_transaksi', 'Diproses')
            ->orderBy('tgl_jatuh_tempo', 'asc')
            ->get();
 
        // Baca tarif denda dari file konfigurasi
        $tarifFile    = storage_path('app/tarif.json');
        $dendaPerHari = 50000; // default fallback
        if (file_exists($tarifFile)) {
            $tarif = json_decode(file_get_contents($tarifFile), true);
            $dendaPerHari = $tarif['denda'] ?? 50000;
        }
 
        $selectedPelanggan = null;
        if (request()->has('pelanggan')) {
            $selectedPelanggan = Pelanggan::find(request()->get('pelanggan'));
        }
 
        $selectedBarang = null;
        if (request()->has('barang')) {
            $selectedBarang = Barang::find(request()->get('barang'));
        }
 
        return view('transaksi.index', compact(
            'barangs',
            'pelanggans',
            'selectedPelanggan',
            'selectedBarang',
            'transaksiAktif',
            'dendaPerHari'
        ));
    }

    public function storePos(Request $request)
    {
        $request->validate([
            'id_pelanggan'    => 'required|exists:pelanggan,id_pelanggan',
            'id_barang'       => 'required|exists:barang,id_barang',
            'tgl_sewa'        => 'required|date',
            'tgl_jatuh_tempo' => 'required|date|after:tgl_sewa',
            'metode_bayar'    => 'required|in:Lunas,DP',
            'items'           => 'required|string',
        ]);
 
        DB::beginTransaction();
        try {
            $pelanggan = Pelanggan::findOrFail($request->id_pelanggan);
            $barang    = Barang::findOrFail($request->id_barang);
 
            $tglSewa    = Carbon::parse($request->tgl_sewa)->startOfDay();
            $tglKembali = Carbon::parse($request->tgl_jatuh_tempo)->startOfDay();
            $durasi     = max(1, $tglSewa->diffInDays($tglKembali));
 
            $items = json_decode($request->items, true) ?? [];
 
            // Hitung total biaya dari items
            $totalBiaya = 0;
            if (!empty($items)) {
                foreach ($items as $item) {
                    $totalBiaya += ($item['harga'] ?? $barang->harga_sewa) * ($item['jumlah'] ?? 1) * $durasi;
                }
            } else {
                $totalBiaya = $barang->harga_sewa * $durasi;
            }
 
            // Terapkan diskon & ongkir jika ada
            $diskon     = (int) ($request->diskon ?? 0);
            $ongkir     = (int) ($request->ongkir ?? 0);
            $totalBiaya = max(0, $totalBiaya - $diskon + $ongkir);
 
            // Hitung DP / Lunas
            $metodeBayar = $request->metode_bayar;
            $jumlahDp    = 0;
            $sisaTagihan = 0;
 
            if ($metodeBayar === 'DP') {
                $jumlahDp    = (int) ($request->jumlah_dp ?? round($totalBiaya * 0.5));
                $sisaTagihan = $totalBiaya - $jumlahDp;
            } else {
                $jumlahDp    = $totalBiaya;
                $sisaTagihan = 0;
            }
 
            // Buat transaksi utama
            $transaksi = Transaksi::create([
                'id_pelanggan'     => $pelanggan->id_pelanggan,
                'id_user'          => session('user')['id_user'],
                'tgl_sewa'         => $request->tgl_sewa,
                'tgl_jatuh_tempo'  => $request->tgl_jatuh_tempo,
                'total_biaya'      => $totalBiaya,
                'total_denda'      => 0,
                'status_transaksi' => 'Diproses',
                'metode_bayar'     => $metodeBayar,
                'jumlah_dp'        => $jumlahDp,
                'sisa_tagihan'     => $sisaTagihan,
            ]);
 
            // Simpan detail & kurangi stok per ukuran
            if (!empty($items)) {
                foreach ($items as $item) {
                    $ukuran    = $item['size']   ?? null;
                    $kuantitas = $item['jumlah'] ?? 1;
                    $harga     = $item['harga']  ?? $barang->harga_sewa;
 
                    DetailTransaksi::create([
                        'id_transaksi' => $transaksi->id_transaksi,
                        'id_barang'    => $barang->id_barang,
                        'ukuran'       => $ukuran,
                        'kuantitas'    => $kuantitas,
                        'sub_total'    => $harga * $kuantitas * $durasi,
                    ]);
 
                    if ($ukuran) {
                        $berhasil = $barang->kurangiStok($ukuran, $kuantitas);
                        if (!$berhasil) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => "Stok ukuran {$ukuran} tidak mencukupi. " .
                                             "Tersedia: " . ($barang->getStokPerUkuranAttribute()[$ukuran] ?? 0) .
                                             " pcs, diminta: {$kuantitas} pcs.",
                            ], 422);
                        }
                    }
                }
            } else {
                // Fallback tanpa ukuran
                DetailTransaksi::create([
                    'id_transaksi' => $transaksi->id_transaksi,
                    'id_barang'    => $barang->id_barang,
                    'ukuran'       => null,
                    'kuantitas'    => 1,
                    'sub_total'    => $totalBiaya,
                ]);
                $barang->update(['status_barang' => 'Disewa']);
            }
 
            DB::commit();
 
            // ── Susun payload resi untuk response JSON ──
            $invoiceNo = 'TRX-' . strtoupper(substr(md5($transaksi->id_transaksi . time()), 0, 8));
 
            // Format items untuk resi
            $resiItems = collect($items)->map(fn($it) => [
                'nama' => $barang->nama_barang,
                'size' => $it['size']   ?? '-',
                'qty'  => $it['jumlah'] ?? 1,
            ])->values()->toArray();
 
            if (empty($resiItems)) {
                $resiItems = [['nama' => $barang->nama_barang, 'size' => '-', 'qty' => 1]];
            }
 
            return response()->json([
                'success'     => true,
                'invoice_no'  => $invoiceNo,
                'tgl_created' => now()->locale('id')->isoFormat('DD MMM YYYY'),
                'tgl_sewa'    => Carbon::parse($request->tgl_sewa)->format('d/m/Y'),
                'tgl_jatuh'   => Carbon::parse($request->tgl_jatuh_tempo)->format('d/m/Y'),
                'printed_at'  => now()->format('j/n/Y, H.i.s'),
                'pelanggan'   => [
                    'nama'   => $pelanggan->nama_pelanggan,
                    'telp'   => $pelanggan->no_telp,
                    'alamat' => $pelanggan->alamat ?? 'Makassar',
                ],
                'items'        => $resiItems,
                'total_biaya'  => $totalBiaya,
                'jumlah_dp'    => $jumlahDp,
                'sisa_tagihan' => $sisaTagihan,
                'metode_bayar' => $metodeBayar,
                'transaksi_id' => $transaksi->id_transaksi,
            ]);
 
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =====================================================================
    // SIMPAN TRANSAKSI BARU (SUPPORT DP & LUNAS)
    // =====================================================================
    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan'  => 'required',
            'no_telp'         => 'required',
            'id_barang'       => 'required|exists:barang,id_barang',
            'tgl_sewa'        => 'required|date',
            'tgl_jatuh_tempo' => 'required|date|after:tgl_sewa',
            'metode_bayar'    => 'required|in:Lunas,DP',
        ]);

        DB::beginTransaction();
        try {
            // Cari atau buat pelanggan berdasarkan no_telp
            $pelanggan = Pelanggan::firstOrCreate(
                ['no_telp' => $request->no_telp],
                [
                    'nama_pelanggan' => $request->nama_pelanggan,
                    'alamat'         => $request->alamat,
                ]
            );

            $barang = Barang::findOrFail($request->id_barang);

            // Hitung durasi dalam hari (minimal 1 hari)
            $tglSewa    = Carbon::parse($request->tgl_sewa)->startOfDay();
            $tglKembali = Carbon::parse($request->tgl_jatuh_tempo)->startOfDay();
            $durasi     = max(1, $tglSewa->diffInDays($tglKembali));

            // Decode items dari form (array ukuran + jumlah yang dipilih user)
            $items = json_decode($request->items, true) ?? [];

            // ------------------------------------------------------------------
            // PERBAIKAN: Hitung total biaya dari items yang sesungguhnya,
            // bukan hanya dari harga barang dikalikan durasi secara flat.
            // Ini memastikan kalau ada multi-ukuran, sub_total per item akurat.
            // ------------------------------------------------------------------
            $totalBiaya = 0;
            if (!empty($items)) {
                foreach ($items as $item) {
                    $totalBiaya += ($item['harga'] ?? $barang->harga_sewa) * ($item['jumlah'] ?? 1) * $durasi;
                }
            } else {
                // Fallback jika tidak ada items (transaksi sederhana tanpa pilih ukuran)
                $totalBiaya = $barang->harga_sewa * $durasi;
            }

            // Hitung DP dan sisa tagihan
            $metodeBayar = $request->metode_bayar;
            $jumlahDp    = 0;
            $sisaTagihan = 0;

            if ($metodeBayar === 'DP') {
                $jumlahDp    = $request->jumlah_dp ?? ($totalBiaya * 0.5);
                $sisaTagihan = $totalBiaya - $jumlahDp;
            } else {
                $jumlahDp    = $totalBiaya;
                $sisaTagihan = 0;
            }

            // Buat record transaksi utama
            $transaksi = Transaksi::create([
                'id_pelanggan'     => $pelanggan->id_pelanggan,
                'id_user'          => session('user')['id_user'],
                'tgl_sewa'         => $request->tgl_sewa,
                'tgl_jatuh_tempo'  => $request->tgl_jatuh_tempo,
                'total_biaya'      => $totalBiaya,
                'total_denda'      => 0,
                'status_transaksi' => 'Diproses',
                'metode_bayar'     => $metodeBayar,
                'jumlah_dp'        => $jumlahDp,
                'sisa_tagihan'     => $sisaTagihan,
            ]);

            // ------------------------------------------------------------------
            // FIX 2 — SIMPAN DETAIL DAN KURANGI STOK PER UKURAN
            //
            // Sebelumnya: loop detail hanya membuat record tapi stok tidak
            // dikurangi di sini. Pengurangan stok justru dilakukan dengan
            // hardcode $barang->update(['status_barang' => 'Disewa']) di bawah
            // loop, yang menyebabkan seluruh barang terkunci padahal stok
            // ukuran lain masih ada.
            //
            // Sekarang: setiap item memanggil kurangiStok($ukuran, $jumlah).
            // Method itu (dari Barang.php yang sudah diperbaiki) akan mengurangi
            // stok di JSON, lalu memanggil syncStatusFromStok() yang secara
            // cerdas memutuskan apakah status perlu berubah berdasarkan TOTAL
            // sisa stok semua ukuran — bukan hanya ukuran yang baru disewa.
            // ------------------------------------------------------------------
            if (!empty($items)) {
                foreach ($items as $item) {
                    $ukuran   = $item['size']   ?? null;
                    $kuantitas = $item['jumlah'] ?? 1;
                    $harga    = $item['harga']   ?? $barang->harga_sewa;

                    // Buat record detail transaksi untuk ukuran ini
                    DetailTransaksi::create([
                        'id_transaksi' => $transaksi->id_transaksi,
                        'id_barang'    => $barang->id_barang, // pakai dari model, bukan request langsung
                        'ukuran'       => $ukuran,
                        'kuantitas'    => $kuantitas,
                        'sub_total'    => $harga * $kuantitas * $durasi,
                    ]);

                    // Kurangi stok dan biarkan syncStatusFromStok() memutuskan
                    // apakah status_barang perlu diubah. Jika masih ada ukuran
                    // lain yang tersedia, status tetap 'Tersedia'.
                    if ($ukuran) {
                        $berhasil = $barang->kurangiStok($ukuran, $kuantitas);

                        // Jika stok tidak cukup, batalkan seluruh transaksi
                        if (!$berhasil) {
                            DB::rollBack();
                            return back()->withErrors([
                                'message' => "Stok ukuran {$ukuran} tidak mencukupi. Tersedia: " .
                                             ($barang->getStokPerUkuranAttribute()[$ukuran] ?? 0) .
                                             " pcs, diminta: {$kuantitas} pcs."
                            ]);
                        }
                    }
                }
            } else {
                // Fallback: transaksi tanpa memilih ukuran spesifik
                DetailTransaksi::create([
                    'id_transaksi' => $transaksi->id_transaksi,
                    'id_barang'    => $barang->id_barang,
                    'ukuran'       => null,
                    'kuantitas'    => 1,
                    'sub_total'    => $totalBiaya,
                ]);

                // Untuk barang tanpa sistem ukuran, langsung set Disewa
                $barang->update(['status_barang' => 'Disewa']);
            }

            // ------------------------------------------------------------------
            // DIHAPUS: Baris "$barang->update(['status_barang' => 'Disewa'])"
            // yang dulu ada di sini adalah penyebab utama bug. Status sekarang
            // dikelola sepenuhnya oleh kurangiStok() → syncStatusFromStok().
            // ------------------------------------------------------------------

            // Hapus draft jika ada yang dimuat
            if ($request->has('draft_id') && $request->draft_id) {
                DraftTransaksi::where('id_draft', $request->draft_id)->delete();
            }

            DB::commit();

            return redirect()
                ->route('transaksi.show', $transaksi->id_transaksi)
                ->with('success', 'Transaksi berhasil dibuat. Total: Rp ' . number_format($totalBiaya, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()]);
        }
    }

    // =====================================================================
    // HALAMAN DETAIL TRANSAKSI + FORM PENGEMBALIAN
    // =====================================================================
    public function show($id)
    {
        $transaksi = Transaksi::with(['pelanggan', 'detailTransaksis.barang'])->findOrFail($id);

        $tarif     = $this->getTarif();
        $dendaInfo = $this->hitungDenda($transaksi, $tarif);

        return view('transaksi.show', compact('transaksi', 'tarif', 'dendaInfo'));
    }

    // =====================================================================
    // PROSES PENGEMBALIAN BARANG
    // =====================================================================
    public function update(Request $request, $id)
    {
        $transaksi = Transaksi::with(['pelanggan', 'detailTransaksis.barang'])->findOrFail($id);

        if ($transaksi->status_transaksi === 'Selesai') {
            return back()->withErrors(['message' => 'Transaksi ini sudah selesai.']);
        }

        DB::beginTransaction();
        try {
            $tarif     = $this->getTarif();
            $dendaInfo = $this->hitungDenda($transaksi, $tarif);

            $tglKembali        = Carbon::now();
            $totalDenda        = $dendaInfo['total_denda'];
            $sisaTagihan       = $transaksi->sisa_tagihan ?? 0;
            $totalBayarKembali = $sisaTagihan + $totalDenda;

            $transaksi->update([
                'tgl_kembali'      => $tglKembali,
                'total_denda'      => $totalDenda,
                'status_transaksi' => 'Selesai',
                'sisa_tagihan'     => 0,
            ]);

            // ------------------------------------------------------------------
            // FIX 3 — KEMBALIKAN STOK UNTUK SEMUA ITEM DI TRANSAKSI INI
            //
            // Sebelumnya: hanya mengambil detail pertama ($detailTransaksis->first())
            // lalu langsung set status 'Tersedia' secara hardcode. Ini salah karena:
            //   1. Kalau ada banyak item, hanya item pertama yang stoknya kembali.
            //   2. Hardcode 'Tersedia' tidak memperhitungkan transaksi lain yang
            //      mungkin masih menyewa ukuran yang berbeda dari barang yang sama.
            //
            // Sekarang: kita loop SEMUA detail, panggil kembalikanStok() per item.
            // Method itu menambah stok di JSON dan memanggil syncStatusFromStok()
            // — status baru benar-benar 'Tersedia' hanya jika total stok > 0.
            // ------------------------------------------------------------------
            foreach ($transaksi->detailTransaksis as $detail) {
                $barang = $detail->barang;

                // Guard: lewati jika relasi barang tidak ditemukan (data rusak)
                if (!$barang) {
                    continue;
                }

                if ($detail->ukuran) {
                    // Barang dengan sistem ukuran → kembalikan via method yang cerdas
                    $barang->kembalikanStok($detail->ukuran, $detail->kuantitas);
                } else {
                    // Barang tanpa ukuran (fallback) → langsung set Tersedia
                    $barang->update(['status_barang' => 'Tersedia']);
                }
            }

            DB::commit();

            $pesan = 'Barang berhasil dikembalikan.';
            if ($totalDenda > 0) {
                $pesan .= ' Denda: Rp ' . number_format($totalDenda, 0, ',', '.');
            }
            if ($sisaTagihan > 0) {
                $pesan .= ' Sisa DP dilunasi: Rp ' . number_format($sisaTagihan, 0, ',', '.');
            }

            $this->sendWhatsAppNotification($transaksi->fresh()->load('pelanggan'), $totalDenda, $sisaTagihan);

            if ($request->has('wantsJson') || $request->wantsJson() || $request->ajax()) {
                $resiItems = $transaksi->detailTransaksis->map(function ($dt) {
                    return [
                        'nama' => $dt->barang->nama_barang ?? '-',
                        'size' => $dt->ukuran ?? '-',
                        'qty'  => $dt->kuantitas ?? 1,
                    ];
                })->toArray();

                $invoiceNo = 'TRX-' . strtoupper(substr(md5($transaksi->id_transaksi . time()), 0, 8));

                return response()->json([
                    'success'     => true,
                    'invoice_no'  => $invoiceNo,
                    'tgl_created' => $transaksi->created_at->locale('id')->isoFormat('DD MMM YYYY'),
                    'tgl_sewa'    => Carbon::parse($transaksi->tgl_sewa)->format('d/m/Y'),
                    'tgl_jatuh'   => Carbon::parse($transaksi->tgl_jatuh_tempo)->format('d/m/Y'),
                    'printed_at'  => now()->format('j/n/Y, H.i.s'),
                    'pelanggan'   => [
                        'nama'   => $transaksi->pelanggan->nama_pelanggan ?? '-',
                        'telp'   => $transaksi->pelanggan->no_telp ?? '-',
                        'alamat' => $transaksi->pelanggan->alamat ?? 'Makassar',
                    ],
                    'items'        => $resiItems,
                    'total_biaya'  => $transaksi->total_biaya,
                    'jumlah_dp'    => $transaksi->jumlah_dp,
                    'sisa_tagihan' => 0, // sisa_tagihan sekarang bernilai 0 karena telah dilunaskan
                    'metode_bayar' => $transaksi->metode_bayar,
                    'transaksi_id' => $transaksi->id_transaksi,
                    
                    'is_pengembalian'     => true,
                    'total_denda'         => $totalDenda,
                    'total_bayar_kembali' => $totalBayarKembali,
                ]);
            }

            return redirect()
                ->route('transaksi.show', $transaksi->id_transaksi)
                ->with('success', $pesan);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['message' => 'Gagal memproses pengembalian: ' . $e->getMessage()]);
        }
    }

    // =====================================================================
    // CETAK PDF — E-NOTA SEWA
    // =====================================================================
    public function printPdf($id)
    {
        $transaksi = Transaksi::with(['pelanggan', 'detailTransaksis.barang'])->findOrFail($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('transaksi.pdf_sewa', [
            'transaksi' => $transaksi,
            'title'     => 'E-NOTA PENYEWAAN',
        ]);
        $pdf->setPaper('a6', 'portrait');
        return $pdf->stream('e-nota-sewa-' . $transaksi->id_transaksi . '.pdf');
    }

    // =====================================================================
    // CETAK PDF — E-NOTA PENGEMBALIAN
    // =====================================================================
    public function printReturnPdf($id)
    {
        $transaksi = Transaksi::with(['pelanggan', 'detailTransaksis.barang'])->findOrFail($id);

        if ($transaksi->status_transaksi !== 'Selesai') {
            abort(403, 'Pengembalian belum diproses.');
        }

        $tarif     = $this->getTarif();
        $dendaInfo = $this->hitungDenda($transaksi, $tarif);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('transaksi.pdf_kembali', [
            'transaksi' => $transaksi,
            'dendaInfo' => $dendaInfo,
            'title'     => 'E-NOTA PENGEMBALIAN',
        ]);
        $pdf->setPaper('a6', 'portrait');
        return $pdf->stream('e-nota-pengembalian-' . $transaksi->id_transaksi . '.pdf');
    }

    // =====================================================================
    // PREVIEW PDF SEBELUM SIMPAN
    // =====================================================================
    public function previewPdf(Request $request)
    {
        $barang = Barang::find($request->id_barang);

        $tglSewa  = Carbon::parse($request->tgl_sewa);
        $tglJatuh = Carbon::parse($request->tgl_jatuh_tempo);
        $durasi   = max(1, $tglSewa->diffInDays($tglJatuh));

        $items       = json_decode($request->items ?? '[]', true);
        $totalBiaya  = 0;
        $detailItems = [];

        foreach ($items as $item) {
            $subtotal    = ($item['harga'] ?? 0) * ($item['jumlah'] ?? 1) * $durasi;
            $totalBiaya += $subtotal;
            $detailItems[] = [
                'nama'     => $barang->nama_barang ?? '-',
                'ukuran'   => $item['size'] ?? '-',
                'jumlah'   => $item['jumlah'] ?? 1,
                'harga'    => $item['harga'] ?? 0,
                'durasi'   => $durasi,
                'subtotal' => $subtotal,
            ];
        }

        $metodeBayar = $request->metode_bayar ?? 'Lunas';
        $jumlahDp    = $request->jumlah_dp ?? ($metodeBayar === 'DP' ? $totalBiaya * 0.5 : $totalBiaya);
        $sisaTagihan = $metodeBayar === 'DP' ? ($totalBiaya - $jumlahDp) : 0;

        $data = [
            'title'        => 'E-NOTA PENYEWAAN (PREVIEW)',
            'isPreview'    => true,
            'pelanggan'    => (object) [
                'nama_pelanggan' => $request->nama_pelanggan,
                'no_telp'        => $request->no_telp,
                'alamat'         => $request->alamat,
            ],
            'barang'       => $barang,
            'tgl_sewa'     => $tglSewa,
            'tgl_jatuh'    => $tglJatuh,
            'durasi'       => $durasi,
            'total_biaya'  => $totalBiaya,
            'metode_bayar' => $metodeBayar,
            'jumlah_dp'    => $jumlahDp,
            'sisa_tagihan' => $sisaTagihan,
            'detailItems'  => $detailItems,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('transaksi.pdf_sewa_preview', $data);
        $pdf->setPaper('a6', 'portrait');
        return $pdf->download('e-nota-preview.pdf');
    }

    // =====================================================================
    // SIMPAN DRAFT TRANSAKSI
    // =====================================================================
    public function saveDraft(Request $request)
    {
        $request->validate([
            'id_barang'      => 'required|exists:barang,id_barang',
            'nama_pelanggan' => 'required',
            'no_telp'        => 'required',
        ]);

        try {
            $barang = Barang::findOrFail($request->id_barang);

            $tglSewa  = $request->tgl_sewa  ? Carbon::parse($request->tgl_sewa)  : null;
            $tglJatuh = $request->tgl_jatuh ? Carbon::parse($request->tgl_jatuh) : null;
            $durasi   = ($tglSewa && $tglJatuh) ? max(1, $tglSewa->diffInDays($tglJatuh)) : 0;

            $items      = json_decode($request->items ?? '[]', true);
            $totalBiaya = 0;
            foreach ($items as $item) {
                $totalBiaya += ($item['harga'] ?? $barang->harga_sewa) * ($item['jumlah'] ?? 1) * $durasi;
            }

            $metodeBayar = $request->metode_bayar ?? 'Lunas';
            $jumlahDp    = $metodeBayar === 'DP' ? ($request->jumlah_dp ?? $totalBiaya * 0.5) : $totalBiaya;

            $draft = DraftTransaksi::create([
                'id_user'         => session('user')['id_user'],
                'nama_pelanggan'  => $request->nama_pelanggan,
                'no_telp'         => $request->no_telp,
                'alamat'          => $request->alamat,
                'id_barang'       => $request->id_barang,
                'ukuran_dipilih'  => $request->items,
                'tgl_sewa'        => $tglSewa,
                'tgl_jatuh_tempo' => $tglJatuh,
                'total_biaya'     => $totalBiaya,
                'metode_bayar'    => $metodeBayar,
                'jumlah_dp'       => $jumlahDp,
                'catatan'         => $request->catatan,
            ]);

            return response()->json([
                'success'  => true,
                'message'  => 'Draft berhasil disimpan.',
                'draft_id' => $draft->id_draft,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =====================================================================
    // AMBIL SEMUA DRAFT MILIK USER
    // =====================================================================
    public function getDrafts()
    {
        $drafts = DraftTransaksi::with('barang')
            ->where('id_user', session('user')['id_user'])
            ->latest()
            ->get()
            ->map(function ($d) {
                return [
                    'id_draft'       => $d->id_draft,
                    'nama_pelanggan' => $d->nama_pelanggan,
                    'no_telp'        => $d->no_telp,
                    'barang'         => $d->barang->nama_barang ?? '-',
                    'total_biaya'    => $d->total_biaya,
                    'metode_bayar'   => $d->metode_bayar,
                    'tgl_sewa'       => $d->tgl_sewa ? $d->tgl_sewa->format('d/m/Y H:i') : '-',
                    'tgl_jatuh'      => $d->tgl_jatuh_tempo ? $d->tgl_jatuh_tempo->format('d/m/Y H:i') : '-',
                    'id_barang'      => $d->id_barang,
                    'items'          => $d->ukuran_dipilih,
                    'alamat'         => $d->alamat,
                    'created_at'     => $d->created_at->format('d/m/Y H:i'),
                    'catatan'        => $d->catatan,
                ];
            });

        return response()->json(['success' => true, 'drafts' => $drafts]);
    }

    // =====================================================================
    // HAPUS DRAFT
    // =====================================================================
    public function deleteDraft($id)
    {
        $draft = DraftTransaksi::where('id_draft', $id)
            ->where('id_user', session('user')['id_user'])
            ->firstOrFail();

        $draft->delete();

        return response()->json(['success' => true]);
    }

    // =====================================================================
    // HAPUS TRANSAKSI
    // =====================================================================
    public function destroy($id)
    {
        $transaksi = Transaksi::with(['detailTransaksis.barang'])->findOrFail($id);

        // Kembalikan stok semua item jika transaksi masih berjalan
        if ($transaksi->status_transaksi === 'Diproses') {
            foreach ($transaksi->detailTransaksis as $detail) {
                if ($detail->barang) {
                    if ($detail->ukuran) {
                        $detail->barang->kembalikanStok($detail->ukuran, $detail->kuantitas);
                    } else {
                        $detail->barang->update(['status_barang' => 'Tersedia']);
                    }
                }
            }
        }

        $transaksi->delete();

        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil dihapus.');
    }

    public function edit($id)
    {
        abort(404);
    }

    // =====================================================================
    // HELPER: Baca tarif dari storage/app/tarif.json
    // =====================================================================
    private function getTarif(): array
    {
        $tarifFile = storage_path('app/tarif.json');
        if (file_exists($tarifFile)) {
            return json_decode(file_get_contents($tarifFile), true) ?? [];
        }
        return [
            'tarif_dasar'   => 150000,
            'tarif_fullset' => 650000,
            'jaminan'       => 200000,
            'denda'         => 50000,
        ];
    }

    // =====================================================================
    // HELPER: Hitung denda berdasarkan tarif pengaturan
    // =====================================================================
    private function hitungDenda(Transaksi $transaksi, array $tarif): array
    {
        $now          = Carbon::now()->startOfDay();
        $jatuhTempo   = Carbon::parse($transaksi->tgl_jatuh_tempo)->startOfDay();
        $terlambat    = $now->gt($jatuhTempo);
        $hariTelat    = $terlambat ? $jatuhTempo->diffInDays($now) : 0;
        $dendaPerHari = $tarif['denda'] ?? 50000;
        $totalDenda   = $hariTelat * $dendaPerHari;

        if ($transaksi->tgl_kembali) {
            $tglKembali  = Carbon::parse($transaksi->tgl_kembali)->startOfDay();
            $terlambat   = $tglKembali->gt($jatuhTempo);
            $hariTelat   = $terlambat ? $jatuhTempo->diffInDays($tglKembali) : 0;
            $totalDenda  = $hariTelat * $dendaPerHari;
        }

        return [
            'terlambat'      => $terlambat,
            'hari_telat'     => $hariTelat,
            'denda_per_hari' => $dendaPerHari,
            'total_denda'    => $totalDenda,
        ];
    }

    // =====================================================================
    // HELPER: Kirim notifikasi WhatsApp jika pengaturan aktif
    // =====================================================================
    private function sendWhatsAppNotification(Transaksi $transaksi, float $denda, float $sisa): void
    {
        $settingFile = storage_path('app/wa_setting.json');
        if (!file_exists($settingFile)) return;

        $setting = json_decode(file_get_contents($settingFile), true);
        if (empty($setting['kirim_enota_otomatis'])) return;

        session(['wa_send_trx_id' => $transaksi->id_transaksi]);
    }
}