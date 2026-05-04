@extends('layouts.app')

@section('title', 'Inventaris & Stok')
@section('breadcrumb', 'Inventaris & Stok')

@section('content')

@php
    $isOwner = session('user')['role'] === 'Owner';
@endphp

{{-- ═══════════════════════════════════════════════════════
     CSS LOKAL — Mengikuti pola visual yang sama dengan
     halaman Transaksi agar bahasa desain konsisten.
     Kita hanya mendeklarasikan class yang unik untuk
     halaman ini; class umum (btn-gold, badge, dll.)
     sudah ada di layouts/app.blade.php.
═══════════════════════════════════════════════════════ --}}
<style>
/* ── Wrapper halaman: memenuhi sisa tinggi area content ── */
.inv-page {
    display: flex;
    flex-direction: column;
    /* Tinggi penuh dikurangi padding content (24px atas + 24px bawah) */
    height: calc(100vh - 52px - 48px);
    margin: -24px;          /* hapus padding default .content */
    overflow: hidden;
}

/* ── Tab bar di bagian atas ── */
.inv-tabbar {
    background: #fff;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    align-items: center;
    padding: 0 20px;
    gap: 4px;
    flex-shrink: 0;
    height: 44px;
}
.inv-tab {
    padding: 0 18px;
    height: 100%;
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 12.5px;
    font-weight: 500;
    color: var(--gray-500);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: color .15s, border-color .15s;
    white-space: nowrap;
}
.inv-tab:hover  { color: var(--gold-dk); }
.inv-tab.active { color: var(--black); font-weight: 600; border-bottom-color: var(--gold); }
.inv-tab-badge  {
    background: var(--gold);
    color: var(--black);
    font-size: 9.5px;
    font-weight: 700;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
}

/* ── Panel tab content ── */
.inv-tab-content         { display: none; flex: 1; overflow: hidden; }
.inv-tab-content.active  { display: flex; }

/* ══════════════════════════════════════════════
   TAB 1 — KELOLA STOK (split panel)
══════════════════════════════════════════════ */
.inv-split { display: flex; flex: 1; overflow: hidden; }

/* ── Sisi kiri: Katalog barang ── */
.inv-katalog {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: #f8f7f4;
    border-right: 1px solid var(--gray-200);
}
.inv-kat-top {
    background: #fff;
    border-bottom: 1px solid var(--gray-200);
    padding: 11px 14px;
    display: flex;
    flex-direction: column;
    gap: 9px;
    flex-shrink: 0;
}
.inv-search {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8f7f4;
    border: 1.5px solid rgba(0,0,0,.12);
    border-radius: 10px;
    padding: 0 12px;
    transition: .2s;
}
.inv-search:focus-within { border-color: var(--gold); background: #fff; }
.inv-search input {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    padding: 8px 0;
    font-size: 13px;
    font-family: var(--ff);
    color: var(--black);
}
.inv-search input::placeholder { color: #bbb; }

/* Filter chips horizontal scroll */
.inv-chips { display: flex; gap: 6px; overflow-x: auto; }
.inv-chips::-webkit-scrollbar { height: 0; }
.inv-chip {
    padding: 4px 13px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 500;
    border: 1px solid rgba(0,0,0,.14);
    background: #fff;
    color: #6b6b6b;
    cursor: pointer;
    white-space: nowrap;
    transition: .12s;
    flex-shrink: 0;
}
.inv-chip:hover:not(.active) { border-color: var(--gold-dk); color: var(--gold-dk); }
.inv-chip.active { background: var(--black); border-color: var(--black); color: var(--gold-lt); }

/* Grid kartu barang */
.inv-grid {
    flex: 1;
    overflow-y: auto;
    padding: 12px 14px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(152px, 1fr));
    gap: 10px;
    align-content: start;
}
.inv-grid::-webkit-scrollbar { width: 4px; }
.inv-grid::-webkit-scrollbar-thumb { background: rgba(0,0,0,.15); border-radius: 2px; }

/* Kartu individual barang */
.inv-card {
    background: #fff;
    border: 1.5px solid rgba(0,0,0,.09);
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    transition: border-color .18s, box-shadow .18s, transform .18s;
    position: relative;
}
.inv-card:hover {
    border-color: var(--gold);
    box-shadow: 0 3px 14px rgba(201,168,76,.15);
    transform: translateY(-2px);
}
/* Kartu yang sedang dipilih mendapat highlight penuh */
.inv-card.selected {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(201,168,76,.18), 0 4px 16px rgba(201,168,76,.15);
}
.inv-card.selected::after {
    content: '✓';
    position: absolute;
    top: 7px;
    right: 7px;
    width: 20px;
    height: 20px;
    background: var(--gold);
    color: var(--black);
    border-radius: 50%;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
}
/* Foto/placeholder item */
.inv-card-img {
    height: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 34px;
    background: linear-gradient(135deg, #faf5e8, #f5edd6);
    position: relative;
    overflow: hidden;
}
.inv-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    inset: 0;
}
/* Badge status di pojok kiri atas kartu */
.inv-status-badge {
    position: absolute;
    top: 6px;
    left: 6px;
    font-size: 8.5px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 9px;
    letter-spacing: .3px;
    z-index: 1;
}
.inv-status-badge.tersedia { background: rgba(26,128,80,.12); color: #1a8050; border: 1px solid rgba(26,128,80,.25); }
.inv-status-badge.disewa   { background: rgba(201,168,76,.15); color: var(--gold-dk); border: 1px solid var(--gold-md); }
.inv-status-badge.laundry  { background: rgba(59,130,246,.1); color: #2563eb; border: 1px solid rgba(59,130,246,.25); }
.inv-status-badge.rusak    { background: rgba(220,52,52,.1); color: #c0392b; border: 1px solid rgba(220,52,52,.25); }

.inv-card-body { padding: 9px 10px; }
.inv-card-nama  { font-size: 11.5px; font-weight: 600; color: var(--black); line-height: 1.3; margin-bottom: 3px; }
.inv-card-meta  { font-size: 10px; color: #aaa; margin-bottom: 5px; }
.inv-card-stok  {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.inv-stok-num {
    font-family: var(--ff-mono);
    font-size: 13px;
    font-weight: 700;
    color: var(--gold-dk);
}
.inv-stok-zero { color: #c0392b; }  /* merah bila stok = 0 */

/* ── Sisi kanan: Panel manajemen stok ── */
.inv-panel {
    width: 290px;
    background: #fff;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
}

/* State kosong: tidak ada barang dipilih */
.inv-panel-empty {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    color: #bbb;
    padding: 24px;
    text-align: center;
}
.inv-panel-empty-ico { font-size: 36px; opacity: .3; }
.inv-panel-empty-txt { font-size: 11.5px; line-height: 1.6; }

/* Header panel saat barang dipilih */
.inv-panel-head {
    background: var(--black);
    padding: 14px 16px;
    flex-shrink: 0;
}
.inv-panel-nama { font-size: 14px; font-weight: 600; color: var(--gold-lt); margin-bottom: 2px; line-height: 1.3; }
.inv-panel-sub  { font-size: 11px; color: rgba(255,255,255,.35); }

/* Body panel: scrollable */
.inv-panel-body { flex: 1; overflow-y: auto; padding: 16px; }
.inv-panel-body::-webkit-scrollbar { width: 3px; }
.inv-panel-body::-webkit-scrollbar-thumb { background: var(--gray-300); }

/* Judul seksi dalam panel */
.inv-sec-title {
    font-size: 10px;
    font-weight: 700;
    color: #999;
    text-transform: uppercase;
    letter-spacing: .9px;
    padding-bottom: 7px;
    border-bottom: 1px solid var(--gray-100);
    margin-bottom: 12px;
}

/* Baris stok per ukuran: label + kontrol +/- */
.stok-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    background: #f8f7f4;
    border-radius: 8px;
    margin-bottom: 8px;
    border: 1.5px solid transparent;
    transition: border-color .15s;
}
.stok-row:focus-within { border-color: var(--gold-rim); background: var(--gold-xs); }
.stok-size-lbl {
    font-size: 13px;
    font-weight: 700;
    color: var(--black);
    min-width: 40px;
}
/* Kontrol quantity +/- */
.stok-ctrl { display: flex; align-items: center; gap: 8px; }
.stok-btn {
    width: 26px; height: 26px;
    border-radius: 7px;
    border: 1.5px solid rgba(0,0,0,.14);
    background: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: var(--black);
    transition: .12s;
    line-height: 1;
    font-family: var(--ff);
}
.stok-btn:hover { border-color: var(--gold); color: var(--gold-dk); background: var(--gold-xs); }
.stok-btn.minus:hover { border-color: #e03434; color: #e03434; background: rgba(220,52,52,.06); }
/* Input jumlah stok — bisa diketik langsung atau diubah via +/- */
.stok-input {
    width: 48px;
    text-align: center;
    border: 1.5px solid rgba(0,0,0,.12);
    border-radius: 6px;
    padding: 5px 4px;
    font-size: 14px;
    font-family: var(--ff-mono);
    font-weight: 700;
    color: var(--gold-dk);
    background: #fff;
    outline: none;
    transition: border-color .15s;
    -moz-appearance: textfield; /* sembunyikan spinner di Firefox */
}
.stok-input::-webkit-outer-spin-button,
.stok-input::-webkit-inner-spin-button { -webkit-appearance: none; }
.stok-input:focus { border-color: var(--gold); }

/* Box ringkasan total */
.stok-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    background: var(--gold-xs);
    border-radius: 8px;
    border: 1px solid var(--gold-md);
    margin: 12px 0 16px;
}
.stok-summary-lbl { font-size: 12.5px; font-weight: 600; }
.stok-summary-val { font-family: var(--ff-mono); font-size: 16px; font-weight: 700; color: var(--gold-dk); }

/* Dropdown status barang */
.status-select {
    width: 100%;
    padding: 9px 12px;
    border: 1.5px solid var(--gray-200);
    border-radius: 8px;
    font-size: 13px;
    font-family: var(--ff);
    color: var(--black);
    background: #fff;
    outline: none;
    cursor: pointer;
    transition: border-color .15s;
    margin-bottom: 12px;
}
.status-select:focus { border-color: var(--gold); }

/* Footer panel: tombol aksi utama */
.inv-panel-foot {
    padding: 13px 16px;
    border-top: 1px solid var(--gray-100);
    flex-shrink: 0;
}
.btn-save-stok {
    width: 100%;
    padding: 11px;
    background: var(--black);
    border: 1.5px solid rgba(201,168,76,.4);
    border-radius: 10px;
    color: var(--gold-lt);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: .18s;
    font-family: var(--ff);
    margin-bottom: 8px;
}
.btn-save-stok:hover { background: #1a1a1a; box-shadow: 0 4px 16px rgba(201,168,76,.2); }
.btn-save-stok:disabled { opacity: .4; cursor: not-allowed; }
.btn-delete-barang {
    width: 100%;
    padding: 8px;
    background: transparent;
    border: 1px solid rgba(220,52,52,.3);
    border-radius: 10px;
    color: #c0392b;
    font-size: 11.5px;
    cursor: pointer;
    transition: .12s;
    font-family: var(--ff);
}
.btn-delete-barang:hover { background: rgba(220,52,52,.06); border-color: #c0392b; }

/* ══════════════════════════════════════════════
   TAB 2 — TAMBAH BARANG (Owner only)
══════════════════════════════════════════════ */
.tambah-wrap {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
    background: #f8f7f4;
}
.tambah-wrap::-webkit-scrollbar { width: 5px; }
.tambah-wrap::-webkit-scrollbar-thumb { background: var(--gray-300); border-radius: 3px; }

/* Form tambah barang: dua kolom di layar lebar */
.tambah-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    max-width: 800px;
}
.tambah-grid .full-col { grid-column: 1 / -1; }

/* Field individual dalam form */
.f-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.f-group label {
    font-size: 11px;
    font-weight: 700;
    color: var(--gray-600);
    text-transform: uppercase;
    letter-spacing: .5px;
}
.f-inp {
    padding: 10px 13px;
    border: 1.5px solid var(--gray-200);
    border-radius: 8px;
    font-size: 13px;
    font-family: var(--ff);
    color: var(--black);
    background: #fff;
    outline: none;
    transition: border-color .18s, box-shadow .18s;
}
.f-inp:focus { border-color: var(--gold); box-shadow: 0 0 0 3px var(--gold-xs); }

/* Grid ukuran+stok dalam form tambah */
.ukuran-stok-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
.ukuran-stok-item {
    background: #fff;
    border: 1.5px solid var(--gray-200);
    border-radius: 8px;
    padding: 12px 10px;
    text-align: center;
    transition: border-color .15s;
}
.ukuran-stok-item:focus-within { border-color: var(--gold); background: var(--gold-xs); }
.ukuran-stok-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--gray-600);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.ukuran-stok-label input[type="checkbox"] { accent-color: var(--gold); cursor: pointer; }
.ukuran-stok-num {
    width: 100%;
    text-align: center;
    border: 1.5px solid var(--gray-200);
    border-radius: 6px;
    padding: 7px 4px;
    font-size: 15px;
    font-family: var(--ff-mono);
    font-weight: 700;
    color: var(--gold-dk);
    background: #f8f7f4;
    outline: none;
    transition: border-color .15s, background .15s;
}
.ukuran-stok-num:disabled { opacity: .35; cursor: not-allowed; }
.ukuran-stok-num:not(:disabled) { background: #fff; }
.ukuran-stok-num:focus { border-color: var(--gold); }

/* ── Barang / Inventaris Responsive ── */
@media (max-width: 900px) {
  /* Stack the split panel vertically on tablet */
  .inv-split {
    flex-direction: column !important;
  }
  .inv-panel {
    width: 100% !important;
    max-height: 340px;
    border-top: 1px solid var(--gray-200);
    border-right: none;
  }
  .inv-katalog {
    border-right: none !important;
    border-bottom: 1px solid var(--gray-200);
    min-height: 320px;
  }
}

@media (max-width: 768px) {
  /* Full-screen inventory layout */
  .inv-page {
    height: auto !important;
    min-height: calc(100vh - 52px - 44px - 28px);
    overflow: visible !important;
    margin: -14px !important;
  }
  .inv-tabbar { padding: 0 12px; gap: 0; overflow-x: auto; }
  .inv-tab    { padding: 0 14px; font-size: 12px; flex-shrink: 0; }

  /* Catalog grid: 2 columns on narrow screens */
  .inv-grid {
    grid-template-columns: repeat(2, 1fr) !important;
    padding: 10px;
  }

  /* Stacked split view */
  .inv-split {
    flex-direction: column !important;
    overflow: visible !important;
  }
  .inv-katalog {
    overflow: visible !important;
    border-right: none !important;
    min-height: 0;
  }
  .inv-grid {
    max-height: 60vh;
    overflow-y: auto;
  }
  .inv-panel {
    width: 100% !important;
    max-height: none;
    border-top: 2px solid var(--gold-md);
  }

  /* Tab 2 table-responsive */
  .tab2-table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
}
</style>

{{-- ═══════════════════════════════════════════
     STRUKTUR HTML UTAMA
═══════════════════════════════════════════ --}}
<div class="inv-page">

    {{-- ── TAB BAR ── --}}
    <div class="inv-tabbar">
        <div class="inv-tab active" id="tabKelola" onclick="switchInvTab('kelola')">
            <i class="bi bi-box-seam"></i> Kelola Stok
            <div class="inv-tab-badge">{{ $totalBarang }}</div>
        </div>
        @if($isOwner)
        <div class="inv-tab" id="tabTambah" onclick="switchInvTab('tambah')">
            <i class="bi bi-plus-circle"></i> Tambah Barang Baru
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════
         TAB KONTEN 1: KELOLA STOK
    ══════════════════════════════════════ --}}
    <div class="inv-tab-content active" id="contentKelola">
        <div class="inv-split">

            {{-- ── KIRI: Katalog ── --}}
            <div class="inv-katalog">
                <div class="inv-kat-top">
                    <div class="inv-search">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                             stroke="#bbb" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input id="invSearch" placeholder="Cari nama baju atau ukuran…"
                               oninput="renderGrid()">
                    </div>
                    <div class="inv-chips" id="invChips"></div>
                </div>
                <div class="inv-grid" id="invGrid"></div>
            </div>

            {{-- ── KANAN: Panel Stok ── --}}
            <div class="inv-panel" id="invPanel">

                {{-- State kosong: tampil saat tidak ada item dipilih --}}
                <div class="inv-panel-empty" id="panelEmpty">
                    <div class="inv-panel-empty-ico"><i class="bi bi-bag-heart" style="font-size:36px;opacity:.3;color:var(--gold-dk)"></i></div>
                    <div class="inv-panel-empty-txt">
                        Pilih barang dari katalog<br>untuk mulai mengelola stok
                    </div>
                </div>

                {{-- State aktif: tampil saat item dipilih --}}
                <div id="panelActive" style="display:none;flex-direction:column;flex:1;overflow:hidden">

                    {{-- Header --}}
                    <div class="inv-panel-head">
                        <div class="inv-panel-nama" id="panelNama">—</div>
                        <div class="inv-panel-sub" id="panelSub">—</div>
                    </div>

                    {{-- Body (scrollable) --}}
                    <div class="inv-panel-body">

                        {{-- Bagian 1: Stok per ukuran --}}
                        <div class="inv-sec-title"><i class="bi bi-grid-3x3-gap"></i> Stok per Ukuran</div>
                        <div id="stokRows">
                            {{-- Diisi oleh JavaScript saat barang dipilih --}}
                        </div>

                        {{-- Ringkasan total --}}
                        <div class="stok-summary">
                            <span class="stok-summary-lbl">Total Stok</span>
                            <span class="stok-summary-val" id="totalStokVal">0 pcs</span>
                        </div>

                        {{-- Bagian 2: Status barang --}}
                        <div class="inv-sec-title"><i class="bi bi-bookmark"></i> Status Barang</div>
                        <select class="status-select" id="panelStatus"
                                onchange="updateStatusColor()">
                            <option value="Tersedia">Tersedia</option>
                            <option value="Disewa">Sedang Disewa</option>
                            <option value="Laundry">Laundry</option>
                            <option value="Rusak">Rusak / Perbaikan</option>
                        </select>

                        {{-- Bagian 3: Info harga (read-only untuk referensi) --}}
                        <div class="inv-sec-title" style="margin-top:8px"><i class="bi bi-cash"></i> Harga Sewa</div>
                        <div style="padding:10px 12px;background:#f8f7f4;border-radius:8px;
                            border:1px solid var(--gray-200);margin-bottom:4px">
                            <span style="font-family:var(--ff-mono);font-size:15px;
                                font-weight:700;color:var(--gold-dk)" id="panelHarga">—</span>
                            <span style="font-size:10.5px;color:#aaa"> / hari</span>
                        </div>

                    </div>

                    {{-- Footer: Tombol aksi --}}
                    <div class="inv-panel-foot">
                        <button class="btn-save-stok" id="btnSaveStok"
                                onclick="saveStok()">
                            <i class=\"bi bi-floppy2-fill\"></i> Simpan Perubahan Stok
                        </button>
                        @if($isOwner)
                        <button class="btn-delete-barang" id="btnDeleteBarang"
                                onclick="deleteBarang()">
                            <i class="bi bi-trash"></i> Hapus Barang dari Inventaris
                        </button>
                        @endif
                    </div>

                </div>{{-- end panelActive --}}

            </div>{{-- end inv-panel --}}

        </div>{{-- end inv-split --}}
    </div>{{-- end contentKelola --}}

    {{-- ══════════════════════════════════════
         TAB KONTEN 2: TAMBAH BARANG (Owner)
    ══════════════════════════════════════ --}}
    @if($isOwner)
    <div class="inv-tab-content" id="contentTambah">
        <div class="tambah-wrap">

            <div style="max-width:800px">
                <div style="margin-bottom:20px">
                    <div style="font-size:16px;font-weight:700;color:var(--black)">
                        <i class="bi bi-plus-circle-fill"></i> Daftarkan Barang Baru
                    </div>
                    <div style="font-size:12px;color:var(--gray-400);margin-top:4px">
                        Isi semua detail barang, lalu tentukan stok awal per ukuran.
                        Barang baru otomatis berstatus <strong>Tersedia</strong>.
                    </div>
                </div>

                <form id="formTambah" enctype="multipart/form-data">
                @csrf

                <div class="tambah-grid">

                    <div class="f-group">
                        <label>Nama Baju *</label>
                        <input type="text" name="nama_barang" class="f-inp"
                               placeholder="Contoh: Baju Bodo Sutra Hijau" required>
                    </div>

                    <div class="f-group">
                        <label>Harga Sewa / Hari *</label>
                        <input type="number" name="harga_sewa" class="f-inp"
                               placeholder="200000" min="0" step="1000" required>
                    </div>

                    <div class="f-group full-col">
                        <label>Label Ukuran *</label>
                        <input type="text" name="ukuran" id="tambahUkuranLabel" class="f-inp"
                               placeholder="Diisi otomatis dari centang ukuran di bawah…"
                               readonly
                               style="background:var(--gray-50);color:var(--gray-500)">
                        <span style="font-size:10.5px;color:#aaa;margin-top:3px">
                            Label ini terisi otomatis berdasarkan ukuran yang Anda centang.
                        </span>
                    </div>

                    <div class="f-group full-col">
                        <label>Stok Awal per Ukuran *</label>
                        <div class="ukuran-stok-grid">
                            @foreach(['S','M','L','XL'] as $uk)
                            <div class="ukuran-stok-item">
                                <div class="ukuran-stok-label">
                                    <input type="checkbox"
                                           class="ukuran-cb"
                                           data-ukuran="{{ $uk }}"
                                           onchange="onUkuranChange()">
                                    Size {{ $uk }}
                                </div>
                                <input type="number"
                                       class="ukuran-stok-num"
                                       data-ukuran="{{ $uk }}"
                                       value="0" min="0"
                                       disabled
                                       oninput="onUkuranChange()">
                            </div>
                            @endforeach
                        </div>
                        {{-- Hidden inputs yang dikirim ke server --}}
                        <input type="hidden" name="stok" id="tambahStokJson">
                    </div>

                    <div class="f-group full-col">
                        <label>Foto Barang</label>
                        <input type="file" name="foto" class="f-inp"
                               accept="image/jpeg,image/png,image/jpg"
                               style="padding:7px 12px;cursor:pointer">
                        <span style="font-size:10.5px;color:#aaa;margin-top:2px">
                            Format JPG / PNG, maksimal 2 MB. Kosongkan jika belum ada foto.
                        </span>
                    </div>

                </div>{{-- end tambah-grid --}}

                <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px">
                    <button type="button" onclick="resetFormTambah()" class="btn-white">
                        ↺ Reset Form
                    </button>
                    <button type="submit" class="btn-gold"
                            style="padding:10px 28px;font-size:13.5px">
                        <i class="bi bi-floppy2-fill"></i> Simpan Barang Baru
                    </button>
                </div>

                </form>
            </div>

        </div>
    </div>
    @endif

</div>{{-- end .inv-page --}}

{{-- Element Toast --}}
<div id="invToast" style="display: none; position: fixed; bottom: 20px; right: 20px; background: var(--black); color: #fff; padding: 12px 24px; border-radius: 8px; font-size: 13px; z-index: 9999; opacity: 0; transition: opacity 0.3s;"></div>

{{-- ═══════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════ --}}
<script>
/* ─── Data barang dari server (sudah di-encode sebagai JSON) ─── */
const INV_BARANG  = @json($barangJson);
const IS_OWNER    = {{ $isOwner ? 'true' : 'false' }};
const CSRF_TOKEN  = '{{ csrf_token() }}';

/* ─── State halaman ─── */
let currentFilter   = 'Semua';
let selectedId      = null;   // id barang yang sedang dipilih di panel kanan
let localStokState  = {};     // cache stok yang sedang diedit (belum disimpan)
let localStatusState = null;

/* ═══════════════════════════════════════════
   FUNGSI TAB SWITCH
═══════════════════════════════════════════ */
function switchInvTab(tab) {
    ['kelola', 'tambah'].forEach(t => {
        const tabEl     = document.getElementById('tab' + cap(t));
        const contentEl = document.getElementById('content' + cap(t));
        if (!tabEl || !contentEl) return;
        tabEl.classList.toggle('active', t === tab);
        contentEl.classList.toggle('active', t === tab);
    });
}
const cap = s => s.charAt(0).toUpperCase() + s.slice(1);

/* ═══════════════════════════════════════════
   RENDER CHIPS FILTER
═══════════════════════════════════════════ */
const FILTERS = [
    { label: 'Semua',    count: {{ $totalBarang }} },
    { label: 'Tersedia', count: {{ $barangTersedia }} },
    { label: 'Disewa',   count: {{ $barangDisewa }} },
    { label: 'Laundry',  count: {{ $barangLaundry }} },
    { label: 'Rusak',    count: {{ $barangRusak }} },
];

function renderChips() {
    document.getElementById('invChips').innerHTML = FILTERS.map(f =>
        `<div class="inv-chip${f.label === currentFilter ? ' active' : ''}"
              onclick="setFilter('${f.label}')">
            ${f.label} (${f.count})
        </div>`
    ).join('');
}

function setFilter(label) {
    currentFilter = label;
    renderGrid();
}

/* ═══════════════════════════════════════════
   RENDER GRID KATALOG
═══════════════════════════════════════════ */
function renderGrid() {
    const query   = document.getElementById('invSearch').value.toLowerCase().trim();
    const grid    = document.getElementById('invGrid');

    // Filter barang sesuai chip aktif dan query pencarian
    const visible = INV_BARANG.filter(b => {
        const statusMatch =
            currentFilter === 'Semua'    ? true :
            currentFilter === 'Tersedia' ? b.status === 'Tersedia' :
            currentFilter === 'Disewa'   ? b.status === 'Disewa'   :
            currentFilter === 'Laundry'  ? b.status === 'Laundry'  :
            b.status === 'Rusak';
        const textMatch = !query ||
            b.nama.toLowerCase().includes(query) ||
            (b.ukuran || '').toLowerCase().includes(query);
        return statusMatch && textMatch;
    });

    if (!visible.length) {
        grid.innerHTML = `<div style="grid-column:1/-1;padding:48px;text-align:center;
            color:#bbb;font-size:12px">Tidak ada barang yang sesuai filter</div>`;
        renderChips();
        return;
    }

    /*
     * Setiap kartu menampilkan:
     * - Foto (jika ada) atau emoji 👘 sebagai placeholder
     * - Badge status di pojok kiri atas
     * - Checkmark di pojok kanan atas jika kartu ini yang sedang dipilih
     * - Nama, label ukuran, dan total stok
     */
    grid.innerHTML = visible.map(b => {
        const isSelected  = b.id === selectedId;
        const hasFoto     = b.foto && b.foto !== 'null' && b.foto !== '';
        const statusClass = b.status.toLowerCase().replace(' ', '-').replace('tersedia','tersedia')
                                    .replace('sedang disewa','disewa');
        const stokClass   = b.total_stok === 0 ? 'inv-stok-zero' : '';

        // Kita tampilkan stok terkini dari localStokState jika sedang diedit
        const displayStok = (isSelected && Object.keys(localStokState).length)
            ? Object.values(localStokState).reduce((a,v) => a + (parseInt(v)||0), 0)
            : b.total_stok;

        return `<div class="inv-card${isSelected ? ' selected' : ''}"
                     onclick="selectBarang(${b.id})">
            <div class="inv-card-img">
                ${hasFoto ? `<img src="/${b.foto}" onerror="this.style.display='none'">` : ''}
                <span style="font-size:32px${hasFoto ? ';display:none' : ''}"><i class='bi bi-bag-heart' style='color:var(--gold-dk);opacity:.5'></i></span>
                <div class="inv-status-badge ${b.status.toLowerCase()}">${b.status}</div>
            </div>
            <div class="inv-card-body">
                <div class="inv-card-nama">${b.nama}</div>
                <div class="inv-card-meta">${b.ukuran || 'Belum ada ukuran'}</div>
                <div class="inv-card-stok">
                    <span class="inv-stok-num ${stokClass}">${displayStok} pcs</span>
                    <span style="font-size:10px;color:#aaa">stok</span>
                </div>
            </div>
        </div>`;
    }).join('');

    renderChips();
}

/* ═══════════════════════════════════════════
   PILIH BARANG → ISI PANEL KANAN
═══════════════════════════════════════════ */
function selectBarang(id) {
    // Jika klik barang yang sama, batalkan seleksi
    if (selectedId === id) {
        selectedId      = null;
        localStokState  = {};
        localStatusState = null;
        showPanelEmpty();
        renderGrid();
        return;
    }

    selectedId = id;
    const b = INV_BARANG.find(x => x.id === id);
    if (!b) return;

    // Salin stok ke state lokal agar perubahan tidak langsung memodifikasi data asli
    localStokState  = { ...b.stok };
    localStatusState = b.status;

    // Isi header panel
    document.getElementById('panelNama').textContent = b.nama;
    document.getElementById('panelSub').textContent  =
        `#BB-${String(id).padStart(3,'0')} · Rp ${fmtRp(b.harga)} / hari`;
    document.getElementById('panelHarga').textContent = fmtRp(b.harga);

    // Set status dropdown
    document.getElementById('panelStatus').value = b.status;
    updateStatusColor();

    // Render baris stok per ukuran
    renderStokRows(b);

    // Tampilkan panel aktif
    showPanelActive();
    renderGrid(); // re-render untuk update tanda checkmark
}

/*
 * Render baris stok: satu baris per ukuran yang terdaftar.
 * Jika barang belum punya ukuran sama sekali, tampilkan pesan kosong.
 */
function renderStokRows(b) {
    const sizes = Object.keys(b.stok);
    const rows  = document.getElementById('stokRows');

    if (!sizes.length) {
        rows.innerHTML = `<div style="padding:16px;text-align:center;color:#aaa;font-size:12px">
            Barang ini belum memiliki data ukuran & stok.
        </div>`;
        updateTotalStok();
        return;
    }

    rows.innerHTML = sizes.map(uk => `
        <div class="stok-row">
            <div class="stok-size-lbl">Size ${uk}</div>
            <div class="stok-ctrl">
                <button class="stok-btn minus" type="button"
                        onclick="adjustStokLocal('${uk}', -1)">−</button>
                <input class="stok-input" type="number" min="0"
                       id="stok_${uk}"
                       value="${localStokState[uk] ?? 0}"
                       oninput="onStokInput('${uk}', this.value)">
                <button class="stok-btn" type="button"
                        onclick="adjustStokLocal('${uk}', 1)">+</button>
            </div>
        </div>
    `).join('');

    updateTotalStok();
}

/* Sesuaikan stok satu ukuran via tombol +/- */
function adjustStokLocal(ukuran, delta) {
    const input = document.getElementById(`stok_${ukuran}`);
    if (!input) return;
    const newVal = Math.max(0, (parseInt(input.value) || 0) + delta);
    input.value          = newVal;
    localStokState[ukuran] = newVal;
    updateTotalStok();
}

/* Sinkronkan saat nilai input diketik langsung */
function onStokInput(ukuran, rawVal) {
    localStokState[ukuran] = Math.max(0, parseInt(rawVal) || 0);
    updateTotalStok();
}

/* Hitung dan tampilkan total stok dari semua ukuran */
function updateTotalStok() {
    const total = Object.values(localStokState).reduce((a, v) => a + (parseInt(v) || 0), 0);
    document.getElementById('totalStokVal').textContent = `${total} pcs`;
}

/* Beri warna hint pada dropdown status */
function updateStatusColor() {
    const sel    = document.getElementById('panelStatus');
    const colors = {
        'Tersedia' : '#1a8050',
        'Disewa'   : 'var(--gold-dk)',
        'Laundry'  : '#2563eb',
        'Rusak'    : '#c0392b',
    };
    sel.style.color = colors[sel.value] || 'inherit';
    localStatusState = sel.value;
}

/* ═══════════════════════════════════════════
   SIMPAN STOK (hit endpoint adjustStok)
═══════════════════════════════════════════ */
function saveStok() {
    if (!selectedId) return;

    const btn    = document.getElementById('btnSaveStok');
    btn.disabled = true;
    btn.textContent = '⏳ Menyimpan…';

    const formData = new FormData();
    formData.append('_token', CSRF_TOKEN);
    formData.append('stok', JSON.stringify(localStokState));
    formData.append('status_barang', document.getElementById('panelStatus').value);

    fetch(`/barang/${selectedId}/stok`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Perbarui data in-memory agar tidak perlu reload halaman
            const idx = INV_BARANG.findIndex(b => b.id === selectedId);
            if (idx > -1) {
                INV_BARANG[idx].stok        = data.stok;
                INV_BARANG[idx].total_stok  = data.total_stok;
                INV_BARANG[idx].status      = data.status;
            }
            renderGrid();
            showToast('✅ Stok berhasil diperbarui');
        } else {
            showToast('❌ ' + (data.message || 'Gagal menyimpan'));
        }
    })
    .catch(() => showToast('❌ Terjadi kesalahan jaringan'))
    .finally(() => {
        btn.disabled    = false;
        btn.innerHTML = '<i class="bi bi-floppy2-fill"></i> Simpan Perubahan Stok';
    });
}

/* ═══════════════════════════════════════════
   HAPUS BARANG (Owner only)
═══════════════════════════════════════════ */
function deleteBarang() {
    if (!selectedId) return;
    const b = INV_BARANG.find(x => x.id === selectedId);
    if (!b) return;

    if (!confirm(`Hapus barang "${b.nama}"?\n\nTindakan ini tidak dapat dibatalkan.`)) return;

    fetch(`/barang/${selectedId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN'    : CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept'          : 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Hapus dari array in-memory dan reset panel
            const idx = INV_BARANG.findIndex(x => x.id === selectedId);
            if (idx > -1) INV_BARANG.splice(idx, 1);

            selectedId   = null;
            localStokState = {};
            showPanelEmpty();
            renderGrid();
            showToast('🗑️ Barang berhasil dihapus');
        } else {
            showToast('❌ ' + (data.message || 'Gagal menghapus'));
        }
    })
    .catch(() => showToast('❌ Terjadi kesalahan jaringan'));
}

/* ═══════════════════════════════════════════
   FORM TAMBAH BARANG (Tab 2, Owner only)
═══════════════════════════════════════════ */

/* Update label ukuran dan hidden stok JSON saat checkbox/input berubah */
function onUkuranChange() {
    const checkboxes = document.querySelectorAll('.ukuran-cb');
    const ukuranList = [];
    const stokObj    = {};

    checkboxes.forEach(cb => {
        const uk    = cb.dataset.ukuran;
        const input = document.querySelector(`.ukuran-stok-num[data-ukuran="${uk}"]`);
        if (!input) return;

        if (cb.checked) {
            input.disabled = false;
            const jumlah   = parseInt(input.value) || 0;
            if (jumlah > 0) {
                ukuranList.push(uk);
                stokObj[uk] = jumlah;
            }
        } else {
            input.disabled = true;
            input.value    = 0;
        }
    });

    document.getElementById('tambahUkuranLabel').value =
        ukuranList.length ? ukuranList.join(', ') : '';
    document.getElementById('tambahStokJson').value = JSON.stringify(stokObj);
}

function resetFormTambah() {
    document.getElementById('formTambah').reset();
    document.querySelectorAll('.ukuran-stok-num').forEach(i => {
        i.disabled = true;
        i.value    = 0;
    });
    document.getElementById('tambahUkuranLabel').value = '';
    document.getElementById('tambahStokJson').value    = '{}';
}

/* Submit form tambah barang via fetch (tanpa redirect) */
if (document.getElementById('formTambah')) {
    document.getElementById('formTambah').addEventListener('submit', function(e) {
        e.preventDefault();

        // Validasi: minimal ada satu ukuran dengan stok > 0
        const stokJson = document.getElementById('tambahStokJson').value;
        const stokObj  = JSON.parse(stokJson || '{}');
        if (!Object.keys(stokObj).length) {
            showToast('⚠️ Centang dan isi minimal satu ukuran');
            return;
        }

        const formData = new FormData(this);
        // Pastikan stok JSON dikirim dengan benar
        formData.set('stok', stokJson);
        formData.set('ukuran', document.getElementById('tambahUkuranLabel').value);

        fetch('{{ route("barang.store") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('✅ Barang baru berhasil ditambahkan');
                resetFormTambah();
                /*
                 * Reload halaman setelah 1.5 detik agar data INV_BARANG
                 * ter-refresh dari server. Alternatif yang lebih elegan
                 * adalah menambahkan barang baru ke array INV_BARANG secara
                 * langsung, tapi kita butuh id_barang dari server untuk itu.
                 */
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('❌ ' + (data.message || 'Gagal menyimpan'));
            }
        })
        .catch(() => showToast('❌ Terjadi kesalahan jaringan'));
    });
}

/* ═══════════════════════════════════════════
   HELPERS TAMPILAN
═══════════════════════════════════════════ */

function showPanelEmpty() {
    document.getElementById('panelEmpty').style.display  = 'flex';
    document.getElementById('panelActive').style.display = 'none';
}

function showPanelActive() {
    document.getElementById('panelEmpty').style.display  = 'none';
    document.getElementById('panelActive').style.display = 'flex';
}

/* Toast notifikasi singkat yang auto-dismiss setelah 2.5 detik */
function showToast(msg) {
    const toast = document.getElementById('invToast');
    toast.textContent  = msg;
    toast.style.display   = 'flex';
    toast.style.opacity   = '1';
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 300);
    }, 2500);
}

/* Format angka ke Rupiah tanpa desimal */
const fmtRp = n => parseInt(n).toLocaleString('id-ID');

/* ═══════════════════════════════════════════
   INISIALISASI
═══════════════════════════════════════════ */
renderChips();
renderGrid();
</script>

@endsection