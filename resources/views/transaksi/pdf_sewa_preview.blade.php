<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>{{ $title }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; font-size: 10px; color: #222; width: 100%; }

/* ── Banner PREVIEW ── */
.preview-banner {
    background: repeating-linear-gradient(
        -45deg,
        rgba(201,168,76,.12),
        rgba(201,168,76,.12) 10px,
        rgba(201,168,76,.04) 10px,
        rgba(201,168,76,.04) 20px
    );
    border: 2px dashed #C9A84C;
    border-radius: 6px;
    text-align: center;
    padding: 8px;
    margin-bottom: 14px;
    font-size: 11px;
    font-weight: bold;
    color: #a07830;
    letter-spacing: 1.5px;
    text-transform: uppercase;
}

.header {
    background: #0a0a0a;
    color: white;
    padding: 16px 18px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}
.brand-name { font-size: 20px; font-style: italic; color: #e0c06e; font-weight: bold; }
.brand-sub  { font-size: 8px; color: rgba(255,255,255,0.35); letter-spacing: 1.5px; text-transform: uppercase; margin-top: 3px; }
.brand-addr { font-size: 8px; color: rgba(255,255,255,0.3); margin-top: 6px; line-height: 1.5; }
.trx-box { text-align: right; }
.trx-label { font-size: 8px; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 1px; }
.trx-num   { font-size: 14px; color: #e0c06e; font-weight: bold; font-family: monospace; margin-top: 2px; }
.trx-date  { font-size: 8px; color: rgba(255,255,255,0.3); margin-top: 3px; }

.doc-type {
    background: repeating-linear-gradient(-45deg, #C9A84C, #C9A84C 8px, #b89640 8px, #b89640 16px);
    color: #0a0a0a;
    text-align: center;
    font-size: 9px;
    font-weight: bold;
    letter-spacing: 2px;
    padding: 5px;
    text-transform: uppercase;
}

.body { padding: 14px 16px; }

.section-title {
    font-size: 8px;
    font-weight: bold;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 6px;
    padding-bottom: 4px;
    border-bottom: 1px solid #eee;
}

.cust-grid { display: flex; gap: 20px; margin-bottom: 12px; }
.cust-item { flex: 1; }
.cust-key  { font-size: 7.5px; text-transform: uppercase; color: #999; font-weight: bold; }
.cust-val  { font-size: 11px; font-weight: 600; color: #0a0a0a; margin-top: 2px; }

table { width: 100%; border-collapse: collapse; margin: 10px 0; }
thead tr { border-bottom: 1.5px solid #C9A84C; }
th { font-size: 8px; text-transform: uppercase; color: #888; font-weight: bold; padding: 5px 4px; text-align: left; }
th.right, td.right { text-align: right; }
td { padding: 7px 4px; font-size: 9.5px; border-bottom: 1px solid #f0f0f0; color: #333; }

.totals-box {
    background: #fafafa;
    border: 1.5px solid #C9A84C;
    border-radius: 8px;
    padding: 10px 12px;
    margin: 10px 0;
}
.tot-row { display: flex; justify-content: space-between; font-size: 10px; padding: 3px 0; }
.tot-key { color: #666; }
.tot-val { font-family: monospace; color: #222; }
.tot-grand { border-top: 1.5px solid #ddd; margin-top: 6px; padding-top: 8px; }
.tot-grand .tot-key { font-size: 12px; font-weight: 800; color: #0a0a0a; }
.tot-grand .tot-val { font-size: 14px; font-weight: 800; color: #C9A84C; font-family: monospace; }

.dp-badge {
    display: inline-block;
    background: rgba(45,166,110,.1);
    color: #1a8050;
    border: 1px solid rgba(45,166,110,.3);
    border-radius: 12px;
    padding: 3px 10px;
    font-size: 9px;
    font-weight: bold;
    margin-bottom: 8px;
}

.footer {
    text-align: center;
    font-size: 8.5px;
    color: #aaa;
    border-top: 1px dashed #ddd;
    padding-top: 10px;
    margin-top: 12px;
    line-height: 1.6;
}
.footer b { color: #C9A84C; }

.sig-area { display: flex; justify-content: space-between; margin-top: 16px; padding-top: 10px; border-top: 1px solid #eee; }
.sig-box  { text-align: center; }
.sig-line { width: 100px; border-top: 1px dashed #aaa; margin: 20px auto 5px; }
.sig-label { font-size: 8px; color: #aaa; font-style: italic; }

/* Watermark PREVIEW di tengah halaman */
.watermark {
    position: fixed;
    top: 45%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 80px;
    font-weight: 900;
    color: rgba(201,168,76,.08);
    letter-spacing: 10px;
    pointer-events: none;
    white-space: nowrap;
    z-index: 0;
}
</style>
</head>
<body>

{{-- Watermark teks besar di latar belakang halaman --}}
<div class="watermark">PREVIEW</div>

{{-- Banner peringatan di bagian paling atas --}}
<div class="preview-banner">
    ⚠️ &nbsp; DOKUMEN PREVIEW — BELUM TERSIMPAN KE SISTEM &nbsp; ⚠️
</div>

<div class="header">
    <div>
        <div class="brand-name">NM Gallery</div>
        <div class="brand-sub">Baju Bodo Authentic Collection</div>
        <div class="brand-addr">Tanete, Kec. Bulukumpa, Kabupaten Bulukumba<br>+62 411-xxx-xxxx · @nmgallery.id</div>
    </div>
    <div class="trx-box">
        <div class="trx-label">No. Transaksi</div>
        {{-- Nomor transaksi belum ada karena transaksi belum disimpan --}}
        <div class="trx-num">#TRX-PREVIEW</div>
        <div class="trx-date">{{ now()->format('d M Y · H:i') }} WITA</div>
    </div>
</div>

<div class="doc-type">📋 PREVIEW — Bukti Penyewaan Baju Bodo</div>

<div class="body">

    @if($metode_bayar === 'DP')
    <div class="dp-badge">⚡ Pembayaran DP — Sisa Dibayar Saat Kembali</div>
    @endif

    <div class="section-title">Data Pelanggan</div>
    <div class="cust-grid">
        <div class="cust-item">
            <div class="cust-key">Nama Pelanggan</div>
            <div class="cust-val">{{ $pelanggan->nama_pelanggan }}</div>
        </div>
        <div class="cust-item">
            <div class="cust-key">No. Telepon</div>
            <div class="cust-val">{{ $pelanggan->no_telp }}</div>
        </div>
    </div>
    @if($pelanggan->alamat)
    <div style="margin-bottom:10px">
        <div class="cust-key">Alamat</div>
        <div style="font-size:10px;color:#555;margin-top:2px">{{ $pelanggan->alamat }}</div>
    </div>
    @endif

    <div class="section-title">Detail Sewa</div>
    <div class="cust-grid" style="margin-bottom:8px">
        <div class="cust-item">
            <div class="cust-key">Tanggal Mulai</div>
            <div class="cust-val">{{ $tgl_sewa->format('d M Y') }}</div>
        </div>
        <div class="cust-item">
            <div class="cust-key">Jatuh Tempo</div>
            <div class="cust-val" style="color:#c0392b">{{ $tgl_jatuh->format('d M Y') }}</div>
        </div>
    </div>

    {{--
        $detailItems adalah array yang dibangun di TransaksiController::previewPdf().
        Setiap elemen berisi: nama, ukuran, jumlah, harga, durasi, subtotal.
    --}}
    <table>
        <thead>
            <tr>
                <th>Item / Barang</th>
                <th>Ukuran</th>
                <th>Qty</th>
                <th class="right">Harga/hr</th>
                <th class="right">Hari</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detailItems as $item)
            <tr>
                <td>{{ $item['nama'] }}</td>
                <td style="text-align:center">{{ $item['ukuran'] ?: '-' }}</td>
                <td style="text-align:center">{{ $item['jumlah'] }}</td>
                <td class="right">Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                <td class="right">×{{ $item['durasi'] }}</td>
                <td class="right" style="font-weight:700;color:#C9A84C">
                    Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;color:#aaa;padding:10px">Tidak ada item</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals-box">
        <div class="tot-row">
            <span class="tot-key">Total Biaya Sewa ({{ $durasi }} hari)</span>
            <span class="tot-val">Rp {{ number_format($total_biaya, 0, ',', '.') }}</span>
        </div>
        @if($metode_bayar === 'Lunas')
        <div class="tot-grand tot-row">
            <span class="tot-key">DIBAYAR LUNAS</span>
            <span class="tot-val">Rp {{ number_format($total_biaya, 0, ',', '.') }}</span>
        </div>
        @else
        <div class="tot-row">
            <span class="tot-key">DP Dibayar Sekarang</span>
            <span class="tot-val" style="color:#1a8050;font-weight:bold">
                Rp {{ number_format($jumlah_dp, 0, ',', '.') }}
            </span>
        </div>
        <div class="tot-row">
            <span class="tot-key">Sisa Dibayar Saat Kembali</span>
            <span class="tot-val" style="color:#c0392b;font-weight:bold">
                Rp {{ number_format($sisa_tagihan, 0, ',', '.') }}
            </span>
        </div>
        <div class="tot-grand tot-row">
            <span class="tot-key">BAYAR SEKARANG</span>
            <span class="tot-val">Rp {{ number_format($jumlah_dp, 0, ',', '.') }}</span>
        </div>
        @endif
    </div>

    <div class="sig-area">
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">Tanda Tangan Pelanggan (Preview)</div>
        </div>
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">Petugas / Kasir (Preview)</div>
        </div>
    </div>

    <div class="footer">
        ⚠️ <strong>Ini adalah dokumen PREVIEW.</strong> Nomor transaksi dan tanda tangan akan terisi setelah transaksi disimpan.<br>
        <b>NM Gallery</b> · Baju Bodo Authentic Collection · Makassar, Sulawesi Selatan
    </div>

</div>
</body>
</html>