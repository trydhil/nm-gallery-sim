@extends('layouts.app')

@section('title', 'Laporan Keuangan')
@section('breadcrumb', 'Laporan Keuangan')

@section('content')
<div class="page active" id="page-reports">

    <div class="pg-head">
        <div class="pg-title"><i class="bi bi-bar-chart-line"></i> Laporan Keuangan</div>
        <div class="pg-sub">Rekap pendapatan dan penyewaan Baju Bodo</div>
    </div>

    {{-- ── Ringkasan 3 kartu (pengganti grafik) ── --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:22px">
        <div style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:16px 20px;border-top:2px solid var(--gold)">
            <div style="font-size:10px;color:var(--gray-500);font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px">
                <i class="bi bi-bag-heart"></i> Total Penyewaan
            </div>
            <div style="font-size:28px;font-weight:800;color:var(--black)">{{ $totalTransaksiPeriode ?? 0 }}</div>
            <div style="font-size:11px;color:var(--gray-400);margin-top:4px">
                {{ \Carbon\Carbon::parse($startDate)->format('d M') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            </div>
        </div>
        <div style="background:rgba(201,168,76,.06);border:1px solid var(--gold-md);border-radius:12px;padding:16px 20px;border-top:2px solid var(--gold)">
            <div style="font-size:10px;color:var(--gold-dk);font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px">
                <i class="bi bi-cash-stack"></i> Total Pendapatan
            </div>
            <div style="font-size:22px;font-weight:800;color:var(--gold-dk);font-family:monospace">
                Rp {{ number_format($totalPendapatanPeriode ?? 0, 0, ',', '.') }}
            </div>
            <div style="font-size:11px;color:var(--gray-400);margin-top:4px">
                Denda: Rp {{ number_format($totalDendaPeriode ?? 0, 0, ',', '.') }}
            </div>
        </div>
        <div style="background:rgba(45,166,110,.05);border:1px solid rgba(45,166,110,.2);border-radius:12px;padding:16px 20px;border-top:2px solid #2da66e">
            <div style="font-size:10px;color:#1a8050;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px">
                <i class="bi bi-activity"></i> Rata-rata per Hari
            </div>
            @php
                $hariCount = max(1, \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1);
                $rataRata  = ($totalPendapatanPeriode ?? 0) / $hariCount;
            @endphp
            <div style="font-size:22px;font-weight:800;color:#1a8050;font-family:monospace">
                Rp {{ number_format($rataRata, 0, ',', '.') }}
            </div>
            <div style="font-size:11px;color:var(--gray-400);margin-top:4px">Dari {{ $hariCount }} hari</div>
        </div>
    </div>

    {{-- ── Tabel Rincian Transaksi ── --}}
    <div class="card gold-top" style="margin-bottom:20px">
        <div class="card-head">
            <div>
                <div class="card-title"><i class="bi bi-table"></i> Rincian Transaksi</div>
                <div class="card-sub">Filter berdasarkan periode dan tanggal</div>
            </div>
        </div>

        <div class="report-filter-row">
            <div class="period-toggle" id="periodToggle">
                <div class="pt-btn {{ $filter == 'harian'   ? 'active' : '' }}" data-filter="harian">
                    <i class="bi bi-clock"></i> Harian
                </div>
                <div class="pt-btn {{ $filter == 'mingguan' ? 'active' : '' }}" data-filter="mingguan">
                    <i class="bi bi-calendar3-week"></i> Mingguan
                </div>
                <div class="pt-btn {{ $filter == 'bulanan'  ? 'active' : '' }}" data-filter="bulanan">
                    <i class="bi bi-calendar3"></i> Bulanan
                </div>
            </div>
            <input type="date" class="date-input" id="start_date" value="{{ $startDate ?? date('Y-m-01') }}">
            <span style="font-size:12px;color:#aaa">→</span>
            <input type="date" class="date-input" id="end_date" value="{{ $endDate ?? date('Y-m-d') }}">
            <button class="btn-gold" id="btnTerapkan"><i class="bi bi-funnel-fill"></i> Terapkan</button>
        </div>

        <div class="table-responsive">
            <table class="report-tbl">
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Pelanggan</th>
                        <th>Baju</th>
                        <th>Tanggal Sewa</th>
                        <th>Tanggal Kembali</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th>Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksis ?? [] as $item)
                    <tr>
                        <td class="td-mono" style="font-size:11px;color:#C9A84C;font-weight:600;">
                            #TRX-{{ str_pad($item->id_transaksi, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td style="font-weight:500">{{ $item->pelanggan->nama_pelanggan ?? '-' }}</td>
                        <td style="color:#666">
                            @php
                                $firstDetail = $item->detailTransaksis->first();
                                $namaBarang  = '-';
                                if ($firstDetail && $firstDetail->barang) {
                                    $namaBarang = $firstDetail->barang->nama_barang;
                                    if ($firstDetail->ukuran) {
                                        $namaBarang .= ' <span style="font-size:9px;color:#C9A84C;">('.$firstDetail->ukuran.')</span>';
                                    }
                                }
                            @endphp
                            {!! $namaBarang !!}
                        </td>
                        <td class="td-mono" style="font-size:11.5px;color:#888">
                            {{ \Carbon\Carbon::parse($item->tgl_sewa)->format('d/m/Y') }}
                        </td>
                        <td class="td-mono" style="font-size:11.5px;color:#888">
                            @if($item->tgl_kembali)
                                {{ \Carbon\Carbon::parse($item->tgl_kembali)->format('d/m/Y') }}
                            @else
                                <span class="badge badge-out" style="font-size:9px;">
                                    <i class="bi bi-hourglass-split"></i> Belum
                                </span>
                            @endif
                        </td>
                        <td class="td-mono" style="font-size:11.5px">
                            @php
                                $durasi = \Carbon\Carbon::parse($item->tgl_sewa)
                                          ->diffInDays(\Carbon\Carbon::parse($item->tgl_jatuh_tempo));
                            @endphp
                            {{ $durasi }} hari
                        </td>
                        <td>
                            @if($item->status_transaksi == 'Diproses')
                                <span class="badge badge-out">
                                    <i class="bi bi-circle-fill" style="color:#d4900a;font-size:8px;vertical-align:2px"></i> Aktif
                                </span>
                            @elseif($item->status_transaksi == 'Selesai')
                                <span class="badge badge-ready" style="background:rgba(45,166,110,.1);color:#1a8050;border:1px solid rgba(45,166,110,.25);border-radius:20px;">
                                    <i class="bi bi-check-circle-fill" style="color:#1a8050;font-size:10px"></i> Selesai
                                </span>
                            @else
                                <span class="badge badge-damaged">
                                    <i class="bi bi-exclamation-triangle-fill" style="font-size:10px"></i> Terlambat
                                </span>
                            @endif
                        </td>
                        <td class="td-mono td-gold" style="font-weight:700;">
                            Rp {{ number_format(($item->total_biaya + ($item->total_denda ?? 0)), 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:50px">
                            <div style="font-size:16px;color:#aaa"><i class="bi bi-inbox"></i> Belum ada transaksi</div>
                            <div style="font-size:12px;color:#ccc;margin-top:8px">Coba pilih periode yang berbeda</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="report-footer">
            @php
                $totalDisplay = collect($transaksis ?? [])
                    ->sum(fn($i) => $i->total_biaya + ($i->total_denda ?? 0));
            @endphp
            <div>
                <i class="bi bi-table"></i>
                Total: <strong style="color:#1a1a1a">{{ count($transaksis ?? []) }} transaksi</strong>
                &nbsp;|&nbsp;
                <i class="bi bi-cash-stack"></i>
                Pendapatan: <strong style="color:#C9A84C;font-family:monospace">
                    Rp {{ number_format($totalDisplay, 0, ',', '.') }}
                </strong>
            </div>
        </div>
    </div>

    {{-- ── Export Row ── --}}
    <div class="export-row">
        <div class="export-text">
            <div class="export-title"><i class="bi bi-download"></i> Unduh Laporan</div>
            <div class="export-sub">Ekspor rekap keuangan untuk pencatatan Owner</div>
        </div>
        <div class="export-btns">
            <button class="btn-outline" id="btnExportExcel">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
            </button>
            <button class="btn-gold" id="btnExportPDF">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </button>
        </div>
    </div>

</div>

{{--
    ════════════════════════════════════════════════════════════════
    JAVASCRIPT — PERBAIKAN UTAMA
    ════════════════════════════════════════════════════════════════
    Semua kode inisialisasi SENGAJA diletakkan di luar DOMContentLoaded.

    Mengapa? SPA Router di layouts/app.blade.php menyuntikkan HTML ke
    .content LALU langsung menjalankan semua script sebagai IIFE. Karena
    dokumen sudah selesai dimuat, event DOMContentLoaded tidak akan pernah
    terpancar lagi. Akibatnya: event listener yang didaftarkan di dalam
    DOMContentLoaded tidak pernah terpasang → semua tombol diam.

    Solusi: daftarkan semua event listener langsung (top-level), sehingga
    dieksekusi segera setelah HTML disuntikkan dan elemen sudah tersedia.
    ════════════════════════════════════════════════════════════════
--}}
<script>
// ── Helpers ──────────────────────────────────────────────────────────────────

function formatDateLocal(date) {
    var y = date.getFullYear();
    var m = String(date.getMonth() + 1).padStart(2, '0');
    var d = String(date.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + d;
}

function updateDateRangeByFilter(filter) {
    var today = new Date();
    var s = document.getElementById('start_date');
    var e = document.getElementById('end_date');
    if (!s || !e) return;

    if (filter === 'harian') {
        s.value = e.value = formatDateLocal(today);
    } else if (filter === 'mingguan') {
        var day  = today.getDay();
        var diff = (day === 0 ? 6 : day - 1);
        var mon  = new Date(today); mon.setDate(today.getDate() - diff);
        var sun  = new Date(mon);   sun.setDate(mon.getDate() + 6);
        s.value  = formatDateLocal(mon);
        e.value  = formatDateLocal(sun);
    } else {
        // bulanan: 1 s/d akhir bulan ini
        var yr = today.getFullYear(), mo = today.getMonth();
        s.value = formatDateLocal(new Date(yr, mo, 1));
        e.value = formatDateLocal(new Date(yr, mo + 1, 0));
    }
}

function applyFilter() {
    var activePt = document.querySelector('.period-toggle .pt-btn.active');
    var filter   = activePt ? activePt.getAttribute('data-filter') : 'bulanan';
    var start    = document.getElementById('start_date').value;
    var end      = document.getElementById('end_date').value;

    if (!start || !end) { swalAlert('Silakan pilih tanggal terlebih dahulu!', 'warning', 'Peringatan'); return; }
    if (new Date(start) > new Date(end)) {
        swalAlert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir!', 'warning', 'Peringatan'); return;
    }

    window.location.href = '{{ route("laporan") }}?filter=' + filter
                         + '&start=' + start + '&end=' + end;
}

function exportExcel() {
    var activePt = document.querySelector('.period-toggle .pt-btn.active');
    var filter   = activePt ? activePt.getAttribute('data-filter') : 'bulanan';
    var start    = document.getElementById('start_date').value;
    var end      = document.getElementById('end_date').value;
    window.location.href = '{{ route("laporan.export.excel") }}?filter=' + filter
                         + '&start=' + start + '&end=' + end;
}

function exportPDF() {
    var activePt = document.querySelector('.period-toggle .pt-btn.active');
    var filter   = activePt ? activePt.getAttribute('data-filter') : 'bulanan';
    var start    = document.getElementById('start_date').value;
    var end      = document.getElementById('end_date').value;
    window.open('{{ route("laporan.export.pdf") }}?filter=' + filter
              + '&start=' + start + '&end=' + end, '_blank');
}

// ── Inisialisasi langsung (bukan di dalam DOMContentLoaded) ──────────────────

// 1. Period toggle buttons
document.querySelectorAll('.period-toggle .pt-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.period-toggle .pt-btn').forEach(function(b) {
            b.classList.remove('active');
        });
        this.classList.add('active');
        updateDateRangeByFilter(this.getAttribute('data-filter'));
        applyFilter();
    });
});

// 2. Tombol Terapkan
var btnTerapkan = document.getElementById('btnTerapkan');
if (btnTerapkan) btnTerapkan.addEventListener('click', applyFilter);

// 3. Tombol Export
var btnExcel = document.getElementById('btnExportExcel');
if (btnExcel) btnExcel.addEventListener('click', exportExcel);

var btnPdf = document.getElementById('btnExportPDF');
if (btnPdf) btnPdf.addEventListener('click', exportPDF);

// 4. Enter pada input tanggal
['start_date', 'end_date'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') applyFilter();
    });
});
</script>

<style>
.report-filter-row {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 20px; border-bottom: 1px solid #f0f0f0;
    flex-wrap: wrap; background: #fefefe;
}
.period-toggle {
    display: flex; background: #f5f5f5; border: 1px solid #e8e8e8;
    border-radius: 10px; padding: 3px; gap: 3px;
}
.pt-btn {
    padding: 6px 14px; border-radius: 7px; font-size: 12px; font-weight: 600;
    cursor: pointer; color: #888; transition: all .15s;
}
.pt-btn:hover { background: rgba(201,168,76,.12); color: #C9A84C; }
.pt-btn.active { background: #C9A84C; color: white; box-shadow: 0 2px 8px rgba(201,168,76,.3); }
.date-input {
    background: white; border: 1px solid #e0e0e0; border-radius: 9px;
    padding: 8px 13px; font-size: 12px; font-family: monospace; transition: all .2s;
}
.date-input:focus { outline: none; border-color: #C9A84C; box-shadow: 0 0 0 3px rgba(201,168,76,.1); }
.report-tbl { width: 100%; border-collapse: collapse; min-width: 720px; }
.report-tbl thead tr { background: #fafafa; border-bottom: 1px solid #eee; }
.report-tbl th {
    padding: 11px 16px; text-align: left; font-size: 10.5px;
    font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: .8px;
}
.report-tbl tbody tr { border-bottom: 1px solid #f5f5f5; transition: background .1s; }
.report-tbl tbody tr:last-child { border-bottom: none; }
.report-tbl tbody tr:hover { background: #fef9e8; }
.report-tbl td { padding: 11px 16px; font-size: 12.5px; vertical-align: middle; }
.td-mono  { font-family: monospace; font-size: 12px; }
.td-gold  { color: #C9A84C; font-weight: 700; }
.report-footer {
    padding: 12px 20px; border-top: 1px solid #f0f0f0; background: #fafafa;
    display: flex; align-items: center; font-size: 12px;
}
.export-row {
    background: white; border: 1px solid #eee; border-radius: 14px;
    padding: 16px 22px; display: flex; align-items: center;
    justify-content: space-between; flex-wrap: wrap; gap: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,.03);
}
.export-title { font-size: 14px; font-weight: 700; color: #1a1a1a; }
.export-sub   { font-size: 11px; color: #aaa; margin-top: 3px; }
.export-btns  { display: flex; gap: 10px; }

@media (max-width: 768px) {
    .report-filter-row { flex-direction: column; align-items: stretch; }
    .date-input { width: 100%; }
    .export-row { flex-direction: column; }
    .export-btns button { flex: 1; justify-content: center; }
}
</style>
@endsection