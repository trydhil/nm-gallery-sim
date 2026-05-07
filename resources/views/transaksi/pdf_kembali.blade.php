<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>E-Nota Pengembalian #TRX-{{ str_pad($transaksi->id_transaksi, 4, '0', STR_PAD_LEFT) }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; font-size: 10px; color: #222; width: 100%; }
.header { background: #0a2e1a; color: white; padding: 16px 18px; display: flex; justify-content: space-between; align-items: flex-start; }
.brand-name { font-size: 20px; font-style: italic; color: #52c896; font-weight: bold; }
.brand-sub  { font-size: 8px; color: rgba(255,255,255,0.35); letter-spacing: 1.5px; text-transform: uppercase; margin-top: 3px; }
.brand-addr { font-size: 8px; color: rgba(255,255,255,0.3); margin-top: 6px; line-height: 1.5; }
.trx-box    { text-align: right; }
.trx-label  { font-size: 8px; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 1px; }
.trx-num    { font-size: 14px; color: #52c896; font-weight: bold; font-family: monospace; margin-top: 2px; }
.trx-date   { font-size: 8px; color: rgba(255,255,255,0.3); margin-top: 3px; }
.doc-type   { background: #2da66e; color: white; text-align: center; font-size: 9px; font-weight: bold; letter-spacing: 2px; padding: 5px; text-transform: uppercase; }
.body { padding: 14px 16px; }
.section-title { font-size: 8px; font-weight: bold; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; padding-bottom: 4px; border-bottom: 1px solid #eee; }
.info-grid { display: flex; gap: 12px; margin-bottom: 10px; flex-wrap: wrap; }
.info-item { flex: 1; min-width: 80px; }
.info-key  { font-size: 7.5px; text-transform: uppercase; color: #999; font-weight: bold; }
.info-val  { font-size: 11px; font-weight: 600; color: #0a0a0a; margin-top: 2px; }
table { width: 100%; border-collapse: collapse; margin: 8px 0; }
thead tr { border-bottom: 1.5px solid #2da66e; }
th { font-size: 8px; text-transform: uppercase; color: #888; font-weight: bold; padding: 5px 4px; text-align: left; }
td { padding: 7px 4px; font-size: 9.5px; border-bottom: 1px solid #f0f0f0; color: #333; }
td.right, th.right { text-align: right; }
.totals-box { background: #f0faf5; border: 1.5px solid #2da66e; border-radius: 8px; padding: 10px 12px; margin: 10px 0; }
.tot-row { display: flex; justify-content: space-between; font-size: 10px; padding: 3px 0; }
.tot-key { color: #666; }
.tot-val { font-family: monospace; color: #222; }
.tot-grand { border-top: 1.5px solid #aaa; margin-top: 6px; padding-top: 8px; }
.tot-grand .tot-key { font-size: 12px; font-weight: 800; color: #0a0a0a; }
.tot-grand .tot-val { font-size: 14px; font-weight: 800; color: #1a8050; font-family: monospace; }
.denda-box { background: rgba(220,80,60,.06); border: 1.5px solid rgba(220,80,60,.25); border-radius: 8px; padding: 10px 12px; margin: 8px 0; }
.denda-title { font-size: 9px; font-weight: bold; color: #c0392b; margin-bottom: 6px; }
.lunas-badge { background: rgba(45,166,110,.1); color: #1a8050; border: 1px solid rgba(45,166,110,.3); border-radius: 12px; padding: 4px 12px; font-size: 9px; font-weight: bold; display: inline-block; margin-bottom: 10px; }
.footer { text-align: center; font-size: 8.5px; color: #aaa; border-top: 1px dashed #ddd; padding-top: 10px; margin-top: 12px; line-height: 1.6; }
.footer b { color: #2da66e; }
.sig-area { display: flex; justify-content: space-between; margin-top: 16px; padding-top: 10px; border-top: 1px solid #eee; }
.sig-box  { text-align: center; }
.sig-line { width: 100px; border-top: 1px solid #999; margin: 20px auto 5px; }
.sig-label{ font-size: 8px; color: #888; }
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="brand-name">NM Gallery</div>
        <div class="brand-sub">Baju Bodo Authentic Collection</div>
        <div class="brand-addr">Tanete, Kec. Bulukumpa, Kabupaten Bulukumba<br>+62 411-xxx-xxxx · @nmgallery.id</div>
    </div>
    <div class="trx-box">
        <div class="trx-label">No. Transaksi</div>
        <div class="trx-num">#TRX-{{ str_pad($transaksi->id_transaksi, 4, '0', STR_PAD_LEFT) }}</div>
        <div class="trx-date">Kembali: {{ \Carbon\Carbon::parse($transaksi->tgl_kembali)->format('d M Y') }} WITA</div>
    </div>
</div>

<div class="doc-type">✅ Bukti Pengembalian & Pelunasan Baju Bodo</div>

<div class="body">

    <div class="lunas-badge">✓ TRANSAKSI SELESAI — LUNAS</div>

    <div class="section-title">Data Pelanggan</div>
    <div class="info-grid">
        <div class="info-item">
            <div class="info-key">Nama</div>
            <div class="info-val">{{ $transaksi->pelanggan->nama_pelanggan ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-key">No. Telepon</div>
            <div class="info-val">{{ $transaksi->pelanggan->no_telp ?? '-' }}</div>
        </div>
    </div>

    <div class="section-title">Detail Pengembalian</div>
    <div class="info-grid">
        <div class="info-item">
            <div class="info-key">Tgl Sewa</div>
            <div class="info-val">{{ \Carbon\Carbon::parse($transaksi->tgl_sewa)->format('d M Y') }}</div>
        </div>
        <div class="info-item">
            <div class="info-key">Jatuh Tempo</div>
            <div class="info-val">{{ \Carbon\Carbon::parse($transaksi->tgl_jatuh_tempo)->format('d M Y') }}</div>
        </div>
        <div class="info-item">
            <div class="info-key">Tgl Dikembalikan</div>
            <div class="info-val" style="color:{{ $dendaInfo['terlambat'] ? '#c0392b' : '#1a8050' }}">
                {{ \Carbon\Carbon::parse($transaksi->tgl_kembali)->format('d M Y') }}
                @if($dendaInfo['terlambat']) (Terlambat {{ $dendaInfo['hari_telat'] }} hari) @endif
            </div>
        </div>
    </div>

    @php
        $tglSewa  = \Carbon\Carbon::parse($transaksi->tgl_sewa);
        $tglJatuh = \Carbon\Carbon::parse($transaksi->tgl_jatuh_tempo);
        $durasi   = max(1, $tglSewa->diffInDays($tglJatuh));
    @endphp

    <table>
        <thead>
            <tr>
                <th>Barang yang Dikembalikan</th>
                <th>Ukuran</th>
                <th class="right">Durasi</th>
                <th class="right">Biaya Sewa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi->detailTransaksis as $det)
            <tr>
                <td>{{ $det->barang->nama_barang ?? '-' }}</td>
                <td style="text-align:center">{{ $det->ukuran ?? '-' }}</td>
                <td class="right">{{ $durasi }} hari</td>
                <td class="right" style="font-weight:700">Rp {{ number_format($transaksi->total_biaya, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($dendaInfo['terlambat'] && $dendaInfo['total_denda'] > 0)
    <div class="denda-box">
        <div class="denda-title">⚠️ Perhitungan Denda Keterlambatan</div>
        <div class="tot-row">
            <span>{{ $dendaInfo['hari_telat'] }} hari telat × Rp {{ number_format($dendaInfo['denda_per_hari'], 0, ',', '.') }}/hari</span>
            <span style="font-weight:bold;color:#c0392b">Rp {{ number_format($dendaInfo['total_denda'], 0, ',', '.') }}</span>
        </div>
    </div>
    @endif

    <div class="totals-box">
        <div class="tot-row">
            <span class="tot-key">Total Biaya Sewa</span>
            <span class="tot-val">Rp {{ number_format($transaksi->total_biaya, 0, ',', '.') }}</span>
        </div>
        @if(($transaksi->metode_bayar ?? 'Lunas') === 'DP')
        <div class="tot-row">
            <span class="tot-key">DP Dibayar di Awal</span>
            <span class="tot-val" style="color:#1a8050">(Rp {{ number_format($transaksi->jumlah_dp ?? 0, 0, ',', '.') }})</span>
        </div>
        @php $sisaDilunasi = ($transaksi->total_biaya) - ($transaksi->jumlah_dp ?? 0); @endphp
        <div class="tot-row">
            <span class="tot-key">Sisa DP Dilunasi</span>
            <span class="tot-val">Rp {{ number_format(max(0, $sisaDilunasi), 0, ',', '.') }}</span>
        </div>
        @endif
        @if($dendaInfo['total_denda'] > 0)
        <div class="tot-row">
            <span class="tot-key" style="color:#c0392b">Denda Keterlambatan</span>
            <span class="tot-val" style="color:#c0392b">Rp {{ number_format($dendaInfo['total_denda'], 0, ',', '.') }}</span>
        </div>
        @endif
        @php
            $sisaLunas = max(0, ($transaksi->total_biaya) - ($transaksi->jumlah_dp ?? $transaksi->total_biaya));
            $totalBayarKembali = $sisaLunas + $dendaInfo['total_denda'];
        @endphp
        <div class="tot-grand tot-row">
            <span class="tot-key">{{ $totalBayarKembali > 0 ? 'TOTAL DIBAYAR SAAT KEMBALI' : 'TIDAK ADA TAGIHAN' }}</span>
            <span class="tot-val">Rp {{ number_format($totalBayarKembali, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="sig-area">
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">Tanda Tangan Pelanggan</div>
        </div>
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">Petugas / Kasir</div>
        </div>
    </div>

    <div class="footer">
        Barang telah dikembalikan dan transaksi dinyatakan <b>SELESAI</b>.<br>
        Terima kasih telah mempercayakan momen Anda kepada <b>NM Gallery</b> ✦ Makassar, Sulawesi Selatan
    </div>

</div>
</body>
</html>