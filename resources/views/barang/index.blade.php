@extends('layouts.app')

@section('title', 'Inventaris & Stok')
@section('breadcrumb', 'Inventaris & Stok')

@section('content')

@php
    $isOwner = session('user')['role'] === 'Owner';
@endphp

<style>
/* ══════════════════════════════════════════════
   PAGE WRAPPER
══════════════════════════════════════════════ */
.inv-page {
    display: flex;
    flex-direction: column;
    gap: 0;
    margin: -24px;
    height: calc(100vh - 52px);
    overflow: hidden;
}

/* ══════════════════════════════════════════════
   TOOLBAR — Header bar
══════════════════════════════════════════════ */
.inv-toolbar {
    background: #fff;
    border-bottom: 1px solid var(--gray-200);
    padding: 10px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

/* Search */
.inv-search-wrap {
    display: flex;
    align-items: center;
    gap: 7px;
    background: var(--gray-50);
    border: 1.5px solid var(--gray-200);
    border-radius: 9px;
    padding: 0 11px;
    transition: .18s;
    flex: 1;
    max-width: 320px;
}
.inv-search-wrap:focus-within {
    border-color: var(--gold);
    background: #fff;
    box-shadow: 0 0 0 3px var(--gold-xs);
}
.inv-search-wrap input {
    border: none;
    background: transparent;
    outline: none;
    padding: 8px 0;
    font-size: 12.5px;
    font-family: var(--ff);
    color: var(--black);
    flex: 1;
}
.inv-search-wrap input::placeholder { color: #bbb; }

/* Stat chips in toolbar */
.inv-stat-chips { display: flex; gap: 5px; }
.inv-stat-chip {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    border: 1.5px solid var(--gray-200);
    background: #fff;
    color: var(--gray-500);
    cursor: pointer;
    transition: .12s;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 5px;
}
.inv-stat-chip:hover { border-color: var(--gold-rim); color: var(--gold-dk); }
.inv-stat-chip.active { background: var(--black); border-color: var(--black); color: var(--gold-lt); }
.inv-stat-chip .chip-count {
    background: rgba(255,255,255,.18);
    border-radius: 10px;
    padding: 0 5px;
    font-size: 9.5px;
    font-weight: 700;
}
.inv-stat-chip.active .chip-count { background: rgba(255,255,255,.2); color: #fff; }

.inv-toolbar-right { margin-left: auto; display: flex; gap: 8px; align-items: center; }

/* ══════════════════════════════════════════════
   PRODUCT GRID AREA
══════════════════════════════════════════════ */
.inv-body {
    flex: 1;
    overflow-y: auto;
    background: #f5f5f3;
    padding: 16px 18px 24px;
}
.inv-body::-webkit-scrollbar { width: 5px; }
.inv-body::-webkit-scrollbar-thumb { background: var(--gray-300); border-radius: 3px; }

/* 6-column grid */
.inv-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
}

/* ══════════════════════════════════════════════
   PRODUCT CARD
══════════════════════════════════════════════ */
.pcard {
    background: #fff;
    border: 1.5px solid rgba(0,0,0,.08);
    border-radius: 10px;
    overflow: hidden;
    position: relative;
    cursor: default;
    transition: border-color .18s, box-shadow .18s, transform .18s;
}
.pcard:hover {
    border-color: var(--gold-rim);
    box-shadow: 0 4px 18px rgba(0,0,0,.09);
    transform: translateY(-2px);
}

/* Image zone */
.pcard-img {
    position: relative;
    aspect-ratio: 3/4;
    background: linear-gradient(135deg, #faf5e8, #f0e8d0);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.pcard-img img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .3s ease;
}
.pcard:hover .pcard-img img { transform: scale(1.04); }
.pcard-img-placeholder {
    font-size: 30px;
    opacity: .35;
    color: var(--gold-dk);
}

/* Stock badge — top right */
.pcard-stok-badge {
    position: absolute;
    top: 7px;
    right: 7px;
    background: rgba(10,10,10,.72);
    backdrop-filter: blur(4px);
    color: #fff;
    font-size: 9px;
    font-weight: 700;
    padding: 2.5px 7px;
    border-radius: 8px;
    letter-spacing: .3px;
    z-index: 2;
}
.pcard-stok-badge.stok-0 { background: rgba(192,57,43,.78); }

/* Hover action buttons — appear on hover */
.pcard-actions {
    position: absolute;
    top: 7px;
    left: 7px;
    display: flex;
    gap: 5px;
    opacity: 0;
    transform: translateY(-4px);
    transition: opacity .18s, transform .18s;
    z-index: 3;
}
.pcard:hover .pcard-actions {
    opacity: 1;
    transform: translateY(0);
}
.pcard-action-btn {
    width: 28px;
    height: 28px;
    border-radius: 7px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    cursor: pointer;
    transition: .12s;
    backdrop-filter: blur(6px);
}
.pcard-action-btn.edit-btn {
    background: rgba(255,255,255,.92);
    color: var(--gold-dk);
    box-shadow: 0 1px 6px rgba(0,0,0,.18);
}
.pcard-action-btn.edit-btn:hover { background: var(--gold); color: var(--black); }
.pcard-action-btn.del-btn {
    background: rgba(255,255,255,.92);
    color: #c0392b;
    box-shadow: 0 1px 6px rgba(0,0,0,.18);
}
.pcard-action-btn.del-btn:hover { background: #c0392b; color: #fff; }

/* Card body */
.pcard-body { padding: 9px 11px 11px; }
.pcard-ukuran {
    display: inline-block;
    font-size: 9px;
    font-weight: 700;
    color: var(--gold-dk);
    background: var(--gold-xs);
    border: 1px solid var(--gold-md);
    border-radius: 4px;
    padding: 1.5px 6px;
    margin-bottom: 4px;
    letter-spacing: .4px;
    text-transform: uppercase;
}
.pcard-name {
    font-size: 11.5px;
    font-weight: 700;
    color: var(--black);
    line-height: 1.35;
    margin-bottom: 3px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.pcard-barcode {
    font-size: 9px;
    color: #bbb;
    font-family: var(--ff-mono);
    margin-bottom: 6px;
    letter-spacing: .5px;
}
.pcard-price {
    font-size: 13px;
    font-weight: 800;
    color: var(--gold-dk);
    font-family: var(--ff-mono);
}
.pcard-price small {
    font-size: 9px;
    font-weight: 400;
    color: #bbb;
    font-family: var(--ff);
}

/* Status indicator stripe at bottom */
.pcard-status-stripe {
    height: 2.5px;
    width: 100%;
}
.stripe-tersedia { background: linear-gradient(90deg, #2da66e, #52c896); }
.stripe-disewa   { background: linear-gradient(90deg, var(--gold-dk), var(--gold)); }
.stripe-laundry  { background: linear-gradient(90deg, #2563eb, #60a5fa); }
.stripe-rusak    { background: linear-gradient(90deg, #c0392b, #e74c3c); }

/* ══════════════════════════════════════════════
   EMPTY STATE
══════════════════════════════════════════════ */
.inv-empty {
    grid-column: 1 / -1;
    padding: 60px 20px;
    text-align: center;
    color: #bbb;
}
.inv-empty-ico { font-size: 40px; opacity: .3; margin-bottom: 12px; display: block; }

/* ══════════════════════════════════════════════
   MODALS
══════════════════════════════════════════════ */
.inv-modal-ov {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    backdrop-filter: blur(5px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    opacity: 0;
    visibility: hidden;
    transition: all .22s ease;
}
.inv-modal-ov.show { opacity: 1; visibility: visible; }

/* ── EDIT MODAL — wide with profile on right ── */
.edit-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 760px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transform: scale(.94) translateY(10px);
    transition: transform .24s cubic-bezier(.34,1.4,.64,1);
    box-shadow: 0 24px 60px rgba(0,0,0,.22);
}
.inv-modal-ov.show .edit-modal { transform: scale(1) translateY(0); }

.edit-modal-head {
    background: var(--black);
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.edit-modal-title { font-size: 14px; font-weight: 600; color: var(--gold-lt); }
.edit-modal-sub   { font-size: 11px; color: rgba(255,255,255,.3); margin-top: 2px; }
.modal-x {
    width: 28px; height: 28px;
    border-radius: 7px;
    border: 1px solid rgba(255,255,255,.12);
    background: transparent;
    color: rgba(255,255,255,.4);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    transition: .12s;
}
.modal-x:hover { background: rgba(255,255,255,.08); color: rgba(255,255,255,.8); }

/* Two-column body */
.edit-modal-body {
    display: grid;
    grid-template-columns: 1fr 230px;
    flex: 1;
    overflow: hidden;
}

/* Left: form */
.edit-form-col {
    padding: 18px 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 13px;
}
.edit-form-col::-webkit-scrollbar { width: 3px; }
.edit-form-col::-webkit-scrollbar-thumb { background: var(--gray-200); }

/* Right: profile card */
.edit-profile-col {
    border-left: 1px solid var(--gray-100);
    background: var(--gray-50);
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px 16px;
    gap: 12px;
    overflow-y: auto;
}
.edit-profile-img {
    width: 130px;
    height: 160px;
    border-radius: 10px;
    background: linear-gradient(135deg, #faf5e8, #f0e8d0);
    overflow: hidden;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--gold-md);
    flex-shrink: 0;
}
.edit-profile-img img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.edit-profile-name {
    font-size: 12.5px;
    font-weight: 700;
    color: var(--black);
    text-align: center;
    line-height: 1.35;
}
.edit-profile-price {
    font-size: 15px;
    font-weight: 800;
    color: var(--gold-dk);
    font-family: var(--ff-mono);
    text-align: center;
}
.edit-profile-price small {
    font-size: 10px;
    font-weight: 400;
    color: #aaa;
    font-family: var(--ff);
}
.edit-profile-status {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 10.5px;
    font-weight: 700;
}
.ep-tersedia { background: rgba(26,128,80,.1); color: #1a8050; border: 1px solid rgba(26,128,80,.2); }
.ep-disewa   { background: var(--gold-xs); color: var(--gold-dk); border: 1px solid var(--gold-md); }
.ep-laundry  { background: rgba(37,99,235,.08); color: #2563eb; border: 1px solid rgba(37,99,235,.2); }
.ep-rusak    { background: rgba(192,57,43,.08); color: #c0392b; border: 1px solid rgba(192,57,43,.2); }

.edit-profile-divider {
    width: 100%;
    border: none;
    border-top: 1px solid var(--gray-200);
    margin: 4px 0;
}

/* Stok per ukuran (profile side) */
.ep-stok-grid { width: 100%; display: flex; flex-direction: column; gap: 5px; }
.ep-stok-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 9px;
    background: #fff;
    border-radius: 6px;
    border: 1px solid var(--gray-200);
}
.ep-stok-label { font-size: 11px; font-weight: 700; color: var(--black); }
.ep-stok-val   { font-size: 11px; font-weight: 700; color: var(--gold-dk); font-family: var(--ff-mono); }

/* Form fields */
.f-group { display: flex; flex-direction: column; gap: 4px; }
.f-label {
    font-size: 10.5px;
    font-weight: 700;
    color: var(--gray-600);
    text-transform: uppercase;
    letter-spacing: .5px;
}
.f-inp, .f-sel {
    width: 100%;
    padding: 8px 11px;
    border: 1.5px solid var(--gray-200);
    border-radius: 8px;
    font-size: 13px;
    font-family: var(--ff);
    color: var(--black);
    background: #fff;
    outline: none;
    transition: .16s;
}
.f-inp:focus, .f-sel:focus { border-color: var(--gold); box-shadow: 0 0 0 3px var(--gold-xs); }
.f-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

/* Stok per ukuran in form */
.stok-edit-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
.stok-edit-item {
    background: var(--gray-50);
    border: 1.5px solid var(--gray-200);
    border-radius: 8px;
    padding: 10px 8px;
    text-align: center;
    transition: .15s;
}
.stok-edit-item:focus-within { border-color: var(--gold); background: var(--gold-xs); }
.stok-edit-size { font-size: 11px; font-weight: 700; color: var(--gray-600); margin-bottom: 7px; }
.stok-edit-input {
    width: 100%;
    text-align: center;
    border: 1.5px solid var(--gray-200);
    border-radius: 6px;
    padding: 5px 2px;
    font-size: 14px;
    font-family: var(--ff-mono);
    font-weight: 700;
    color: var(--gold-dk);
    background: #fff;
    outline: none;
    -moz-appearance: textfield;
}
.stok-edit-input::-webkit-outer-spin-button,
.stok-edit-input::-webkit-inner-spin-button { -webkit-appearance: none; }
.stok-edit-input:focus { border-color: var(--gold); }

/* Modal footer */
.edit-modal-foot {
    padding: 12px 20px;
    border-top: 1px solid var(--gray-100);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-shrink: 0;
    background: var(--gray-50);
}
.edit-modal-foot-left { display: flex; gap: 8px; }

/* ── ADD MODAL ── */
.add-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 540px;
    max-height: 88vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transform: scale(.94) translateY(10px);
    transition: transform .24s cubic-bezier(.34,1.4,.64,1);
    box-shadow: 0 24px 60px rgba(0,0,0,.22);
}
.inv-modal-ov.show .add-modal { transform: scale(1) translateY(0); }
.add-modal-body {
    padding: 18px 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 13px;
}
.add-modal-body::-webkit-scrollbar { width: 3px; }
.add-modal-body::-webkit-scrollbar-thumb { background: var(--gray-200); }

/* Add stok grid */
.add-stok-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
.add-stok-item {
    background: var(--gray-50);
    border: 1.5px solid var(--gray-200);
    border-radius: 8px;
    padding: 10px 8px;
    text-align: center;
    transition: .15s;
}
.add-stok-item:focus-within { border-color: var(--gold); background: var(--gold-xs); }
.add-stok-size { font-size: 11px; font-weight: 700; color: var(--gray-600); margin-bottom: 5px; display: flex; align-items: center; justify-content: center; gap: 5px; }
.add-stok-size input[type="checkbox"] { accent-color: var(--gold); cursor: pointer; }
.add-stok-num {
    width: 100%;
    text-align: center;
    border: 1.5px solid var(--gray-200);
    border-radius: 6px;
    padding: 5px 2px;
    font-size: 14px;
    font-family: var(--ff-mono);
    font-weight: 700;
    color: var(--gold-dk);
    background: #f5f5f3;
    outline: none;
}
.add-stok-num:not(:disabled) { background: #fff; }
.add-stok-num:disabled { opacity: .3; cursor: not-allowed; }
.add-stok-num:focus { border-color: var(--gold); }

/* ── TOAST ── */
.inv-toast {
    position: fixed;
    bottom: 22px;
    right: 22px;
    z-index: 2000;
    background: var(--black);
    color: #fff;
    border: 1px solid var(--gold-rim);
    border-radius: 10px;
    padding: 11px 18px;
    font-size: 12.5px;
    font-weight: 600;
    box-shadow: 0 8px 24px rgba(0,0,0,.22);
    opacity: 0;
    transform: translateY(8px);
    pointer-events: none;
    transition: all .22s ease;
    max-width: 280px;
}
.inv-toast.show { opacity: 1; transform: translateY(0); }

/* Photo upload preview */
.photo-preview-box {
    width: 100%;
    height: 100px;
    border: 2px dashed var(--gray-300);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    background: var(--gray-50);
    transition: .15s;
    overflow: hidden;
    position: relative;
}
.photo-preview-box:hover { border-color: var(--gold-rim); background: var(--gold-xs); }
.photo-preview-box img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
}
.photo-preview-placeholder { color: var(--gray-400); font-size: 12px; display: flex; flex-direction: column; align-items: center; gap: 4px; }

/* Responsive */
@media (max-width: 1200px) {
    .inv-grid { grid-template-columns: repeat(5, 1fr); }
}
@media (max-width: 960px) {
    .inv-grid { grid-template-columns: repeat(4, 1fr); }
}
@media (max-width: 720px) {
    .inv-grid { grid-template-columns: repeat(3, 1fr); }
    .edit-modal-body { grid-template-columns: 1fr; }
    .edit-profile-col { border-left: none; border-top: 1px solid var(--gray-100); }
}
@media (max-width: 500px) {
    .inv-grid { grid-template-columns: repeat(2, 1fr); }
    .inv-stat-chips { display: none; }
}
</style>

{{-- ══════════════════════════════════════════
     PAGE STRUCTURE
══════════════════════════════════════════ --}}
<div class="inv-page">

    {{-- ── TOOLBAR ── --}}
    <div class="inv-toolbar">
        {{-- Search --}}
        <div class="inv-search-wrap">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="2.5">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input id="invSearch" placeholder="Cari nama barang atau ukuran…" oninput="renderGrid()">
        </div>

        {{-- Filter chips --}}
        <div class="inv-stat-chips" id="invChips"></div>

        {{-- Right actions --}}
        <div class="inv-toolbar-right">
            @if($isOwner)
            <button class="btn-gold" onclick="openAddModal()">
                <i class="bi bi-plus-circle-fill"></i> Tambah Produk
            </button>
            @endif
        </div>
    </div>

    {{-- ── PRODUCT GRID ── --}}
    <div class="inv-body">
        <div class="inv-grid" id="invGrid"></div>
    </div>
</div>

{{-- ════ EDIT MODAL ════ --}}
<div class="inv-modal-ov" id="editOv" onclick="if(event.target===this)closeEditModal()">
    <div class="edit-modal">
        <div class="edit-modal-head">
            <div>
                <div class="edit-modal-title" id="editModalTitle">Edit Barang</div>
                <div class="edit-modal-sub" id="editModalSub">Perbarui data & stok</div>
            </div>
            <button class="modal-x" onclick="closeEditModal()"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="edit-modal-body">

            {{-- LEFT: form --}}
            <div class="edit-form-col">
                <form id="editForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="editId" name="_id" value="">
                <input type="hidden" id="editMethod" name="_method" value="PUT">
                <input type="hidden" id="editHapusFoto" name="hapus_foto" value="0">
                <input type="hidden" id="editStokJson" name="stok" value="{}">
                <input type="hidden" id="editUkuranLabel" name="ukuran" value="">

                <div style="display:flex;flex-direction:column;gap:12px">
                    <div class="f-group">
                        <label class="f-label">Nama Barang *</label>
                        <input type="text" class="f-inp" id="editNama" name="nama_barang" placeholder="Nama barang" required>
                    </div>
                    <div class="f-grid2">
                        <div class="f-group">
                            <label class="f-label">Harga Sewa / Hari *</label>
                            <input type="number" class="f-inp" id="editHarga" name="harga_sewa"
                                   placeholder="200000" min="0" step="1000" required
                                   oninput="updateProfilePrice()">
                        </div>
                        <div class="f-group">
                            <label class="f-label">Status Barang</label>
                            <select class="f-sel" id="editStatus" name="status_barang" onchange="updateProfileStatus()">
                                <option value="Tersedia">Tersedia</option>
                                <option value="Disewa">Sedang Disewa</option>
                                <option value="Laundry">Laundry</option>
                                <option value="Rusak">Rusak / Perbaikan</option>
                            </select>
                        </div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Stok per Ukuran</label>
                        <div class="stok-edit-grid" id="editStokGrid"></div>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Foto Barang</label>
                        <div class="photo-preview-box" id="editPhotoPrev"
                             onclick="document.getElementById('editFotoFile').click()">
                            <div class="photo-preview-placeholder" id="editPhotoPlaceholder">
                                <i class="bi bi-cloud-arrow-up" style="font-size:22px;color:#ccc"></i>
                                <span>Klik untuk ganti foto</span>
                            </div>
                        </div>
                        <input type="file" id="editFotoFile" name="foto"
                               accept="image/jpeg,image/png,image/jpg"
                               style="display:none" onchange="previewEditFoto(this)">
                        <button type="button" id="editDelFotoBtn"
                                onclick="hapusEditFoto()"
                                style="display:none;margin-top:5px;background:transparent;border:1px solid rgba(220,52,52,.3);border-radius:7px;padding:5px 10px;font-size:11px;color:#c0392b;cursor:pointer;font-family:var(--ff)">
                            <i class="bi bi-trash3"></i> Hapus Foto
                        </button>
                    </div>
                </div>
                </form>
            </div>

            {{-- RIGHT: product profile --}}
            <div class="edit-profile-col">
                <div class="edit-profile-img" id="profileImgWrap">
                    <div class="pcard-img-placeholder"><i class="bi bi-bag-heart"></i></div>
                </div>
                <div class="edit-profile-name" id="profileName">—</div>
                <div class="edit-profile-price" id="profilePrice">—</div>
                <div class="edit-profile-status ep-tersedia" id="profileStatus">Tersedia</div>
                <hr class="edit-profile-divider">
                <div style="font-size:9.5px;font-weight:700;color:var(--gray-400);text-transform:uppercase;letter-spacing:.8px;align-self:flex-start">Stok per Ukuran</div>
                <div class="ep-stok-grid" id="profileStokGrid"></div>
                <hr class="edit-profile-divider">
                <div style="text-align:center">
                    <div style="font-size:9px;color:#bbb;margin-bottom:3px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Total Stok</div>
                    <div id="profileTotalStok" style="font-size:22px;font-weight:800;color:var(--gold-dk);font-family:var(--ff-mono)">0</div>
                    <div style="font-size:9.5px;color:#bbb">pcs</div>
                </div>
            </div>
        </div>

        <div class="edit-modal-foot">
            <div class="edit-modal-foot-left">
                @if($isOwner)
                <button type="button" class="btn-white" id="editDeleteBtn" onclick="deleteBarangFromModal()"
                        style="color:#c0392b;border-color:rgba(220,52,52,.3)">
                    <i class="bi bi-trash3"></i> Hapus Barang
                </button>
                @endif
            </div>
            <div style="display:flex;gap:8px">
                <button type="button" class="btn-white" onclick="closeEditModal()">Batal</button>
                <button type="button" class="btn-gold" onclick="saveEditBarang()">
                    <i class="bi bi-floppy2-fill"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ════ ADD MODAL (Owner only) ════ --}}
@if($isOwner)
<div class="inv-modal-ov" id="addOv" onclick="if(event.target===this)closeAddModal()">
    <div class="add-modal">
        <div class="edit-modal-head">
            <div>
                <div class="edit-modal-title">Tambah Produk Baru</div>
                <div class="edit-modal-sub">Produk baru otomatis berstatus Tersedia</div>
            </div>
            <button class="modal-x" onclick="closeAddModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="add-modal-body">
            <form id="addForm" enctype="multipart/form-data">
            @csrf
            <div style="display:flex;flex-direction:column;gap:13px">
                <div class="f-group">
                    <label class="f-label">Nama Barang *</label>
                    <input type="text" class="f-inp" name="nama_barang" id="addNama" placeholder="Contoh: Baju Bodo Sutra Hijau" required>
                </div>
                <div class="f-grid2">
                    <div class="f-group">
                        <label class="f-label">Harga Sewa / Hari *</label>
                        <input type="number" class="f-inp" name="harga_sewa" id="addHarga" placeholder="200000" min="0" step="1000" required>
                    </div>
                    <div class="f-group">
                        <label class="f-label">Label Ukuran</label>
                        <input type="text" class="f-inp" id="addUkuranLabel" name="ukuran" placeholder="Otomatis dari centang"
                               readonly style="background:var(--gray-50);color:var(--gray-500)">
                    </div>
                </div>
                <div class="f-group">
                    <label class="f-label">Stok Awal per Ukuran *</label>
                    <div class="add-stok-grid">
                        @foreach(['S','M','L','XL'] as $uk)
                        <div class="add-stok-item">
                            <div class="add-stok-size">
                                <input type="checkbox" class="add-ukuran-cb" data-ukuran="{{ $uk }}" onchange="onAddUkuranChange()">
                                {{ $uk }}
                            </div>
                            <input type="number" class="add-stok-num" data-ukuran="{{ $uk }}"
                                   value="0" min="0" disabled oninput="onAddUkuranChange()">
                        </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="stok" id="addStokJson" value="{}">
                </div>
                <div class="f-group">
                    <label class="f-label">Foto Barang</label>
                    <div class="photo-preview-box" id="addPhotoPrev"
                         onclick="document.getElementById('addFotoFile').click()">
                        <div class="photo-preview-placeholder">
                            <i class="bi bi-cloud-arrow-up" style="font-size:22px;color:#ccc"></i>
                            <span>Klik untuk upload foto (opsional)</span>
                        </div>
                    </div>
                    <input type="file" id="addFotoFile" name="foto" accept="image/jpeg,image/png,image/jpg"
                           style="display:none" onchange="previewAddFoto(this)">
                </div>
            </div>
            </form>
        </div>
        <div class="edit-modal-foot">
            <div></div>
            <div style="display:flex;gap:8px">
                <button type="button" class="btn-white" onclick="closeAddModal()">Batal</button>
                <button type="button" class="btn-gold" onclick="saveAddBarang()">
                    <i class="bi bi-plus-circle-fill"></i> Simpan Produk
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Toast --}}
<div class="inv-toast" id="invToast"></div>

{{-- ════════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════ --}}
<script>
/* ─── Server data ─── */
const INV_BARANG  = @json($barangJson);
const IS_OWNER    = {{ $isOwner ? 'true' : 'false' }};
const CSRF_TOKEN  = '{{ csrf_token() }}';

/* ─── State ─── */
let currentFilter = 'Semua';
let editingId     = null;

/* ─── Filter definitions ─── */
const FILTERS = [
    { label: 'Semua',    count: {{ $totalBarang }},    cls: '' },
    { label: 'Tersedia', count: {{ $barangTersedia }}, cls: '' },
    { label: 'Disewa',   count: {{ $barangDisewa }},   cls: '' },
    { label: 'Laundry',  count: {{ $barangLaundry }},  cls: '' },
    { label: 'Rusak',    count: {{ $barangRusak }},     cls: '' },
];

/* ═══════════════════════════════════════
   CHIPS
═══════════════════════════════════════ */
function renderChips() {
    document.getElementById('invChips').innerHTML = FILTERS.map(f =>
        `<div class="inv-stat-chip${f.label === currentFilter ? ' active' : ''}"
              onclick="setFilter('${f.label}')">
            ${f.label}
            <span class="chip-count">${f.count}</span>
        </div>`
    ).join('');
}

function setFilter(label) {
    currentFilter = label;
    renderChips();
    renderGrid();
}

/* ═══════════════════════════════════════
   PRODUCT GRID
═══════════════════════════════════════ */
const fmtRp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

function renderGrid() {
    const q     = document.getElementById('invSearch').value.toLowerCase().trim();
    const grid  = document.getElementById('invGrid');

    const visible = INV_BARANG.filter(b => {
        if (currentFilter !== 'Semua' && b.status !== currentFilter) return false;
        if (q && !b.nama.toLowerCase().includes(q) && !(b.ukuran||'').toLowerCase().includes(q)) return false;
        return true;
    });

    if (!visible.length) {
        grid.innerHTML = `<div class="inv-empty">
            <span class="inv-empty-ico"><i class="bi bi-search"></i></span>
            <div style="font-size:14px;font-weight:600;color:var(--black);margin-bottom:5px">Tidak ada produk ditemukan</div>
            <div style="font-size:12px">Coba ubah filter atau kata kunci pencarian</div>
        </div>`;
        return;
    }

    const stripeClass = s => ({Tersedia:'stripe-tersedia',Disewa:'stripe-disewa',Laundry:'stripe-laundry',Rusak:'stripe-rusak'})[s]||'stripe-tersedia';
    const totalStok   = b => Object.values(b.stok).reduce((a,v)=>a+(parseInt(v)||0),0);
    const hasFoto     = b => b.foto && b.foto !== 'null' && b.foto.trim() !== '';
    const barcodeId   = id => 'BB-'+String(id).padStart(4,'0');

    grid.innerHTML = visible.map(b => {
        const stok = totalStok(b);
        return `<div class="pcard">
            <div class="pcard-status-stripe ${stripeClass(b.status)}"></div>

            <div class="pcard-img">
                ${hasFoto(b)
                    ? `<img src="/${b.foto}" alt="${b.nama}" onerror="this.style.display='none'">`
                    : `<div class="pcard-img-placeholder"><i class="bi bi-bag-heart"></i></div>`
                }
                <div class="pcard-stok-badge${stok===0?' stok-0':''}">Stok: ${stok}</div>

                <!-- Hover actions -->
                <div class="pcard-actions">
                    <button class="pcard-action-btn edit-btn" title="Edit"
                            onclick="openEditModal(${b.id})">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    ${IS_OWNER ? `<button class="pcard-action-btn del-btn" title="Hapus"
                            onclick="deleteBarang(${b.id},'${b.nama.replace(/'/g,"\\'")}')">
                        <i class="bi bi-trash3-fill"></i>
                    </button>` : ''}
                </div>
            </div>

            <div class="pcard-body">
                <div class="pcard-ukuran">${b.ukuran||'—'}</div>
                <div class="pcard-name">${b.nama}</div>
                <div class="pcard-barcode">${barcodeId(b.id)}</div>
                <div class="pcard-price">${fmtRp(b.harga)}<small>/hari</small></div>
            </div>
        </div>`;
    }).join('');
}

/* ═══════════════════════════════════════
   EDIT MODAL
═══════════════════════════════════════ */
function openEditModal(id) {
    const b = INV_BARANG.find(x => x.id === id);
    if (!b) return;
    editingId = id;

    /* — header — */
    document.getElementById('editModalTitle').textContent = b.nama;
    document.getElementById('editModalSub').textContent   = 'ID Barang: #BB-'+String(id).padStart(4,'0');

    /* — form fields — */
    document.getElementById('editId').value     = id;
    document.getElementById('editNama').value   = b.nama;
    document.getElementById('editHarga').value  = b.harga;
    document.getElementById('editStatus').value = b.status;
    document.getElementById('editHapusFoto').value = '0';

    /* — foto preview — */
    const hasFoto = b.foto && b.foto !== 'null' && b.foto.trim() !== '';
    const prevBox = document.getElementById('editPhotoPrev');
    const placeholder = document.getElementById('editPhotoPlaceholder');
    const delBtn  = document.getElementById('editDelFotoBtn');
    prevBox.style.backgroundImage = '';
    placeholder.style.display = 'flex';
    // Remove old preview img if any
    const oldImg = prevBox.querySelector('img');
    if (oldImg) oldImg.remove();

    if (hasFoto) {
        const img = document.createElement('img');
        img.src   = '/' + b.foto;
        img.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:6px';
        prevBox.appendChild(img);
        placeholder.style.display = 'none';
        delBtn.style.display = 'block';
    } else {
        delBtn.style.display = 'none';
    }
    document.getElementById('editFotoFile').value = '';

    /* — stok grid (form) — */
    const sizes = Object.keys(b.stok);
    const stokGrid = document.getElementById('editStokGrid');
    if (sizes.length) {
        stokGrid.innerHTML = sizes.map(s =>
            `<div class="stok-edit-item">
                <div class="stok-edit-size">Size ${s}</div>
                <input class="stok-edit-input" type="number" min="0"
                       id="eStok_${s}" data-size="${s}"
                       value="${b.stok[s]||0}"
                       oninput="syncEditStok()">
            </div>`
        ).join('');
    } else {
        stokGrid.innerHTML = '<div style="color:#bbb;font-size:11px;padding:8px;grid-column:1/-1">Belum ada data ukuran</div>';
    }

    /* — profile card (right) — */
    updateProfileCard(b, b.harga, b.status);

    document.getElementById('editOv').classList.add('show');
}

function updateProfileCard(b, harga, status) {
    const hasFoto = b.foto && b.foto !== 'null' && b.foto.trim() !== '';

    /* image */
    const wrap = document.getElementById('profileImgWrap');
    wrap.innerHTML = hasFoto
        ? `<img src="/${b.foto}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover">`
        : `<div class="pcard-img-placeholder" style="font-size:36px"><i class="bi bi-bag-heart"></i></div>`;

    document.getElementById('profileName').textContent = b.nama;
    document.getElementById('profilePrice').innerHTML  =
        fmtRp(harga||b.harga) + '<small>/hari</small>';

    /* status badge */
    const statusEl = document.getElementById('profileStatus');
    const cls = {Tersedia:'ep-tersedia',Disewa:'ep-disewa',Laundry:'ep-laundry',Rusak:'ep-rusak'};
    statusEl.className = 'edit-profile-status ' + (cls[status||b.status]||'ep-tersedia');
    statusEl.textContent = status || b.status;

    /* stok per ukuran (profile) */
    syncEditStok();
}

function syncEditStok() {
    if (!editingId) return;
    const b = INV_BARANG.find(x => x.id === editingId);
    if (!b) return;

    const inputs = document.querySelectorAll('.stok-edit-input');
    const stokObj = {};
    let total = 0;
    inputs.forEach(inp => {
        const s = inp.dataset.size;
        const v = Math.max(0, parseInt(inp.value)||0);
        stokObj[s] = v;
        total += v;
    });

    /* update hidden JSON */
    document.getElementById('editStokJson').value = JSON.stringify(stokObj);
    document.getElementById('editUkuranLabel').value = Object.keys(stokObj).join(', ');

    /* update profile stok grid */
    const profileGrid = document.getElementById('profileStokGrid');
    profileGrid.innerHTML = Object.entries(stokObj).map(([s,v]) =>
        `<div class="ep-stok-row">
            <span class="ep-stok-label">Size ${s}</span>
            <span class="ep-stok-val">${v} pcs</span>
        </div>`
    ).join('');
    document.getElementById('profileTotalStok').textContent = total;
}

function updateProfilePrice() {
    const harga = parseInt(document.getElementById('editHarga').value)||0;
    document.getElementById('profilePrice').innerHTML = fmtRp(harga) + '<small>/hari</small>';
}

function updateProfileStatus() {
    const status = document.getElementById('editStatus').value;
    const statusEl = document.getElementById('profileStatus');
    const cls = {Tersedia:'ep-tersedia',Disewa:'ep-disewa',Laundry:'ep-laundry',Rusak:'ep-rusak'};
    statusEl.className = 'edit-profile-status ' + (cls[status]||'ep-tersedia');
    statusEl.textContent = status;
}

function previewEditFoto(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const prevBox = document.getElementById('editPhotoPrev');
        const placeholder = document.getElementById('editPhotoPlaceholder');
        let img = prevBox.querySelector('img');
        if (!img) { img = document.createElement('img'); img.style.cssText='position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:6px'; prevBox.appendChild(img); }
        img.src = e.target.result;
        placeholder.style.display = 'none';
        document.getElementById('editDelFotoBtn').style.display = 'block';
        document.getElementById('editHapusFoto').value = '0';

        /* update profile card image */
        const wrap = document.getElementById('profileImgWrap');
        let profImg = wrap.querySelector('img');
        if (!profImg) { profImg = document.createElement('img'); profImg.style.cssText='position:absolute;inset:0;width:100%;height:100%;object-fit:cover'; wrap.appendChild(profImg); }
        profImg.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}

function hapusEditFoto() {
    const prevBox = document.getElementById('editPhotoPrev');
    const img = prevBox.querySelector('img');
    if (img) img.remove();
    document.getElementById('editPhotoPlaceholder').style.display = 'flex';
    document.getElementById('editDelFotoBtn').style.display = 'none';
    document.getElementById('editHapusFoto').value = '1';
    document.getElementById('editFotoFile').value = '';

    /* clear profile card image */
    const wrap = document.getElementById('profileImgWrap');
    wrap.innerHTML = `<div class="pcard-img-placeholder" style="font-size:36px"><i class="bi bi-bag-heart"></i></div>`;
}

function closeEditModal() {
    document.getElementById('editOv').classList.remove('show');
    editingId = null;
}

function saveEditBarang() {
    if (!editingId) return;

    const form = document.getElementById('editForm');
    const fd   = new FormData();

    fd.append('_token',        CSRF_TOKEN);
    fd.append('_method',       'PUT');
    fd.append('nama_barang',   document.getElementById('editNama').value);
    fd.append('harga_sewa',    document.getElementById('editHarga').value);
    fd.append('status_barang', document.getElementById('editStatus').value);
    fd.append('stok',          document.getElementById('editStokJson').value);
    fd.append('ukuran',        document.getElementById('editUkuranLabel').value);
    fd.append('hapus_foto',    document.getElementById('editHapusFoto').value);

    const fotoFile = document.getElementById('editFotoFile');
    if (fotoFile.files && fotoFile.files[0]) {
        fd.append('foto', fotoFile.files[0]);
    }

    fetch(`/barang/${editingId}`, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            /* update in-memory */
            const idx = INV_BARANG.findIndex(x => x.id === editingId);
            if (idx > -1) {
                INV_BARANG[idx].nama   = document.getElementById('editNama').value;
                INV_BARANG[idx].harga  = parseFloat(document.getElementById('editHarga').value)||0;
                INV_BARANG[idx].status = document.getElementById('editStatus').value;
                const stokParsed = JSON.parse(document.getElementById('editStokJson').value||'{}');
                INV_BARANG[idx].stok  = stokParsed;
                INV_BARANG[idx].total_stok = Object.values(stokParsed).reduce((a,v)=>a+v,0);
                INV_BARANG[idx].ukuran = document.getElementById('editUkuranLabel').value;
            }
            renderGrid();
            closeEditModal();
            showToast('✅ Perubahan berhasil disimpan');
        } else {
            showToast('❌ ' + (data.message || 'Gagal menyimpan'));
        }
    })
    .catch(() => showToast('❌ Terjadi kesalahan jaringan'));
}

function deleteBarangFromModal() {
    if (!editingId) return;
    const b = INV_BARANG.find(x => x.id === editingId);
    if (!b) return;
    deleteBarang(editingId, b.nama, true);
}

function deleteBarang(id, nama, fromModal = false) {
    if (!confirm(`Hapus barang "${nama}"?\n\nTindakan ini tidak dapat dibatalkan.`)) return;

    fetch(`/barang/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const idx = INV_BARANG.findIndex(x => x.id === id);
            if (idx > -1) INV_BARANG.splice(idx, 1);
            if (fromModal) closeEditModal();
            renderGrid();
            showToast('🗑️ Barang berhasil dihapus');
        } else {
            showToast('❌ ' + (data.message || 'Gagal menghapus'));
        }
    })
    .catch(() => showToast('❌ Terjadi kesalahan jaringan'));
}

/* ═══════════════════════════════════════
   ADD MODAL
═══════════════════════════════════════ */
function openAddModal() {
    document.getElementById('addOv').classList.add('show');
}
function closeAddModal() {
    document.getElementById('addOv').classList.remove('show');
    document.getElementById('addForm').reset();
    document.querySelectorAll('.add-stok-num').forEach(i=>{i.disabled=true;i.value=0;});
    document.getElementById('addUkuranLabel').value='';
    document.getElementById('addStokJson').value='{}';
    /* clear photo preview */
    const pv = document.getElementById('addPhotoPrev');
    const img = pv.querySelector('img');
    if (img) img.remove();
}

function onAddUkuranChange() {
    const ukuranList = [], stokObj = {};
    document.querySelectorAll('.add-ukuran-cb').forEach(cb => {
        const uk    = cb.dataset.ukuran;
        const input = document.querySelector(`.add-stok-num[data-ukuran="${uk}"]`);
        if (!input) return;
        if (cb.checked) {
            input.disabled = false;
            const j = parseInt(input.value)||0;
            if (j > 0) { ukuranList.push(uk); stokObj[uk] = j; }
        } else {
            input.disabled = true;
            input.value = 0;
        }
    });
    document.getElementById('addUkuranLabel').value = ukuranList.join(', ');
    document.getElementById('addStokJson').value = JSON.stringify(stokObj);
}

function previewAddFoto(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const pv = document.getElementById('addPhotoPrev');
        let img = pv.querySelector('img');
        if (!img) { img = document.createElement('img'); img.style.cssText='position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:6px'; pv.appendChild(img); }
        img.src = e.target.result;
        const ph = pv.querySelector('.photo-preview-placeholder');
        if (ph) ph.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}

function saveAddBarang() {
    const stokJson = document.getElementById('addStokJson').value;
    const stokObj  = JSON.parse(stokJson||'{}');
    if (!Object.keys(stokObj).length) { showToast('⚠️ Centang dan isi minimal satu ukuran'); return; }

    const nama  = document.getElementById('addNama').value.trim();
    const harga = document.getElementById('addHarga').value;
    if (!nama)  { showToast('⚠️ Nama barang wajib diisi'); return; }
    if (!harga) { showToast('⚠️ Harga sewa wajib diisi'); return; }

    const fd = new FormData(document.getElementById('addForm'));
    fd.set('stok', stokJson);
    fd.set('ukuran', document.getElementById('addUkuranLabel').value);

    fetch('{{ route("barang.store") }}', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('✅ Produk baru berhasil ditambahkan');
            closeAddModal();
            setTimeout(() => location.reload(), 1400);
        } else {
            showToast('❌ ' + (data.message || 'Gagal menyimpan'));
        }
    })
    .catch(() => showToast('❌ Terjadi kesalahan jaringan'));
}

/* ═══════════════════════════════════════
   TOAST
═══════════════════════════════════════ */
function showToast(msg) {
    const t = document.getElementById('invToast');
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(t._t);
    t._t = setTimeout(() => t.classList.remove('show'), 2600);
}

/* ═══════════════════════════════════════
   ESC CLOSE
═══════════════════════════════════════ */
document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    closeEditModal();
    if (typeof closeAddModal === 'function') closeAddModal();
});

/* ═══════════════════════════════════════
   INIT
═══════════════════════════════════════ */
renderChips();
renderGrid();
</script>

@endsection