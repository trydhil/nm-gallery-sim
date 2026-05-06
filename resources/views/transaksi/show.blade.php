@extends('layouts.app')

@section('title', 'Detail Transaksi')
@section('breadcrumb', 'Detail Transaksi')

@section('content')
<div class="pg-head">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <div>
            <div class="pg-title">Detail Transaksi #TRX-{{ str_pad($transaksi->id_transaksi, 4, '0', STR_PAD_LEFT) }}</div>
            <div class="pg-sub">
                @if($transaksi->status_transaksi == 'Diproses')
                    <span style="color:#C9A84C;font-weight:700">● Sedang Disewa</span>
                @else
                    <span style="color:#1a8050;font-weight:700">✓ Selesai</span>
                @endif
                · {{ \Carbon\Carbon::parse($transaksi->tgl_sewa)->format('d M Y') }}
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="{{ route('transaksi.create') }}" class="btn-white">← Transaksi Baru</a>
            @if($transaksi->status_transaksi == 'Selesai')
                <a href="{{ route('transaksi.print', $transaksi->id_transaksi) }}" target="_blank" class="btn-outline">🖨 Nota Sewa</a>
                <a href="{{ route('transaksi.print.kembali', $transaksi->id_transaksi) }}" target="_blank" class="btn-gold">🖨 Nota Pengembalian</a>
            @else
                <a href="{{ route('transaksi.print', $transaksi->id_transaksi) }}" target="_blank" class="btn-gold">🖨 Cetak E-Nota Sewa</a>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    swalToast(@json('✅ ' . session('success')), 'success');
});
</script>
@endpush
@endif

{{--
    Kalkulasi "yang dibayar saat pengembalian":
    - Untuk Lunas : seluruh biaya sudah dibayar di awal, saat kembali hanya ada denda (jika terlambat).
    - Untuk DP    : yang dibayar saat kembali = sisa tagihan asli + denda.
                    Sisa tagihan asli = total_biaya - jumlah_dp.
                    Kita tidak bisa membaca sisa_tagihan dari DB lagi karena sudah di-set ke 0
                    saat pengembalian diproses. Jadi kita hitung ulang dari total_biaya & jumlah_dp.
--}}
@php
    $sisaYangDilunasi = max(0, $transaksi->total_biaya - ($transaksi->jumlah_dp ?? $transaksi->total_biaya));
    $totalDibayarSaatKembali = $sisaYangDilunasi + ($transaksi->total_denda ?? 0);
@endphp

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px">

    <!-- ====================================================== -->
    <!-- KIRI: Info Transaksi + Proses Pengembalian -->
    <!-- ====================================================== -->
    <div style="display:flex;flex-direction:column;gap:16px">

        <!-- Info Pelanggan & Barang -->
        <div class="form-card">
            <div class="form-sect" style="background:var(--gold-xs)">
                <div class="form-sect-lbl">👤 Data Pelanggan & Barang</div>
                <div class="fgrid">
                    <div class="field">
                        <label class="flbl">Nama Pelanggan</label>
                        <input type="text" class="finput" readonly value="{{ $transaksi->pelanggan->nama_pelanggan ?? '-' }}">
                    </div>
                    <div class="field">
                        <label class="flbl">No. Telepon</label>
                        <input type="text" class="finput" readonly value="{{ $transaksi->pelanggan->no_telp ?? '-' }}">
                    </div>
                    <div class="field f-full">
                        <label class="flbl">Alamat</label>
                        <input type="text" class="finput" readonly value="{{ $transaksi->pelanggan->alamat ?? '-' }}">
                    </div>
                    <div class="field f-full">
                        <label class="flbl">Baju yang Disewa</label>
                        <input type="text" class="finput" readonly
                            value="{{ $transaksi->detailTransaksis->first()->barang->nama_barang ?? '-' }}
                                   @if($transaksi->detailTransaksis->first() && $transaksi->detailTransaksis->first()->ukuran)
                                   ({{ $transaksi->detailTransaksis->first()->ukuran }})
                                   @endif">
                    </div>
                </div>
            </div>
            <div class="form-sect">
                <div class="form-sect-lbl">📅 Periode Sewa</div>
                <div class="fgrid">
                    <div class="field">
                        <label class="flbl">Tgl Sewa</label>
                        <input type="text" class="finput" readonly value="{{ \Carbon\Carbon::parse($transaksi->tgl_sewa)->format('d/m/Y') }}">
                    </div>
                    <div class="field">
                        <label class="flbl">Jatuh Tempo</label>
                        <input type="text" class="finput" readonly
                            value="{{ \Carbon\Carbon::parse($transaksi->tgl_jatuh_tempo)->format('d/m/Y') }}"
                            style="{{ $dendaInfo['terlambat'] && $transaksi->status_transaksi=='Diproses' ? 'color:#c0392b;font-weight:700' : '' }}">
                    </div>
                    @if($transaksi->tgl_kembali)
                    <div class="field">
                        <label class="flbl">Tgl Dikembalikan</label>
                        <input type="text" class="finput" readonly value="{{ \Carbon\Carbon::parse($transaksi->tgl_kembali)->format('d/m/Y') }}" style="color:#1a8050;font-weight:700">
                    </div>
                    @endif
                    <div class="field">
                        <label class="flbl">Durasi Sewa</label>
                        <input type="text" class="finput" readonly value="{{ \Carbon\Carbon::parse($transaksi->tgl_sewa)->diffInDays($transaksi->tgl_jatuh_tempo) }} hari">
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Pembayaran -->
        <div class="form-card">
            <div class="form-sect">
                <div class="form-sect-lbl">💰 Pembayaran</div>
                <div class="fgrid">
                    <div class="field">
                        <label class="flbl">Metode Bayar</label>
                        <input type="text" class="finput" readonly
                            value="{{ $transaksi->metode_bayar ?? 'Lunas' }}"
                            style="{{ ($transaksi->metode_bayar ?? 'Lunas') == 'DP' ? 'color:#C9A84C;font-weight:700' : '' }}">
                    </div>
                    <div class="field">
                        <label class="flbl">Total Biaya Sewa</label>
                        <input type="text" class="finput" readonly
                            value="Rp {{ number_format($transaksi->total_biaya, 0, ',', '.') }}"
                            style="color:var(--gold-dk);font-weight:700;font-family:monospace">
                    </div>
                    @if(($transaksi->metode_bayar ?? 'Lunas') == 'DP')
                    <div class="field">
                        <label class="flbl">Sudah Dibayar (DP)</label>
                        <input type="text" class="finput" readonly
                            value="Rp {{ number_format($transaksi->jumlah_dp ?? 0, 0, ',', '.') }}"
                            style="color:#1a8050;font-weight:700;font-family:monospace">
                    </div>
                    <div class="field">
                        <label class="flbl">
                            {{ $transaksi->status_transaksi == 'Selesai' ? 'Sisa Tagihan (Dilunasi)' : 'Sisa Tagihan' }}
                        </label>
                        <input type="text" class="finput" readonly
                            value="Rp {{ number_format($sisaYangDilunasi, 0, ',', '.') }}"
                            style="color:#c0392b;font-weight:700;font-family:monospace">
                    </div>
                    @endif
                    @if($transaksi->total_denda > 0)
                    <div class="field">
                        <label class="flbl">Denda Keterlambatan</label>
                        <input type="text" class="finput" readonly
                            value="Rp {{ number_format($transaksi->total_denda, 0, ',', '.') }}"
                            style="color:#c0392b;font-weight:700;font-family:monospace">
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- FORM PROSES PENGEMBALIAN (hanya jika masih Diproses) -->
        <!-- ====================================================== -->
        @if($transaksi->status_transaksi == 'Diproses')
        @php
            $sisaTagihanSaatIni = $transaksi->sisa_tagihan ?? 0;
            $totalDendaKini     = $dendaInfo['total_denda'];
            $totalBayarKembali  = $sisaTagihanSaatIni + $totalDendaKini;
        @endphp
        <div class="form-card" style="border-top:3px solid {{ $dendaInfo['terlambat'] ? '#e74c3c' : 'var(--gold)' }}">
            <div class="form-sect" style="background: {{ $dendaInfo['terlambat'] ? 'rgba(220,80,60,.04)' : 'var(--gold-xs)' }}">
                <div class="form-sect-lbl">↩️ Proses Pengembalian</div>

                @if($dendaInfo['terlambat'])
                <div style="background:rgba(220,80,60,.08);border:1px solid rgba(220,80,60,.2);border-radius:10px;padding:14px 16px;margin-bottom:16px">
                    <div style="font-size:13px;font-weight:700;color:#c0392b;margin-bottom:4px">⚠️ TERLAMBAT {{ $dendaInfo['hari_telat'] }} HARI</div>
                    <div style="font-size:12px;color:#666">
                        Jatuh tempo: {{ \Carbon\Carbon::parse($transaksi->tgl_jatuh_tempo)->format('d M Y') }} ·
                        Denda: Rp {{ number_format($dendaInfo['denda_per_hari'], 0, ',', '.') }}/hari ×
                        {{ $dendaInfo['hari_telat'] }} hari =
                        <strong style="color:#c0392b">Rp {{ number_format($totalDendaKini, 0, ',', '.') }}</strong>
                    </div>
                </div>
                @else
                <div style="background:rgba(45,166,110,.06);border:1px solid rgba(45,166,110,.2);border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:12px;color:#1a8050;font-weight:600">
                    ✅ Pengembalian tepat waktu — tidak ada denda
                </div>
                @endif

                <!-- Ringkasan Total yang Harus Dibayar Saat Kembali -->
                <div style="background:white;border:1.5px solid {{ $dendaInfo['terlambat'] ? 'rgba(220,80,60,.3)' : 'var(--gold-md)' }};border-radius:12px;padding:16px;margin-bottom:16px">
                    <div style="font-size:11px;font-weight:700;color:#888;text-transform:uppercase;margin-bottom:12px;letter-spacing:.8px">Ringkasan Tagihan Pengembalian</div>
                    @if($sisaTagihanSaatIni > 0)
                    <div style="display:flex;justify-content:space-between;font-size:12.5px;padding:6px 0;border-bottom:1px solid #f0f0f0">
                        <span style="color:#666">Sisa DP</span>
                        <span style="font-family:monospace;font-weight:600">Rp {{ number_format($sisaTagihanSaatIni, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($totalDendaKini > 0)
                    <div style="display:flex;justify-content:space-between;font-size:12.5px;padding:6px 0;border-bottom:1px solid #f0f0f0">
                        <span style="color:#c0392b">Denda {{ $dendaInfo['hari_telat'] }} hari</span>
                        <span style="font-family:monospace;font-weight:600;color:#c0392b">Rp {{ number_format($totalDendaKini, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div style="display:flex;justify-content:space-between;padding:10px 0 0;margin-top:4px">
                        <span style="font-size:14px;font-weight:800;color:#0a0a0a">
                            {{ $totalBayarKembali > 0 ? 'TOTAL TAGIHAN' : 'TIDAK ADA TAGIHAN' }}
                        </span>
                        <span style="font-family:monospace;font-size:18px;font-weight:800;color:{{ $totalBayarKembali > 0 ? '#c0392b' : '#1a8050' }}">
                            Rp {{ number_format($totalBayarKembali, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                    <form action="{{ route('transaksi.update', $transaksi->id_transaksi) }}" method="POST"
                        onsubmit="return confirmPengembalian(this, event)">
                    @csrf
                    @method('PUT')
                    <div style="display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap">
                        <a href="{{ route('transaksi.print', $transaksi->id_transaksi) }}" target="_blank" class="btn-outline">
                            🖨 Cetak Nota Sewa
                        </a>
                        <button type="submit" class="btn-gold" style="background:#1a8050;border-color:rgba(45,166,110,.4)">
                            ✅ Konfirmasi Pengembalian & Selesai
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        @if($transaksi->status_transaksi == 'Selesai')
        <div style="background:white;border:1px solid var(--gray-200);border-radius:12px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px">
            <div>
                <div style="font-size:13px;font-weight:700;color:#1a8050">✅ Transaksi Selesai</div>
            @push('scripts')
            <script>
            function confirmPengembalian(form, event) {
                event.preventDefault();
                swalConfirm('Total tagihan: Rp {{ number_format($totalBayarKembali, 0, '.', '.') }}', {
                    title: 'Konfirmasi pengembalian barang',
                    confirmButtonText: 'Lanjutkan',
                    confirmButtonColor: '#1a8050',
                }).then(result => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
                return false;
            }
            </script>
            @endpush

                <div style="font-size:11px;color:#888;margin-top:2px">
                    Dikembalikan: {{ $transaksi->tgl_kembali ? \Carbon\Carbon::parse($transaksi->tgl_kembali)->format('d M Y') : '-' }}
                    @if($transaksi->total_denda > 0)
                        · Denda: Rp {{ number_format($transaksi->total_denda, 0, ',', '.') }}
                    @endif
                </div>
            </div>
            <div style="display:flex;gap:8px">
                <a href="{{ route('transaksi.print', $transaksi->id_transaksi) }}" target="_blank" class="btn-outline">
                    🧾 Nota Sewa
                </a>
                <a href="{{ route('transaksi.print.kembali', $transaksi->id_transaksi) }}" target="_blank" class="btn-gold">
                    📋 Nota Pengembalian
                </a>
            </div>
        </div>
        @endif

    </div>

    <!-- ====================================================== -->
    <!-- KANAN: Preview E-Nota -->
    <!-- ====================================================== -->
    <div class="nota-panel">
        <div class="nota-preview-hd">
            <div class="nota-preview-title">Preview E-Nota</div>
            <span style="font-size:10px;color:#aaa">
                {{ $transaksi->status_transaksi == 'Selesai' ? 'Pengembalian' : 'Penyewaan' }}
            </span>
        </div>
        <div class="nota-paper">
            <div class="nota-top">
                <div class="nota-brand">NM Gallery</div>
                <div class="nota-tagline">Baju Bodo Collection · Makassar</div>
                <div class="nota-trx-label">
                    {{ $transaksi->status_transaksi == 'Selesai' ? 'BUKTI PENGEMBALIAN' : 'BUKTI PENYEWAAN' }}
                </div>
                <div class="nota-trx-num">#TRX-{{ str_pad($transaksi->id_transaksi, 4, '0', STR_PAD_LEFT) }}</div>
            </div>
            <div class="nota-body">
                <div class="nota-row">
                    <span class="nota-key">Tanggal</span>
                    <span class="nota-val">{{ \Carbon\Carbon::parse($transaksi->created_at)->format('d/m/Y') }}</span>
                </div>
                <div class="nota-row">
                    <span class="nota-key">Pelanggan</span>
                    <span class="nota-val">{{ $transaksi->pelanggan->nama_pelanggan ?? '-' }}</span>
                </div>
                <div class="nota-row">
                    <span class="nota-key">Barang</span>
                    <span class="nota-val">
                        {{ $transaksi->detailTransaksis->first()->barang->nama_barang ?? '-' }}
                        @if($transaksi->detailTransaksis->first() && $transaksi->detailTransaksis->first()->ukuran)
                            ({{ $transaksi->detailTransaksis->first()->ukuran }})
                        @endif
                    </span>
                </div>
                <div class="nota-row">
                    <span class="nota-key">Periode</span>
                    <span class="nota-val">
                        {{ \Carbon\Carbon::parse($transaksi->tgl_sewa)->format('d/m/Y') }} s/d
                        {{ \Carbon\Carbon::parse($transaksi->tgl_jatuh_tempo)->format('d/m/Y') }}
                    </span>
                </div>
                <div class="nota-row">
                    <span class="nota-key">Total Sewa</span>
                    <span class="nota-val" style="color:var(--gold-dk);font-weight:700;font-family:monospace">
                        Rp {{ number_format($transaksi->total_biaya, 0, ',', '.') }}
                    </span>
                </div>
                @if(($transaksi->metode_bayar ?? 'Lunas') == 'DP')
                <div class="nota-row">
                    <span class="nota-key">DP Dibayar</span>
                    <span class="nota-val" style="color:#1a8050;font-family:monospace">
                        Rp {{ number_format($transaksi->jumlah_dp ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                @endif
                @if($transaksi->status_transaksi == 'Selesai' && $transaksi->total_denda > 0)
                <div class="nota-row">
                    <span class="nota-key">Denda</span>
                    <span class="nota-val" style="color:#c0392b;font-family:monospace">
                        Rp {{ number_format($transaksi->total_denda, 0, ',', '.') }}
                    </span>
                </div>
                @endif
                <div class="nota-total-box">
                    <span class="nota-total-lbl">
                        @if($transaksi->status_transaksi == 'Selesai')
                            {{-- 
                                "TOTAL DILUNASI" = apa yang dibayar saat pengembalian.
                                Ini adalah sisa_tagihan asli (sebelum di-set ke 0) + denda.
                                Sisa asli = total_biaya - jumlah_dp.
                                Variabel $totalDibayarSaatKembali sudah dihitung di atas.
                            --}}
                            TOTAL DILUNASI
                        @else
                            DIBAYAR DI MUKA
                        @endif
                    </span>
                    <span class="nota-total-val">
                        @if($transaksi->status_transaksi == 'Selesai')
                            Rp {{ number_format($totalDibayarSaatKembali, 0, ',', '.') }}
                        @else
                            Rp {{ number_format($transaksi->jumlah_dp ?? $transaksi->total_biaya, 0, ',', '.') }}
                        @endif
                    </span>
                </div>
                <div class="nota-footer">
                    Terima kasih telah mempercayakan<br>
                    momen Anda kepada <b>NM Gallery</b> ✦
                </div>
            </div>
        </div>

        @if($transaksi->status_transaksi == 'Selesai')
        <button class="nota-gen-btn"
                onclick="window.open('{{ route('transaksi.print.kembali', $transaksi->id_transaksi) }}', '_blank')">
            📋 Cetak Nota Pengembalian
        </button>
        @else
        <button class="nota-gen-btn"
                onclick="window.open('{{ route('transaksi.print', $transaksi->id_transaksi) }}', '_blank')">
            🖨 Cetak E-Nota Sewa
        </button>
        @endif
    </div>

</div>

<style>
@media print {
    .sidebar, .topbar, .pg-head, .form-card, .nota-gen-btn { display: none !important; }
    .nota-panel { box-shadow: none !important; border: none !important; width: 100% !important; }
}
</style>
@endsection