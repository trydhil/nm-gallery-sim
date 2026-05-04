{{-- ============================================================
     resources/views/pelanggan/index.blade.php
     REVISI:
     • Ganti semua emoji dengan SVG Heroicons
     • Perbaiki logika badge "Aktif Sewa": cek transaksi aktif
       (status = 'Diproses'), bukan total jumlah sewa
     • Hapus tombol paginasi dummy (hardcoded tanpa backend)
     • Tambah .table-responsive wrapper
     • Responsivitas: stat strip 1 kolom di mobile, search full-width
     ============================================================ --}}
@extends('layouts.app')

@section('title', 'Data Pelanggan')
@section('breadcrumb', 'Data Pelanggan')

@section('content')
<div class="page active" id="page-pelanggan">

    {{-- ═══ PAGE HEADER ═══ --}}
    <div class="pg-head">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div>
                <div class="pg-title">Data Pelanggan</div>
                <div class="pg-sub">{{ $totalPelanggan }} pelanggan terdaftar · diperbarui baru saja</div>
            </div>
            <button class="btn-gold" onclick="showTambahPelangganModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tambah Pelanggan
            </button>
        </div>
    </div>

    {{-- ═══ STAT STRIP ═══ --}}
    <div class="pelanggan-stat-grid">
        <div class="pelanggan-stat-card" style="border-top:2px solid var(--gold)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <div style="font-size:10.5px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.6px">Total Pelanggan</div>
                <div style="width:32px;height:32px;border-radius:8px;background:var(--gold-xs);border:1px solid var(--gold-md);display:flex;align-items:center;justify-content:center">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color:var(--gold-dk)" aria-hidden="true">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                    </svg>
                </div>
            </div>
            <div style="font-size:28px;font-weight:800;color:var(--black);letter-spacing:-.5px">{{ $totalPelanggan }}</div>
            <div style="font-size:11px;color:var(--gray-400);margin-top:4px">{{ $pelangganBaruBulanIni ?? 0 }} baru bulan ini</div>
        </div>

        <div class="pelanggan-stat-card" style="border-top:2px solid #2da66e">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <div style="font-size:10.5px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.6px">Sedang Menyewa</div>
                <div style="width:32px;height:32px;border-radius:8px;background:rgba(45,166,110,.08);border:1px solid rgba(45,166,110,.2);display:flex;align-items:center;justify-content:center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                         stroke="#2da66e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 01-8 0"/>
                    </svg>
                </div>
            </div>
            <div style="font-size:28px;font-weight:800;color:var(--black);letter-spacing:-.5px">{{ $sedangMenyewa ?? 0 }}</div>
            <div style="font-size:11px;color:var(--gray-400);margin-top:4px">Transaksi aktif hari ini</div>
        </div>

        <div class="pelanggan-stat-card" style="border-top:2px solid #e07040">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <div style="font-size:10.5px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.6px">Pelanggan Setia</div>
                <div style="width:32px;height:32px;border-radius:8px;background:rgba(224,112,64,.08);border:1px solid rgba(224,112,64,.2);display:flex;align-items:center;justify-content:center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                         stroke="#e07040" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                </div>
            </div>
            <div style="font-size:28px;font-weight:800;color:var(--black);letter-spacing:-.5px">{{ $pelangganSetia ?? 0 }}</div>
            <div style="font-size:11px;color:var(--gray-400);margin-top:4px">Telah sewa 3 kali atau lebih</div>
        </div>
    </div>

    {{-- ═══ TOOLBAR ═══ --}}
    <div class="pelanggan-toolbar">
        <div class="inv-search" style="max-width:300px;flex:1">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="searchInput" placeholder="Cari nama atau nomor telepon…" aria-label="Cari pelanggan">
        </div>

        <div class="filter-chips">
            <div class="chip active" onclick="setChip(this)">Semua ({{ $totalPelanggan }})</div>
            <div class="chip" onclick="setChip(this)">Aktif ({{ $sedangMenyewa ?? 0 }})</div>
            <div class="chip" onclick="setChip(this)">Riwayat ({{ $totalPelanggan - ($sedangMenyewa ?? 0) }})</div>
        </div>

        {{-- Export PDF: ikon download menggantikan emoji 📄 --}}
        <a href="{{ route('pelanggan.export.pdf') }}" class="btn-outline" style="margin-left:auto">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export PDF
        </a>
    </div>

    {{-- ═══ TABEL ═══ --}}
    <div class="inv-table-card"><div class="table-responsive">
            <table class="inv-tbl">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>No. Telepon</th>
                        <th>Total Sewa</th>
                        <th>Terakhir Sewa</th>
                        <th>Total Bayar</th>
                        <th>Status</th>
                        <th style="width:80px"></th>
                    </tr>
                </thead>
                <tbody id="pelangganTableBody">
                    @forelse($pelanggan as $item)
                    @php
        
                        $sedangAktif = $item->transaksi()
                            ->where('status_transaksi', 'Diproses')
                            ->exists();
                    @endphp
                    <tr data-status="{{ $sedangAktif ? 'aktif' : 'selesai' }}">

                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div class="pelanggan-avatar" aria-hidden="true">
                                    {{ strtoupper(substr($item->nama_pelanggan, 0, 2)) }}
                                </div>
                                <div>
                                    <div style="font-size:13px;font-weight:600;color:var(--black)">{{ $item->nama_pelanggan }}</div>
                                    <div style="font-size:10.5px;color:var(--gray-400)">{{ $item->alamat ?: 'Alamat tidak tersedia' }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="font-mono" style="font-size:12px;color:var(--gray-600)">{{ $item->no_telp }}</td>

                        <td style="text-align:center">
                            <span style="background:var(--gold-xs);color:var(--gold-dk);border:1px solid var(--gold-md);padding:3px 10px;border-radius:12px;font-size:11.5px;font-weight:700">
                                {{ $item->transaksi_count ?? 0 }}×
                            </span>
                        </td>

                        <td style="font-size:12px;color:var(--gray-500)">
                            {{ $item->terakhir_sewa ? \Carbon\Carbon::parse($item->terakhir_sewa)->format('d M Y') : '—' }}
                        </td>

                        <td class="td-mono td-gold" style="font-size:12px">
                            Rp {{ number_format($item->transaksi_sum_total_biaya ?? 0, 0, ',', '.') }}
                        </td>

                        <td>
                            @if($sedangAktif)
                                <span class="badge badge-out" style="white-space:nowrap">Aktif Sewa</span>
                            @else
                                <span class="badge badge-ready" style="white-space:nowrap">Selesai</span>
                            @endif
                        </td>

                        <td>
                            <div class="row-acts">
                                {{-- Edit: ikon pencil menggantikan ✏️ --}}
                                <button class="row-btn"
                                        onclick="showEditPelangganModal({{ $item->id_pelanggan }}, '{{ addslashes($item->nama_pelanggan) }}', '{{ $item->no_telp }}', '{{ addslashes($item->alamat) }}')"
                                        title="Edit pelanggan"
                                        aria-label="Edit {{ $item->nama_pelanggan }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>

                                {{-- Buat Sewa: ikon clipboard menggantikan 📋 --}}
                               
                                    <a href="{{ route('transaksi.create') }}?pelanggan={{ $item->id_pelanggan }}"
                                       class="row-btn"
                                       title="Buat transaksi sewa"
                                       aria-label="Buat sewa untuk {{ $item->nama_pelanggan }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                                            <rect x="9" y="3" width="6" height="4" rx="1"/>
                                            <line x1="9" y1="12" x2="15" y2="12"/>
                                            <line x1="9" y1="16" x2="13" y2="16"/>
                                        </svg>
                                    </a>
                                
                                    {{-- Owner: tombol non-aktif dengan visual disabled --}}
                                    <button class="row-btn"
                                            onclick="showAccessDeniedSewa()"
                                            
                                            style="opacity:.4;cursor:not-allowed">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                                            <rect x="9" y="3" width="6" height="4" rx="1"/>
                                        </svg>
                                    </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:48px;color:var(--gray-400)">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.5"
                                 style="margin:0 auto 10px;display:block;opacity:.3" aria-hidden="true">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                            </svg>
                            Belum ada data pelanggan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer: info jumlah saja, tanpa paginasi dummy --}}
        <div class="tbl-footer">
            <div class="pg-info" id="pelangganCountInfo">
                Menampilkan {{ $pelanggan->count() }} dari {{ $totalPelanggan }} pelanggan
            </div>
        </div>
    </div>

</div>

{{-- MODAL TAMBAH --}}
<div class="modal-overlay" id="tambahPelangganModal" role="dialog" aria-modal="true">
    <div class="modal-popup">
        <div class="modal-popup-header">
            <div>
                <div class="modal-popup-title">Tambah Pelanggan Baru</div>
                <div class="modal-popup-sub">Masukkan data pelanggan</div>
            </div>
            <button class="modal-popup-close" onclick="closeTambahPelangganModal()" aria-label="Tutup">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-popup-body">
            <div class="user-info">
                <div class="user-info-field">
                    <label for="new_nama_pelanggan">Nama Pelanggan *</label>
                    <input type="text" id="new_nama_pelanggan" class="modal-input" placeholder="Nama lengkap">
                </div>
                <div class="user-info-field">
                    <label for="new_no_telp">No. Telepon *</label>
                    <input type="text" id="new_no_telp" class="modal-input" placeholder="0812-xxxx-xxxx">
                </div>
                <div class="user-info-field">
                    <label for="new_alamat">Alamat</label>
                    <input type="text" id="new_alamat" class="modal-input" placeholder="Opsional">
                </div>
            </div>
        </div>
        <div class="modal-popup-footer">
            <button class="btn-white" onclick="closeTambahPelangganModal()">Batal</button>
            <button class="btn-gold" onclick="savePelangganBaru()">Simpan</button>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal-overlay" id="editPelangganModal" role="dialog" aria-modal="true">
    <div class="modal-popup">
        <div class="modal-popup-header">
            <div>
                <div class="modal-popup-title">Edit Pelanggan</div>
                <div class="modal-popup-sub">Ubah data pelanggan</div>
            </div>
            <button class="modal-popup-close" onclick="closeEditPelangganModal()" aria-label="Tutup">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-popup-body">
            <div class="user-info">
                <div class="user-info-field">
                    <label for="edit_nama_pelanggan">Nama Pelanggan *</label>
                    <input type="text" id="edit_nama_pelanggan" class="modal-input">
                </div>
                <div class="user-info-field">
                    <label for="edit_no_telp">No. Telepon *</label>
                    <input type="text" id="edit_no_telp" class="modal-input">
                </div>
                <div class="user-info-field">
                    <label for="edit_alamat">Alamat</label>
                    <input type="text" id="edit_alamat" class="modal-input">
                </div>
            </div>
        </div>
        <div class="modal-popup-footer">
            <button class="btn-white" onclick="closeEditPelangganModal()">Batal</button>
            <button class="btn-gold" onclick="updatePelanggan()">Simpan Perubahan</button>
        </div>
    </div>
</div>

<script>
let currentEditId = null;

/* ── Chip filter berbasis data-status pada <tr> ── */
function setChip(el) {
    el.closest('.filter-chips').querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    let text = el.innerText.toLowerCase().replace(/\s*\(.*\)/, '').trim();
    let rows = document.querySelectorAll('#pelangganTableBody tr[data-status]');
    let count = 0;
    rows.forEach(row => {
        let s = row.dataset.status;
        let show = text === 'semua' || (text === 'aktif' && s === 'aktif') || (text === 'riwayat' && s === 'selesai');
        row.style.display = show ? '' : 'none';
        if (show) count++;
    });
    updateCount(count, rows.length);
}

/* ── Search ── */
document.getElementById('searchInput').addEventListener('keyup', function() {
    let q = this.value.toLowerCase().trim();
    let rows = document.querySelectorAll('#pelangganTableBody tr[data-status]');
    let count = 0;
    rows.forEach(row => {
        let nama = row.cells[0]?.innerText.toLowerCase() || '';
        let telp = row.cells[1]?.innerText.toLowerCase() || '';
        let show = !q || nama.includes(q) || telp.includes(q);
        row.style.display = show ? '' : 'none';
        if (show) count++;
    });
    updateCount(count, rows.length);
});

function updateCount(v, t) {
    let el = document.getElementById('pelangganCountInfo');
    if (el) el.textContent = `Menampilkan ${v} dari ${t} pelanggan`;
}


/* ── Tambah ── */
function showTambahPelangganModal() {
    ['new_nama_pelanggan','new_no_telp','new_alamat'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('tambahPelangganModal').classList.add('show');
}
function closeTambahPelangganModal() { document.getElementById('tambahPelangganModal').classList.remove('show'); }
function savePelangganBaru() {
    let nama = document.getElementById('new_nama_pelanggan').value.trim();
    let telp = document.getElementById('new_no_telp').value.trim();
    let alamat = document.getElementById('new_alamat').value.trim();
    if (!nama) { alert('Nama harus diisi!'); return; }
    if (!telp) { alert('Telepon harus diisi!'); return; }
    let fd = new FormData();
    fd.append('nama_pelanggan', nama); fd.append('no_telp', telp);
    fd.append('alamat', alamat); fd.append('_token', '{{ csrf_token() }}');
    fetch('{{ route("pelanggan.store") }}', { method:'POST', body:fd })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); })
        .catch(err => alert('Error: ' + err));
}

/* ── Edit ── */
function showEditPelangganModal(id, nama, telp, alamat) {
    currentEditId = id;
    document.getElementById('edit_nama_pelanggan').value = nama;
    document.getElementById('edit_no_telp').value = telp;
    document.getElementById('edit_alamat').value = alamat || '';
    document.getElementById('editPelangganModal').classList.add('show');
}
function closeEditPelangganModal() { document.getElementById('editPelangganModal').classList.remove('show'); }
function updatePelanggan() {
    let nama = document.getElementById('edit_nama_pelanggan').value.trim();
    let telp = document.getElementById('edit_no_telp').value.trim();
    let alamat = document.getElementById('edit_alamat').value.trim();
    if (!nama) { alert('Nama harus diisi!'); return; }
    if (!telp) { alert('Telepon harus diisi!'); return; }
    let fd = new FormData();
    fd.append('nama_pelanggan', nama); fd.append('no_telp', telp);
    fd.append('alamat', alamat); fd.append('_method','PUT');
    fd.append('_token', '{{ csrf_token() }}');
    fetch('/pelanggan/' + currentEditId, { method:'POST', body:fd })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); })
        .catch(err => alert('Error: ' + err));
}

document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    closeTambahPelangganModal(); closeEditPelangganModal();
});
</script>

<style>
.pelanggan-stat-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
.pelanggan-stat-card {
    background: var(--white); border: 1px solid var(--gray-200);
    border-radius: var(--r3); padding: 16px 18px; box-shadow: var(--sh-xs);
    transition: box-shadow .2s, border-color .2s;
}
.pelanggan-stat-card:hover { box-shadow: var(--sh-sm); border-color: var(--gray-300); }

.pelanggan-toolbar { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }

.inv-search {
    display: flex; align-items: center; gap: 7px;
    background: white; border: 1px solid var(--gray-200);
    border-radius: 8px; padding: 0 12px;
    transition: border-color .2s, box-shadow .2s;
}
.inv-search:focus-within { border-color: var(--gold-rim); box-shadow: 0 0 0 3px var(--gold-xs); }
.inv-search input { flex:1; border:none; background:transparent; outline:none; padding:8.5px 0; font-size:12.5px; }
.inv-search input::placeholder { color: var(--gray-400); }

.filter-chips { display: flex; gap: 6px; flex-wrap: wrap; }
.chip { padding:5px 12px; border-radius:20px; font-size:11.5px; font-weight:500; border:1px solid var(--gray-200); background:white; color:var(--gray-500); cursor:pointer; transition:all .12s; }
.chip:hover { border-color:var(--gold-rim); color:var(--gold-dk); }
.chip.active { background:var(--black); border-color:var(--black); color:var(--gold-lt); }

.inv-table-card { background:white; border:1px solid var(--gray-200); border-radius:var(--r3); box-shadow:var(--sh-xs); overflow:visible; }
.table-responsive { overflow-x:auto; -webkit-overflow-scrolling:touch; border-radius:var(--r3) var(--r3) 0 0; }

.inv-tbl { width:100%; border-collapse:collapse; min-width:640px; }
.inv-tbl thead tr { background:var(--gray-50); border-bottom:1px solid var(--gray-200); }
.inv-tbl th { padding:12px 16px; text-align:left; font-size:10.5px; font-weight:700; color:var(--gray-500); text-transform:uppercase; letter-spacing:.8px; white-space:nowrap; }
.inv-tbl tbody tr { border-bottom:1px solid var(--gray-100); transition:background .1s; }
.inv-tbl tbody tr:last-child td { border-bottom:none; }
.inv-tbl tbody tr:hover { background:var(--gray-50); }
.inv-tbl td { padding:13px 16px; font-size:12.5px; vertical-align:middle; }

.pelanggan-avatar {
    width:34px; height:34px; flex-shrink:0; border-radius:50%;
    background:var(--black); border:1.5px solid var(--gold-md);
    display:flex; align-items:center; justify-content:center;
    font-size:12px; font-weight:700; color:var(--gold-lt);
}

.row-acts { display:flex; gap:5px; opacity:0; transition:opacity .12s; }
.inv-tbl tbody tr:hover .row-acts { opacity:1; }
.row-btn {
    width:28px; height:28px; border-radius:6px; border:1px solid var(--gray-200);
    background:white; display:flex; align-items:center; justify-content:center;
    cursor:pointer; color:var(--gray-500); text-decoration:none; transition:all .12s;
}
.row-btn:hover { border-color:var(--gold-rim); color:var(--gold-dk); background:var(--gold-xs); }

.badge { display:inline-flex; align-items:center; gap:4.5px; padding:3.5px 9px; border-radius:5px; font-size:11px; font-weight:600; white-space:nowrap; }
.badge::before { content:''; width:5px; height:5px; border-radius:50%; flex-shrink:0; }
.badge-out { background:var(--gold-xs); color:var(--gold-dk); border:1px solid var(--gold-md); }
.badge-out::before { background:var(--gold); }
.badge-ready { background:rgba(45,166,110,.08); color:#1a8050; border:1px solid rgba(45,166,110,.2); }
.badge-ready::before { background:#2da66e; }

.font-mono { font-family:'JetBrains Mono',monospace; }
.td-mono { font-family:'JetBrains Mono',monospace; font-size:12px; }
.td-gold { color:var(--gold-dk); font-weight:700; }

.tbl-footer { padding:12px 16px; border-top:1px solid var(--gray-100); background:var(--gray-50); border-radius:0 0 var(--r3) var(--r3); }
.pg-info { font-size:11.5px; color:var(--gray-400); }

/* Modal */
.modal-overlay { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,.6); backdrop-filter:blur(4px); z-index:1000; display:flex; align-items:center; justify-content:center; opacity:0; visibility:hidden; transition:all .25s ease; }
.modal-overlay.show { opacity:1; visibility:visible; }
.modal-popup { background:white; border-radius:20px; width:90%; max-width:450px; max-height:88vh; overflow:hidden; box-shadow:0 25px 50px rgba(0,0,0,.3); transform:scale(.95); transition:transform .25s ease; }
.modal-overlay.show .modal-popup { transform:scale(1); }
.modal-popup-header { padding:20px 24px; background:var(--gray-50); border-bottom:1px solid var(--gray-200); display:flex; justify-content:space-between; align-items:flex-start; }
.modal-popup-title { font-size:18px; font-weight:700; color:var(--black); }
.modal-popup-sub { font-size:11px; color:var(--gold-dk); margin-top:4px; }
.modal-popup-close { width:30px; height:30px; border-radius:8px; border:1px solid var(--gray-200); background:white; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--gray-500); transition:all .15s; }
.modal-popup-close:hover { border-color:var(--gold-rim); color:var(--gold-dk); background:var(--gold-xs); }
.modal-popup-body { padding:24px; max-height:58vh; overflow-y:auto; }
.modal-popup-footer { padding:16px 24px; border-top:1px solid var(--gray-200); background:var(--gray-50); display:flex; gap:12px; justify-content:flex-end; }
.user-info { display:flex; flex-direction:column; gap:16px; }
.user-info-field { display:flex; flex-direction:column; gap:6px; }
.user-info-field label { font-size:11px; font-weight:700; color:var(--gray-500); text-transform:uppercase; letter-spacing:.5px; }
.modal-input { padding:11px 14px; border:1.5px solid var(--gray-200); border-radius:10px; font-size:13px; font-family:inherit; transition:border-color .2s, box-shadow .2s; width:100%; }
.modal-input:focus { outline:none; border-color:var(--gold); box-shadow:0 0 0 3px var(--gold-xs); }

@media (max-width: 768px) {
    .pelanggan-stat-grid { grid-template-columns: 1fr; }
    .pelanggan-toolbar { flex-direction: column; align-items: stretch; }
    .inv-search { max-width: 100% !important; }
    .pelanggan-toolbar > a { align-self: flex-start; }
}
@media (max-width: 480px) {
    .filter-chips .chip { font-size:11px; padding:4px 10px; }
}
</style>
@endsection