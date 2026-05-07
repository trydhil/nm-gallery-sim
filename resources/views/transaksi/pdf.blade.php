@extends('layouts.pdf_master')

@section('content')
<div class="modal-nota">
    <div class="modal-nota-top">
        <div>
            <div class="modal-nota-brand">NM Gallery</div>
            <div class="modal-nota-tagline">Baju Bodo Authentic Collection</div>
            <div class="modal-nota-addr">Tanete, Kec. Bulukumpa, Kabupaten Bulukumba<br>+62 411-xxx-xxxx · @nmgallery.id</div>
        </div>
        <div class="modal-nota-trxid">
            <div class="label">No. Transaksi</div>
            <div class="num">#TRX-{{ str_pad($transaksi->id_transaksi, 4, '0', STR_PAD_LEFT) }}</div>
            <div class="date">{{ \Carbon\Carbon::parse($transaksi->created_at)->format('d M Y · H:i') }} WIB</div>
        </div>
    </div>

    <div class="modal-nota-body">
        <div class="nota-cust-grid">
            <div>
                <div class="nota-field-k">Nama Pelanggan</div>
                <div class="nota-field-v">{{ $transaksi->pelanggan->nama_pelanggan ?? '-' }}</div>
            </div>
            <div>
                <div class="nota-field-k">No. Telepon</div>
                <div class="nota-field-v">{{ $transaksi->pelanggan->no_telp ?? '-' }}</div>
            </div>
            <div>
                <div class="nota-field-k">Periode Sewa</div>
                <div class="nota-field-v">
                    {{ \Carbon\Carbon::parse($transaksi->tgl_sewa)->format('d M Y H:i') }} s/d {{ \Carbon\Carbon::parse($transaksi->tgl_jatuh_tempo)->format('d M Y H:i') }}
                </div>
            </div>
        </div>

        <table class="nota-items-tbl">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Harga/hr</th>
                    <th>Hari</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $tglSewa = \Carbon\Carbon::parse($transaksi->tgl_sewa);
                    $tglJatuh = \Carbon\Carbon::parse($transaksi->tgl_jatuh_tempo);
                    $durasi = $tglSewa->diffInDays($tglJatuh);
                    if ($durasi == 0) $durasi = 1;
                @endphp
                @foreach($transaksi->detailTransaksis as $detail)
                @php
                    $subtotal = ($detail->barang->harga_sewa ?? 0) * $detail->kuantitas * $durasi;
                @endphp
                <tr>
                    <td>{{ $detail->barang->nama_barang ?? '-' }} ({{ $detail->ukuran ?? '-' }}) x{{ $detail->kuantitas }}</td>
                    <td class="text-right">Rp {{ number_format($detail->barang->harga_sewa ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">×{{ $durasi }}</td>
                    <td class="text-right gold">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="nota-totals">
            <div class="nota-tot-row">
                <span class="nota-tot-lbl">Subtotal Sewa</span>
                <span class="nota-tot-val">Rp {{ number_format($transaksi->total_biaya, 0, ',', '.') }}</span>
            </div>
            <div class="nota-tot-row">
                <span class="nota-tot-lbl">Jaminan</span>
                <span class="nota-tot-val">Rp 200.000</span>
            </div>
            @if(($transaksi->total_denda ?? 0) > 0)
            <div class="nota-tot-row">
                <span class="nota-tot-lbl">Denda Keterlambatan</span>
                <span class="nota-tot-val">Rp {{ number_format($transaksi->total_denda, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="nota-tot-row grand">
                <span class="nota-tot-lbl">TOTAL DIBAYAR</span>
                <span class="nota-tot-val grand-total">Rp {{ number_format(($transaksi->total_biaya + 200000 + ($transaksi->total_denda ?? 0)), 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="nota-foot">
            Terima kasih telah mempercayakan momen spesial Anda kepada <b>NM Gallery</b>.<br>
            Nota ini sah sebagai bukti transaksi. ✦ Makassar, Sulawesi Selatan
        </div>
    </div>
</div>
@endsection