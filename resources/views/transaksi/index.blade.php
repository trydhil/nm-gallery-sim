@extends('layouts.app')

@section('title', 'Kasir POS')
@section('breadcrumb', 'Kasir POS')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
@endpush

@section('content')

@php
  $posBarangData = $barangs->map(function($b) {
    $stok = [];
    if ($b->stok) { $decoded = json_decode($b->stok, true); if (is_array($decoded)) $stok = $decoded; }
    $totalStok = array_sum($stok);
    $activeRental = $b->detailTransaksis->contains(function ($dt) {
      return ($dt->transaksi?->status_transaksi) === 'Diproses';
    });
    return [
      'id'            => $b->id_barang,
      'nama'          => $b->nama_barang,
      'stok'          => $stok,
      'harga'         => (float) $b->harga_sewa,
      'status'        => $b->status_barang,
      'foto'          => $b->foto,
      'ukuran'        => $b->ukuran ?? '',
      'total_stok'    => $totalStok,
      'available'     => $totalStok > 0 && $b->status_barang === 'Tersedia',
      'active_rental' => $activeRental,
    ];
  });

  $dendaVal    = $dendaPerHari ?? 50000;
  $trxJsData   = isset($transaksiAktif) ? $transaksiAktif->map(function($t) use ($dendaVal) {
    $jTempo    = \Carbon\Carbon::parse($t->tgl_jatuh_tempo)->startOfDay();
    $nowD      = \Carbon\Carbon::now()->startOfDay();
    $terlambat = $nowD->gt($jTempo);
    $hariTelat = $terlambat ? $jTempo->diffInDays($nowD) : 0;
    $detail    = $t->detailTransaksis->first();
    $sisa      = (float)($t->sisa_tagihan ?? 0);
    return [
      'id'                  => $t->id_transaksi,
      'id_barang'           => $detail->id_barang ?? null,
      'qty'                 => (int) ($detail->kuantitas ?? 1),
      'no_trx'              => '#TRX-'.str_pad($t->id_transaksi, 4, '0', STR_PAD_LEFT),
      'pelanggan'           => $t->pelanggan->nama_pelanggan ?? '-',
      'barang'              => $detail->barang->nama_barang ?? '-',
      'ukuran'              => $detail->ukuran ?? '-',
      'tgl_sewa'            => \Carbon\Carbon::parse($t->tgl_sewa)->format('d/m/Y'),
      'tgl_jatuh'           => \Carbon\Carbon::parse($t->tgl_jatuh_tempo)->format('d/m/Y'),
      'total_biaya'         => (float)$t->total_biaya,
      'sisa_tagihan'        => $sisa,
      'terlambat'           => $terlambat,
      'hari_telat'          => $hariTelat,
      'denda_per_hari'      => (float)$dendaVal,
      'total_denda'         => (float)($hariTelat * $dendaVal),
      'total_bayar_kembali' => (float)($sisa + $hariTelat * $dendaVal),
    ];
  })->values() : collect();

  $jmlTerlambat = isset($transaksiAktif) ? $transaksiAktif->filter(fn($t) =>
    \Carbon\Carbon::now()->startOfDay()->gt(\Carbon\Carbon::parse($t->tgl_jatuh_tempo)->startOfDay())
  )->count() : 0;
@endphp

<style>
:root {
  --pos-black:#0a0a0a; --pos-black3:#1a1a1a;
  --pos-gold:#C9A84C; --pos-gold-lt:#e0c06e; --pos-gold-dk:#a07830;
  --pos-gold-xs:rgba(201,168,76,.08); --pos-gold-md:rgba(201,168,76,.25);
  --pos-gold-rim:rgba(201,168,76,.35);
  --pos-surface:#f8f7f4;
  --pos-border:rgba(0,0,0,.09); --pos-border2:rgba(0,0,0,.15);
  --pos-muted:#6b6b6b; --pos-hint:#bbb;
  --pos-red:#c0392b; --pos-green:#1a8050;
  --pos-r:6px; --pos-r2:10px; --pos-r3:14px;
}

/* Halaman mengisi penuh area .content */
.pos-page {
  display:flex; flex-direction:column;
  height:calc(100vh - 52px);
  overflow:hidden;
  margin:-24px;
}

/* ── TAB BAR ── */
.pos-tabbar{background:#fff;border-bottom:1px solid var(--gray-200);display:flex;align-items:center;padding:0 20px;gap:4px;flex-shrink:0;height:44px}
.pos-tab{padding:0 18px;height:100%;display:flex;align-items:center;gap:7px;font-size:12.5px;font-weight:500;color:var(--gray-500);cursor:pointer;border-bottom:2px solid transparent;transition:.15s;user-select:none}
.pos-tab:hover{color:var(--pos-gold-dk)}
.pos-tab.active{color:var(--black);font-weight:600;border-bottom-color:var(--pos-gold)}
.pos-tab-badge{background:var(--pos-gold);color:var(--black);font-size:9.5px;font-weight:700;min-width:18px;height:18px;border-radius:9px;display:flex;align-items:center;justify-content:center;padding:0 4px}
.pos-tab-badge.red{background:var(--pos-red);color:#fff}
.pos-tab-pane{display:none;flex:1;overflow:hidden;min-height:0}
.pos-tab-pane.active{display:flex;min-height:0}

/* ── KATALOG ── */
.pos-main{display:flex;flex:1;overflow:hidden;min-height:0}
.pos-catalog{flex:1;display:flex;flex-direction:column;overflow:hidden;background:var(--pos-surface);border-right:1px solid var(--gray-200);min-height:0}
.pos-cat-top{background:#fff;border-bottom:1px solid var(--pos-border);padding:10px 14px;display:flex;flex-direction:column;gap:8px;flex-shrink:0}
.pos-search{display:flex;align-items:center;gap:8px;background:var(--pos-surface);border:1.5px solid var(--pos-border2);border-radius:var(--pos-r2);padding:0 11px;transition:.2s}
.pos-search:focus-within{border-color:var(--pos-gold);background:#fff}
.pos-search input{flex:1;border:none;background:transparent;outline:none;padding:8px 0;font-size:12.5px;font-family:inherit;color:var(--black)}
.pos-search input::placeholder{color:var(--pos-hint)}
.pos-chips{display:flex;gap:6px;overflow-x:auto}
.pos-chips::-webkit-scrollbar{height:0}
.pos-chip{padding:3.5px 12px;border-radius:20px;font-size:11px;font-weight:500;border:1px solid var(--pos-border2);background:#fff;color:var(--pos-muted);cursor:pointer;white-space:nowrap;flex-shrink:0;transition:.12s}
.pos-chip:hover:not(.active){border-color:var(--pos-gold-dk);color:var(--pos-gold-dk)}
.pos-chip.active{background:var(--pos-black);border-color:var(--pos-black);color:var(--pos-gold-lt)}
.pos-grid{flex:1;overflow-y:auto;padding:16px 16px 60px 16px;display:grid;grid-template-columns:repeat(5,1fr);gap:16px;align-content:start;min-height:0;grid-auto-rows:max-content}
.pos-grid::-webkit-scrollbar{width:8px}
.pos-grid::-webkit-scrollbar-thumb{background:var(--gray-300);border-radius:4px}

/* ── PRODUCT CARD ── */
.pos-card{background:#fff;border:1.5px solid var(--pos-border);border-radius:var(--pos-r2);overflow:hidden;cursor:pointer;transition:.18s;position:relative;display:flex;flex-direction:column;height:100%;min-height:220px}
.pos-card:hover{border-color:var(--pos-gold);box-shadow:0 3px 14px rgba(201,168,76,.14);transform:translateY(-2px)}
.pos-card.disewa{opacity:.5;cursor:not-allowed}
.pos-card.disewa:hover{transform:none;box-shadow:none;border-color:var(--pos-border)}
.pos-card-img{aspect-ratio:3/4;width:100%;display:flex;align-items:center;justify-content:center;font-size:34px;background:linear-gradient(135deg,#faf5e8,#f5edd6);position:relative;overflow:hidden}
.pos-card-img img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0}
.pos-status{position:absolute;top:5px;right:5px;font-size:8.5px;font-weight:700;padding:2px 6px;border-radius:8px;z-index:1}
.pos-status.ok{background:rgba(26,128,80,.1);color:#1a8050;border:1px solid rgba(26,128,80,.2)}
.pos-status.out{background:rgba(220,52,52,.08);color:#c0392b;border:1px solid rgba(220,52,52,.18)}
.pos-card-body{padding:10px 12px;flex:1;display:flex;flex-direction:column;justify-content:flex-start}
.pos-card-name{font-size:12px;font-weight:600;color:var(--black);line-height:1.4;margin-bottom:4px}
.pos-card-meta{font-size:10px;color:var(--pos-hint);margin-bottom:auto}
.pos-card-price{font-size:13px;font-weight:800;color:var(--pos-gold-dk);margin-top:6px}
.pos-card-price small{font-size:9px;font-weight:400;color:var(--pos-hint)}

/* ── ORDER PANEL ── */
.pos-panel{width:360px;background:#fff;display:flex;flex-direction:column;flex-shrink:0;border-left:1px solid var(--gray-200);overflow-y:auto}
.pos-panel::-webkit-scrollbar{width:5px}
.pos-panel::-webkit-scrollbar-thumb{background:var(--gray-300);border-radius:3px}
.pos-cust-sec{padding:10px 13px;border-bottom:1px solid var(--gray-100);flex-shrink:0}
.pos-cust-lbl{font-size:9.5px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px}
.pos-cust-select{display:flex;align-items:center;gap:7px;border:1.5px solid var(--gray-200);border-radius:var(--pos-r2);padding:7px 10px;cursor:pointer;transition:.15s;background:#fff}
.pos-cust-select:hover{border-color:var(--pos-gold-rim)}
.pos-cust-select.selected{border-color:var(--pos-gold);background:var(--pos-gold-xs)}
.pos-cust-icon{width:26px;height:26px;border-radius:50%;background:var(--pos-black);border:1.5px solid var(--pos-gold-md);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:var(--pos-gold-lt);flex-shrink:0}
.pos-cust-name{font-size:12px;font-weight:500;color:var(--black);flex:1}
.pos-cust-placeholder{color:var(--pos-hint)}
.pos-cart-sec{display:flex;flex-direction:column;flex-shrink:0}
.pos-cart-head{padding:8px 13px;background:var(--pos-black);display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
.pos-cart-title{font-size:12px;font-weight:600;color:var(--pos-gold-lt);display:flex;align-items:center;gap:6px}
.pos-cart-badge{background:var(--pos-gold);color:var(--pos-black);font-size:9px;font-weight:700;width:16px;height:16px;border-radius:50%;display:flex;align-items:center;justify-content:center}
.pos-cart-body{display:flex;flex-direction:column;min-height:0}
.pos-cart-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:120px;gap:8px;color:var(--pos-hint);padding:20px;text-align:center}
.pos-cart-empty-ico{font-size:28px;opacity:.3}
.pos-cart-empty-txt{font-size:11px;line-height:1.6}
.pos-cart-item{padding:9px 12px;border-bottom:1px solid var(--gray-100);position:relative}
.pos-item-name{font-size:11.5px;font-weight:600;color:var(--black);padding-right:18px;margin-bottom:1px}
.pos-item-sub{font-size:10px;color:var(--gray-500);margin-bottom:6px}
.pos-item-row{display:flex;align-items:center;justify-content:space-between}
.pos-qc{display:flex;align-items:center;gap:5px}
.pos-qb{width:20px;height:20px;border-radius:5px;border:1px solid var(--gray-200);background:var(--pos-surface);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:13px;color:var(--black);transition:.1s;line-height:1;font-family:inherit}
.pos-qb:hover{border-color:var(--pos-gold);color:var(--pos-gold-dk)}
.pos-qn{font-size:12px;font-weight:600;min-width:16px;text-align:center}
.pos-item-price{font-size:11.5px;font-weight:700;color:var(--pos-gold-dk);font-family:var(--ff-mono)}
.pos-item-del{position:absolute;top:8px;right:10px;background:none;border:none;cursor:pointer;color:var(--pos-hint);font-size:12px;padding:2px;transition:.1s;line-height:1}
.pos-item-del:hover{color:var(--pos-red)}
.pos-period-sec{padding:9px 13px;border-bottom:1px solid var(--gray-100);flex-shrink:0}
.pos-period-lbl{font-size:9.5px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:flex;align-items:center;justify-content:space-between}
.pos-period-grid{display:grid;grid-template-columns:1fr 1fr;gap:7px}
.pos-period-field{display:flex;flex-direction:column;gap:3px}
.pos-period-field label{font-size:9.5px;color:var(--gray-400);font-weight:500}
.pos-period-field input{padding:6px 8px;border:1.5px solid var(--gray-200);border-radius:var(--pos-r);font-size:11.5px;font-family:inherit;color:var(--black);outline:none;transition:.15s}
.pos-period-field input:focus{border-color:var(--pos-gold)}
.pos-durasi-chip{padding:5px 8px;background:var(--pos-gold-xs);border-radius:var(--pos-r);border:1px solid var(--pos-gold-md);font-size:10.5px;color:var(--pos-gold-dk);font-weight:600;text-align:center}
.pos-pay-sec{padding:9px 13px;border-bottom:1px solid var(--gray-100);flex-shrink:0}
.pos-pay-lbl{font-size:9.5px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.7px;margin-bottom:7px}
.pos-metode-wrap{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:9px}
.pos-metode-opt{padding:7px 8px;border:1.5px solid var(--gray-200);border-radius:var(--pos-r2);cursor:pointer;text-align:center;transition:.15s;background:#fff}
.pos-metode-opt.active{border-color:var(--pos-gold);background:var(--pos-gold-xs)}
.pos-metode-lbl{font-size:11.5px;font-weight:600;color:var(--black)}
.pos-metode-desc{font-size:9px;color:var(--gray-400);margin-top:1px}
.pos-field-row{display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-bottom:6px}
.pos-field-item{display:flex;flex-direction:column;gap:3px}
.pos-field-item label{font-size:9.5px;color:var(--gray-500);font-weight:600}
.pos-inp{width:100%;padding:6px 8px;border:1.5px solid var(--gray-200);border-radius:var(--pos-r);font-size:11.5px;font-family:inherit;color:var(--black);background:var(--pos-surface);outline:none;transition:.15s}
.pos-inp:focus{border-color:var(--pos-gold);background:#fff}
.pos-summary-row{display:flex;justify-content:space-between;font-size:11.5px;color:var(--gray-500);padding:3px 0}
.pos-summary-row.total{padding:8px 0 5px;border-top:1px solid var(--gray-200);margin-top:4px;font-size:15px;font-weight:700;color:var(--black)}
.pos-summary-row.total .val{color:var(--pos-gold-dk);font-family:var(--ff-mono)}
.pos-panel-foot{padding:10px 13px;border-top:1px solid var(--gray-100);flex-shrink:0;background:var(--pos-surface)}
.pos-bayar-wrap{background:#fff;border:1.5px solid var(--gray-200);border-radius:var(--pos-r2);padding:9px 11px;margin-bottom:9px}
.pos-bayar-label{font-size:10px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;display:flex;align-items:center;justify-content:space-between}
.pos-bayar-row{display:flex;align-items:center;gap:7px}
.pos-bayar-prefix{font-size:11.5px;color:var(--gray-400);font-weight:600}
.pos-bayar-input{flex:1;border:1.5px solid var(--gray-200);border-radius:var(--pos-r);padding:7px 9px;font-size:14px;font-family:var(--ff-mono);font-weight:700;color:var(--black);text-align:right;outline:none;transition:.15s;-moz-appearance:textfield}
.pos-bayar-input::-webkit-outer-spin-button,.pos-bayar-input::-webkit-inner-spin-button{-webkit-appearance:none}
.pos-bayar-input:focus{border-color:var(--pos-gold);background:var(--pos-gold-xs)}
.pos-kembali-row{display:flex;justify-content:space-between;align-items:center;padding:7px 0 0;border-top:1px dashed var(--gray-200);margin-top:7px}
.pos-kembali-lbl{font-size:11px;color:var(--gray-500);font-weight:600}
.pos-kembali-val{font-family:var(--ff-mono);font-size:15px;font-weight:700;color:var(--pos-green)}
.pos-kembali-val.minus{color:var(--pos-red)}
.pos-quick-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:5px}
.pos-quick-btn{padding:5px 3px;border:1px solid var(--gray-200);border-radius:var(--pos-r);background:#fff;font-size:9.5px;font-weight:600;color:var(--gray-600);cursor:pointer;transition:.12s;text-align:center}
.pos-quick-btn:hover{border-color:var(--pos-gold-rim);color:var(--pos-gold-dk);background:var(--pos-gold-xs)}
.pos-btn-pay{width:100%;padding:11px;background:var(--pos-black);border:1.5px solid var(--pos-gold-rim);border-radius:var(--pos-r2);color:var(--pos-gold-lt);font-size:13px;font-weight:700;cursor:pointer;transition:.18s;font-family:inherit;margin-bottom:6px}
.pos-btn-pay:hover{background:var(--pos-black3);box-shadow:0 4px 16px rgba(201,168,76,.2)}
.pos-btn-pay:disabled{opacity:.35;cursor:not-allowed;box-shadow:none}
.pos-btn-reset{width:100%;padding:7px;background:transparent;border:1px solid var(--gray-200);border-radius:var(--pos-r2);color:var(--gray-500);font-size:11px;cursor:pointer;transition:.12s;font-family:inherit}
.pos-btn-reset:hover{border-color:var(--pos-red);color:var(--pos-red)}

/* ── PENGEMBALIAN ── */
.kembali-wrap{flex:1;overflow-y:auto;padding:20px;background:var(--pos-surface)}
.kembali-wrap::-webkit-scrollbar{width:5px}
.kembali-wrap::-webkit-scrollbar-thumb{background:var(--gray-300);border-radius:3px}
.kembali-header{margin-bottom:16px}
.kembali-title{font-size:16px;font-weight:700;color:var(--black)}
.kembali-subtitle{font-size:12px;color:var(--gray-500);margin-top:3px}
.kembali-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px}
.trx-card{background:#fff;border:1.5px solid var(--pos-border);border-radius:var(--pos-r2);overflow:hidden;transition:.18s}
.trx-card:hover{border-color:var(--pos-gold);box-shadow:0 3px 12px rgba(201,168,76,.12)}
.trx-card.terlambat{border-color:rgba(220,52,52,.3)}
.trx-card-head{padding:11px 14px;background:var(--pos-surface);border-bottom:1px solid var(--pos-border);display:flex;align-items:center;justify-content:space-between}
.trx-no{font-size:11px;font-weight:700;color:var(--pos-gold-dk);font-family:var(--ff-mono)}
.trx-status-ok{font-size:10px;font-weight:600;padding:2px 9px;border-radius:10px;background:rgba(26,128,80,.1);color:#1a8050;border:1px solid rgba(26,128,80,.2)}
.trx-status-late{font-size:10px;font-weight:600;padding:2px 9px;border-radius:10px;background:rgba(220,52,52,.1);color:#c0392b;border:1px solid rgba(220,52,52,.2)}
.trx-card-body{padding:13px 14px}
.trx-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px}
.trx-field-k{font-size:9.5px;text-transform:uppercase;color:var(--pos-hint);font-weight:600;letter-spacing:.6px;margin-bottom:2px}
.trx-field-v{font-size:12.5px;font-weight:500;color:var(--black)}
.trx-biaya-box{background:var(--pos-surface);border-radius:var(--pos-r);padding:10px 12px;margin-bottom:12px}
.trx-biaya-row{display:flex;justify-content:space-between;font-size:11.5px;color:var(--gray-500);padding:2px 0}
.trx-biaya-total{display:flex;justify-content:space-between;padding-top:8px;margin-top:6px;border-top:1px solid var(--pos-border2)}
.trx-biaya-total-lbl{font-size:13px;font-weight:600}
.trx-biaya-total-val{font-size:15px;font-weight:700;color:var(--pos-gold-dk);font-family:var(--ff-mono)}
.trx-biaya-total-val.danger{color:var(--pos-red)}
.btn-kembalikan{width:100%;padding:10px;background:var(--pos-black);border:1.5px solid var(--pos-gold-rim);border-radius:var(--pos-r2);color:var(--pos-gold-lt);font-size:12.5px;font-weight:600;cursor:pointer;transition:.18s;font-family:inherit}
.btn-kembalikan:hover{background:var(--pos-black3)}
.btn-kembalikan.danger{background:#b71c1c;border-color:rgba(220,52,52,.4)}
.btn-kembalikan.danger:hover{background:#c62828}
.kembali-empty{text-align:center;padding:60px 20px;color:var(--pos-hint)}

/* ── OVERLAY ── */
.pos-ov{position:fixed;inset:0;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;z-index:500;opacity:0;pointer-events:none;transition:opacity .2s;padding:16px}
.pos-ov.show{opacity:1;pointer-events:all}

/* ── SIZE PICKER ── */
.pos-spick{background:#fff;border-radius:var(--pos-r3);width:340px;overflow:hidden;transform:scale(.93) translateY(8px);transition:.22s cubic-bezier(.34,1.4,.64,1)}
.pos-ov.show .pos-spick{transform:scale(1) translateY(0)}
.sp-head{background:var(--pos-black);padding:13px 16px;display:flex;justify-content:space-between;align-items:flex-start}
.sp-name{font-size:14px;font-weight:600;color:var(--pos-gold-lt);margin-bottom:2px}
.sp-price{font-size:11px;color:rgba(255,255,255,.35)}
.sp-close{background:none;border:none;color:rgba(255,255,255,.35);cursor:pointer;font-size:18px;padding:2px;line-height:1;transition:.12s}
.sp-close:hover{color:var(--pos-gold-lt)}
.sp-body{padding:15px}
.sp-lbl{font-size:9.5px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.9px;margin-bottom:8px}
.sz-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:7px;margin-bottom:13px}
.sz-opt{border:1.5px solid var(--gray-200);border-radius:var(--pos-r);background:var(--pos-surface);cursor:pointer;padding:8px 4px;text-align:center;transition:.15s}
.sz-opt:hover:not(.sz-dis):not(.sz-active){border-color:var(--pos-gold-dk)}
.sz-opt.sz-active{border-color:var(--pos-gold);background:var(--pos-gold-xs)}
.sz-opt.sz-dis{opacity:.3;cursor:not-allowed}
.sz-lbl{font-size:12px;font-weight:600}
.sz-stok{font-size:9px;color:var(--gray-400);margin-top:2px}
.qty-row{display:flex;align-items:center;justify-content:space-between;background:var(--pos-surface);border-radius:var(--pos-r);padding:9px 12px;margin-bottom:12px}
.qty-lbl{font-size:12px;color:var(--gray-500)}
.qty-ctrl{display:flex;align-items:center;gap:10px}
.qty-btn{width:24px;height:24px;border-radius:6px;border:1.5px solid var(--gray-200);background:#fff;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;transition:.12px;line-height:1;font-family:inherit}
.qty-btn:hover{border-color:var(--pos-gold);color:var(--pos-gold-dk)}
.qty-n{font-size:14px;font-weight:700;min-width:20px;text-align:center}
.btn-add-cart{width:100%;padding:11px;background:var(--pos-black);border:1.5px solid var(--pos-gold-rim);border-radius:var(--pos-r2);color:var(--pos-gold-lt);font-size:13px;font-weight:600;cursor:pointer;transition:.15s;font-family:inherit}
.btn-add-cart:hover{background:var(--pos-black3)}
.btn-add-cart:disabled{opacity:.35;cursor:not-allowed}

/* ── CUSTOMER MODAL ── */
.cust-modal{background:#fff;border-radius:var(--pos-r3);width:420px;max-height:80vh;display:flex;flex-direction:column;overflow:hidden;transform:scale(.93) translateY(8px);transition:.22s cubic-bezier(.34,1.4,.64,1)}
.pos-ov.show .cust-modal{transform:scale(1) translateY(0)}
.cm-head{background:var(--pos-black);padding:13px 16px;display:flex;justify-content:space-between;align-items:center}
.cm-title{font-size:14px;font-weight:600;color:var(--pos-gold-lt)}
.cm-close{background:none;border:none;color:rgba(255,255,255,.35);cursor:pointer;font-size:18px;padding:2px;line-height:1}
.cm-close:hover{color:var(--pos-gold-lt)}
.cm-search{padding:12px;border-bottom:1px solid var(--gray-100)}
.cm-search input{width:100%;padding:8px 12px;border:1.5px solid var(--gray-200);border-radius:var(--pos-r2);font-size:12.5px;font-family:inherit;outline:none;transition:.15s}
.cm-search input:focus{border-color:var(--pos-gold)}
.cm-list{flex:1;overflow-y:auto}
.cm-item{display:flex;align-items:center;gap:10px;padding:10px 14px;border-bottom:1px solid var(--gray-100);cursor:pointer;transition:.1s}
.cm-item:hover{background:var(--pos-surface)}
.cm-ava{width:30px;height:30px;border-radius:50%;background:var(--pos-black);border:1.5px solid var(--pos-gold-md);display:flex;align-items:center;justify-content:center;font-size:10.5px;font-weight:700;color:var(--pos-gold-lt);flex-shrink:0}
.cm-name{font-size:12.5px;font-weight:600;color:var(--black)}
.cm-telp{font-size:10px;color:var(--gray-400)}
.cm-empty{text-align:center;padding:32px;color:var(--gray-400);font-size:12px}
.cm-new-btn{width:calc(100% - 24px);margin:10px 12px;padding:9px;background:#fff;border:1.5px dashed var(--gray-300);border-radius:var(--pos-r2);color:var(--gray-500);font-size:12px;cursor:pointer;transition:.15s;font-family:inherit}
.cm-new-btn:hover{border-color:var(--pos-gold-rim);color:var(--pos-gold-dk);background:var(--pos-gold-xs)}
.cm-new-form{padding:14px;border-top:1px solid var(--gray-100)}
.cm-form-title{font-size:11px;font-weight:700;color:var(--gray-600);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px}
.cm-form-field{margin-bottom:9px}
.cm-form-field label{font-size:10.5px;font-weight:600;color:var(--gray-500);display:block;margin-bottom:3px}
.cm-form-field input{width:100%;padding:7px 10px;border:1.5px solid var(--gray-200);border-radius:var(--pos-r);font-size:12px;font-family:inherit;outline:none;transition:.15s}
.cm-form-field input:focus{border-color:var(--pos-gold)}
.cm-form-btns{display:flex;gap:7px}
.cm-form-save{flex:1;padding:8px;background:var(--pos-black);border:1px solid var(--pos-gold-rim);border-radius:var(--pos-r);color:var(--pos-gold-lt);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit}
.cm-form-cancel{padding:8px 12px;background:transparent;border:1px solid var(--gray-200);border-radius:var(--pos-r);color:var(--gray-500);font-size:12px;cursor:pointer;font-family:inherit}

/* ── RESI MODAL ── */
.resi-overlay{position:fixed;inset:0;background:var(--pos-surface);z-index:900;display:flex;flex-direction:column;opacity:0;pointer-events:none;transition:opacity .25s}
.resi-overlay.show{opacity:1;pointer-events:all}
.resi-topbar{height:52px;background:#fff;border-bottom:1px solid var(--gray-200);display:flex;align-items:center;padding:0 20px;gap:10px;flex-shrink:0}
.resi-back-btn{display:flex;align-items:center;gap:6px;padding:7px 13px;background:#fff;border:1px solid var(--gray-200);border-radius:var(--pos-r2);color:var(--gray-600);font-size:12px;font-weight:600;cursor:pointer;transition:.15s;font-family:inherit}
.resi-back-btn:hover{border-color:var(--pos-gold-rim);color:var(--pos-gold-dk)}
.resi-fmt-btn{padding:6px 12px;border:1px solid var(--gray-200);border-radius:var(--pos-r);background:#fff;color:var(--gray-500);font-size:11.5px;font-weight:500;cursor:pointer;transition:.15s;font-family:inherit}
.resi-fmt-btn.active{background:var(--pos-gold-xs);border-color:var(--pos-gold-md);color:var(--pos-gold-dk);font-weight:700}
.resi-pdf-btn{margin-left:auto;display:flex;align-items:center;gap:6px;padding:7px 16px;background:#1a8050;border:none;border-radius:var(--pos-r2);color:#fff;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s}
.resi-pdf-btn:hover{background:#15704a}
.resi-main{flex:1;display:flex;align-items:flex-start;justify-content:center;padding:32px 20px;overflow-y:auto}
.resi-card{background:#fff;border:1px solid var(--gray-200);border-radius:14px;overflow:hidden;width:100%;max-width:520px;box-shadow:0 8px 32px rgba(0,0,0,.08);border-left:4px solid var(--pos-gold)}
.resi-card-inner{padding:20px 22px}
.resi-header-row{display:flex;align-items:flex-start;justify-content:space-between;padding-bottom:16px;margin-bottom:16px;border-bottom:1px dashed var(--gray-200)}
.resi-store{display:flex;align-items:center;gap:10px}
.resi-store-ico{width:40px;height:40px;border-radius:9px;background:linear-gradient(135deg,var(--pos-gold-lt),var(--pos-gold),var(--pos-gold-dk));display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:var(--black);flex-shrink:0}
.resi-store-name{font-size:14px;font-weight:700;color:var(--black)}
.resi-store-phone{font-size:10.5px;color:var(--gray-400);margin-top:2px}
.resi-invoice-box{text-align:right}
.resi-inv-label{font-size:9px;font-weight:700;color:var(--gray-400);text-transform:uppercase;letter-spacing:1px}
.resi-inv-no{font-family:var(--ff-mono);font-size:16px;font-weight:700;color:var(--pos-gold-dk);margin-top:2px;word-break:break-all}
.resi-inv-date{font-size:10.5px;color:var(--gray-500);margin-top:3px}
.resi-kembali-box{background:rgba(26,128,80,.06);border:1px solid rgba(26,128,80,.2);border-radius:var(--pos-r);padding:9px 13px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center}
.resi-kembali-lbl{font-size:11px;font-weight:600;color:#1a8050}
.resi-kembali-amount{font-family:var(--ff-mono);font-size:14px;font-weight:700;color:#1a8050}
.resi-content-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;padding:14px 0;border-bottom:1px dashed var(--gray-200);margin-bottom:14px}
.resi-sec-label{font-size:8.5px;font-weight:700;color:var(--gray-400);text-transform:uppercase;letter-spacing:1px;margin-bottom:7px;display:flex;align-items:center;gap:5px}
.resi-cust-name{font-size:13.5px;font-weight:700;color:var(--black);margin-bottom:3px}
.resi-cust-addr{font-size:11px;color:var(--gray-500);line-height:1.5}
.resi-items-list{font-size:11.5px;color:var(--gray-600);line-height:1.8}
.resi-item-row{display:flex;justify-content:space-between;align-items:center;padding:2px 0}
.resi-total-row{display:flex;justify-content:space-between;align-items:center;padding-top:9px;margin-top:7px;border-top:1px solid var(--gray-200)}
.resi-total-lbl{font-size:10.5px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.5px}
.resi-total-val{font-family:var(--ff-mono);font-size:15px;font-weight:800;color:var(--black)}
.resi-footer-row{display:flex;align-items:flex-end;justify-content:space-between;padding-top:14px;border-top:1px dashed var(--gray-100)}
.resi-print-date{font-size:10px;color:var(--gray-400)}
.resi-barcode-wrap{text-align:center}
.resi-barcode-code{font-family:var(--ff-mono);font-size:9px;color:var(--gray-500);margin-top:3px;letter-spacing:1px}

/* ── KONFIRM KEMBALI MODAL ── */
.konfirm-modal{background:#fff;border-radius:var(--pos-r3);width:420px;overflow:hidden;transform:scale(.94) translateY(8px);transition:.22s cubic-bezier(.34,1.4,.64,1)}
.pos-ov.show .konfirm-modal{transform:scale(1) translateY(0)}
.km-head{background:var(--pos-black);padding:14px 18px;display:flex;justify-content:space-between;align-items:center}
.km-title{font-size:14px;font-weight:600;color:var(--pos-gold-lt)}
.km-close{background:none;border:none;color:rgba(255,255,255,.38);cursor:pointer;font-size:18px;line-height:1;transition:.12s}
.km-close:hover{color:var(--pos-gold-lt)}
.km-body{padding:18px}
.km-info{background:var(--pos-surface);border-radius:var(--pos-r2);padding:13px;margin-bottom:13px}
.km-info-row{display:flex;justify-content:space-between;font-size:12px;color:var(--gray-500);padding:3px 0}
.km-alert{padding:10px 13px;border-radius:var(--pos-r);margin-bottom:13px;font-size:12px;font-weight:500}
.km-alert.ok{background:rgba(26,128,80,.08);color:#1a8050;border:1px solid rgba(26,128,80,.2)}
.km-alert.late{background:rgba(220,52,52,.07);color:#c0392b;border:1px solid rgba(220,52,52,.2)}
.km-total{display:flex;justify-content:space-between;align-items:center;padding:12px 13px;background:var(--pos-surface);border-radius:var(--pos-r2);border:1.5px solid var(--pos-gold-md);margin-bottom:15px}
.km-total-lbl{font-size:13px;font-weight:600}
.km-total-val{font-size:17px;font-weight:700;font-family:var(--ff-mono);color:var(--pos-gold-dk)}
.km-total-val.danger{color:var(--pos-red)}
.btn-km-konfirm{width:100%;padding:12px;background:var(--pos-green);border:none;border-radius:var(--pos-r2);color:#fff;font-size:13.5px;font-weight:600;cursor:pointer;transition:.18s;font-family:inherit;margin-bottom:8px}
.btn-km-konfirm:hover{background:#15704a}
.btn-km-cancel{width:100%;padding:9px;background:transparent;border:1px solid var(--gray-200);border-radius:var(--pos-r2);color:var(--gray-500);font-size:12px;cursor:pointer;transition:.12s;font-family:inherit}
.btn-km-cancel:hover{border-color:var(--pos-red);color:var(--pos-red)}

/* ── TOAST ── */
.pos-toast{position:fixed;top:64px;right:20px;z-index:1000;background:var(--pos-black);color:var(--pos-gold-lt);border:1px solid var(--pos-gold-rim);border-radius:var(--pos-r2);padding:11px 16px;font-size:12.5px;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.25);display:flex;align-items:center;gap:7px;opacity:0;transform:translateY(-8px);pointer-events:none;transition:all .25s ease;max-width:280px}
.pos-toast.show{opacity:1;transform:translateY(0)}
</style>

{{-- ════ TAB BAR ════ --}}
<div class="pos-page">

<div class="pos-tabbar">
  <div class="pos-tab active" data-tab="pos">
    <i class="bi bi-receipt"></i> Transaksi Baru
  </div>
  <div class="pos-tab" data-tab="kembali">
    <i class="bi bi-clock-history"></i> Pengembalian
    @if(isset($transaksiAktif) && $transaksiAktif->count() > 0)
      <div class="pos-tab-badge {{ $jmlTerlambat > 0 ? 'red' : '' }}">
        {{ $transaksiAktif->count() }}
      </div>
    @endif
  </div>
</div>

{{-- ════ PANE 1: POS ════ --}}
<div class="pos-tab-pane active" id="panePos">
<div class="pos-main">

  <div class="pos-catalog">
    <div class="pos-cat-top">
      <div class="pos-search">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input id="posSearch" placeholder="Cari nama baju atau ukuran..." oninput="posRenderGrid()">
      </div>
      <div class="pos-chips" id="posChips"></div>
    </div>
    <div class="pos-grid" id="posGrid"></div>
  </div>

  <div class="pos-panel">
    <div class="pos-cust-sec">
      <div class="pos-cust-lbl">Pelanggan</div>
      <div class="pos-cust-select" id="posCustBtn" onclick="openCustModal()">
        <div class="pos-cust-icon" id="posCustIco"><i class="bi bi-person" style="font-size:12px"></i></div>
        <div class="pos-cust-name pos-cust-placeholder" id="posCustName">Pilih pelanggan...</div>
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
      </div>
    </div>
    <div class="pos-cart-sec">
      <div class="pos-cart-head">
        <div class="pos-cart-title">
          <i class="bi bi-bag"></i> Keranjang
          <div class="pos-cart-badge" id="posCartBadge">0</div>
        </div>
      </div>
      <div class="pos-cart-body" id="posCartBody">
        <div class="pos-cart-empty">
          <div class="pos-cart-empty-ico"><i class="bi bi-cart3"></i></div>
          <div class="pos-cart-empty-txt">Keranjang kosong</div>
        </div>
      </div>
    </div>
    <div class="pos-period-sec">
      <div class="pos-period-lbl">
        <span><i class="bi bi-calendar3"></i> Periode Sewa</span>
        <span id="posDurasiChip" style="display:none" class="pos-durasi-chip"></span>
      </div>
      <div class="pos-period-grid">
        <div class="pos-period-field">
          <label>Tgl Mulai</label>
          <input type="date" id="posTglSewa" onchange="posRecalc()">
        </div>
        <div class="pos-period-field">
          <label>Tgl Kembali</label>
          <input type="date" id="posTglKembali" onchange="posRecalc()">
        </div>
      </div>
    </div>
    <div class="pos-pay-sec">
      <div class="pos-pay-lbl">Metode Pembayaran</div>
      <div class="pos-metode-wrap">
        <div class="pos-metode-opt active" id="posOptLunas" onclick="posSetMetode('lunas')">
          <div class="pos-metode-lbl">Lunas</div>
          <div class="pos-metode-desc">Bayar penuh</div>
        </div>
        <div class="pos-metode-opt" id="posOptDP" onclick="posSetMetode('dp')">
          <div class="pos-metode-lbl">DP</div>
          <div class="pos-metode-desc">Bayar sebagian</div>
        </div>
      </div>
      <div class="pos-field-row">
        <div class="pos-field-item">
          <label>Diskon (Rp)</label>
          <input type="number" class="pos-inp" id="posDiskon" value="0" min="0" oninput="posRecalc()">
        </div>
        <div class="pos-field-item">
          <label>Ongkos Kirim (Rp)</label>
          <input type="number" class="pos-inp" id="posOngkir" value="0" min="0" oninput="posRecalc()">
        </div>
      </div>
      <div class="pos-summary-row"><span>Subtotal</span><span class="val" id="posSubtotal">Rp 0</span></div>
      <div class="pos-summary-row" id="posDiskonRow" style="display:none"><span>Diskon</span><span class="val" style="color:var(--pos-green)" id="posDiskonVal">-Rp 0</span></div>
      <div class="pos-summary-row" id="posOngkirRow" style="display:none"><span>Ongkos Kirim</span><span class="val" id="posOngkirVal">+Rp 0</span></div>
      <div class="pos-summary-row total"><span>Total</span><span class="val" id="posTotal">Rp 0</span></div>
    </div>
    <div class="pos-panel-foot">
      <div class="pos-bayar-wrap">
        <div class="pos-bayar-label">
          <span>Jumlah Bayar</span>
          <div class="pos-quick-grid">
            <div class="pos-quick-btn" onclick="posQuickAmount(10000)">10K</div>
            <div class="pos-quick-btn" onclick="posQuickAmount(20000)">20K</div>
            <div class="pos-quick-btn" onclick="posQuickAmount(50000)">50K</div>
            <div class="pos-quick-btn" onclick="posQuickAmount(100000)">100K</div>
          </div>
        </div>
        <div class="pos-bayar-row">
          <span class="pos-bayar-prefix">Rp</span>
          <input type="number" class="pos-bayar-input" id="posJumlahBayar" placeholder="0" oninput="posHitungKembali()" min="0">
        </div>
        <div class="pos-kembali-row">
          <span class="pos-kembali-lbl">Kembalian</span>
          <span class="pos-kembali-val" id="posKembali">Rp 0</span>
        </div>
      </div>
      <button class="pos-btn-pay" id="posBtnPay" disabled onclick="posProses()">
        <i class="bi bi-receipt-cutoff"></i> Proses Pembayaran
      </button>
      <button class="pos-btn-reset" onclick="posReset()">Batal / Reset Keranjang</button>
    </div>
  </div>

</div>
</div>{{-- end panePos --}}

{{-- ════ PANE 2: PENGEMBALIAN ════ --}}
<div class="pos-tab-pane" id="paneKembali">
  <div class="kembali-wrap">
    <div class="kembali-header">
      <div class="kembali-title">↩️ Konfirmasi Pengembalian</div>
      <div class="kembali-subtitle">
        {{ isset($transaksiAktif) ? $transaksiAktif->count() : 0 }} transaksi aktif
        @if($jmlTerlambat > 0)
          · <span style="color:var(--pos-red);font-weight:600">{{ $jmlTerlambat }} terlambat</span>
        @endif
      </div>
    </div>
    @if(!isset($transaksiAktif) || $transaksiAktif->isEmpty())
      <div class="kembali-empty">
        <div style="font-size:40px;opacity:.3;margin-bottom:12px">✅</div>
        <div style="font-size:14px;font-weight:600;margin-bottom:6px;color:var(--black)">Semua baju sudah kembali</div>
        <div style="font-size:12px">Tidak ada transaksi aktif saat ini</div>
      </div>
    @else
      <div class="kembali-grid">
        @foreach($transaksiAktif as $trx)
        @php
          $jatuh                = \Carbon\Carbon::parse($trx->tgl_jatuh_tempo)->startOfDay();
          $nowDay               = \Carbon\Carbon::now()->startOfDay();
          $terlambat            = $nowDay->gt($jatuh);
          $hariTelat            = $terlambat ? $jatuh->diffInDays($nowDay) : 0;
          $dendaVal2            = $dendaPerHari ?? 50000;
          $totalDendaTrx        = $hariTelat * $dendaVal2;
          $sisaTagihanTrx       = $trx->sisa_tagihan ?? 0;
          $totalBayarKembaliTrx = $sisaTagihanTrx + $totalDendaTrx;
          $detail               = $trx->detailTransaksis->first();
        @endphp
        <div class="trx-card{{ $terlambat ? ' terlambat' : '' }}">
          <div class="trx-card-head">
            <div class="trx-no">#TRX-{{ str_pad($trx->id_transaksi, 4, '0', STR_PAD_LEFT) }}</div>
            @if($terlambat)
              <span class="trx-status-late"><i class="bi bi-exclamation-triangle-fill"></i> Telat {{ $hariTelat }} hari</span>
            @else
              <span class="trx-status-ok"><i class="bi bi-check-circle-fill"></i> Tepat waktu</span>
            @endif
          </div>
          <div class="trx-card-body">
            <div class="trx-info-grid">
              <div>
                <div class="trx-field-k">Pelanggan</div>
                <div class="trx-field-v">{{ $trx->pelanggan->nama_pelanggan ?? '-' }}</div>
              </div>
              <div>
                <div class="trx-field-k">No. Telepon</div>
                <div class="trx-field-v">{{ $trx->pelanggan->no_telp ?? '-' }}</div>
              </div>
              <div>
                <div class="trx-field-k">Barang</div>
                <div class="trx-field-v">
                  {{ $detail->barang->nama_barang ?? '-' }}
                  {{ $detail->ukuran ? '('.$detail->ukuran.')' : '' }}
                </div>
              </div>
              <div>
                <div class="trx-field-k">Jatuh Tempo</div>
                <div class="trx-field-v" style="{{ $terlambat ? 'color:var(--pos-red);font-weight:600' : '' }}">
                  {{ \Carbon\Carbon::parse($trx->tgl_jatuh_tempo)->format('d M Y') }}
                </div>
              </div>
            </div>
            <div class="trx-biaya-box">
              <div class="trx-biaya-row"><span>Total biaya sewa</span><span>Rp {{ number_format($trx->total_biaya, 0, ',', '.') }}</span></div>
              @if(($trx->metode_bayar ?? 'Lunas') === 'DP' && $sisaTagihanTrx > 0)
              <div class="trx-biaya-row"><span>Sisa DP belum lunas</span><span style="color:var(--pos-red)">Rp {{ number_format($sisaTagihanTrx, 0, ',', '.') }}</span></div>
              @endif
              @if($terlambat)
              <div class="trx-biaya-row"><span>Denda {{ $hariTelat }} hari × Rp {{ number_format($dendaVal2, 0, ',', '.') }}</span><span style="color:var(--pos-red)">Rp {{ number_format($totalDendaTrx, 0, ',', '.') }}</span></div>
              @endif
              <div class="trx-biaya-total">
                <div class="trx-biaya-total-lbl">{{ $totalBayarKembaliTrx > 0 ? 'Tagihan saat kembali' : 'Tidak ada tagihan' }}</div>
                <div class="trx-biaya-total-val{{ $terlambat ? ' danger' : '' }}">Rp {{ number_format($totalBayarKembaliTrx, 0, ',', '.') }}</div>
              </div>
            </div>
            <button class="btn-kembalikan{{ $terlambat ? ' danger' : '' }}" onclick="openKonfirmKembali({{ $trx->id_transaksi }})">
              ↩️ {{ $terlambat ? 'Proses Pengembalian + Denda' : 'Konfirmasi Pengembalian' }}
            </button>
          </div>
        </div>
        @endforeach
      </div>
    @endif
  </div>
</div>{{-- end paneKembali --}}

</div>{{-- end pos-page --}}

{{-- ════ SIZE PICKER ════ --}}
<div class="pos-ov" id="sizeOv" onclick="if(event.target===this)closeSizePicker()">
  <div class="pos-spick">
    <div class="sp-head">
      <div><div class="sp-name" id="spName">-</div><div class="sp-price" id="spPrice">-</div></div>
      <button class="sp-close" onclick="closeSizePicker()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sp-body">
      <div class="sp-lbl">Pilih Ukuran</div>
      <div class="sz-grid" id="szGrid"></div>
      <div class="qty-row">
        <div class="qty-lbl">Jumlah</div>
        <div class="qty-ctrl">
          <button class="qty-btn" onclick="adjQty(-1)">−</button>
          <div class="qty-n" id="spQtyN">1</div>
          <button class="qty-btn" onclick="adjQty(1)">+</button>
        </div>
      </div>
      <button class="btn-add-cart" id="spBtnAdd" disabled onclick="addToCart()">+ Tambah ke Keranjang</button>
    </div>
  </div>
</div>

{{-- ════ CUSTOMER PICKER ════ --}}
<div class="pos-ov" id="custOv" onclick="if(event.target===this)closeCustModal()">
  <div class="cust-modal">
    <div class="cm-head">
      <div class="cm-title"><i class="bi bi-people"></i> Pilih Pelanggan</div>
      <button class="cm-close" onclick="closeCustModal()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="cm-search"><input type="text" id="cmSearch" placeholder="Cari nama atau nomor telepon..." oninput="cmFilter()"></div>
    <div class="cm-list" id="cmList"></div>
    <button class="cm-new-btn" onclick="showNewCustForm()"><i class="bi bi-plus-circle"></i> Tambah Pelanggan Baru</button>
    <div class="cm-new-form" id="cmNewForm" style="display:none">
      <div class="cm-form-title">Data Pelanggan Baru</div>
      <div class="cm-form-field"><label>Nama *</label><input type="text" id="cmNewNama" placeholder="Nama lengkap"></div>
      <div class="cm-form-field"><label>No. Telepon *</label><input type="text" id="cmNewTelp" placeholder="08xx-xxxx-xxxx"></div>
      <div class="cm-form-field"><label>Alamat</label><input type="text" id="cmNewAlamat" placeholder="Opsional"></div>
      <div class="cm-form-btns">
        <button class="cm-form-cancel" onclick="hideNewCustForm()">Batal</button>
        <button class="cm-form-save" onclick="saveNewCust()"><i class="bi bi-floppy2-fill"></i> Simpan & Pilih</button>
      </div>
    </div>
  </div>
</div>

{{-- ════ RESI MODAL ════ --}}
<div class="resi-overlay" id="resiOverlay">
  <div class="resi-topbar">
    <button class="resi-back-btn" onclick="closeResi()"><i class="bi bi-arrow-left"></i> Kembali ke kasir</button>
    <div style="margin-left:8px"><button class="resi-fmt-btn active">Resi</button></div>
    <button class="resi-pdf-btn" onclick="printResi()"><i class="bi bi-printer-fill"></i> PDF Resi</button>
  </div>
  <div class="resi-main">
    <div class="resi-card" id="resiCard">
      <div class="resi-card-inner">
        <div class="resi-header-row">
          <div class="resi-store">
            <div class="resi-store-ico">N</div>
            <div><div class="resi-store-name">NM Gallery</div><div class="resi-store-phone">Baju Bodo Collection · Makassar</div></div>
          </div>
          <div class="resi-invoice-box">
            <div class="resi-inv-label">NO. INVOICE</div>
            <div class="resi-inv-no" id="resiInvNo">TRX-XXXXXXXX</div>
            <div class="resi-inv-date" id="resiInvDate">-</div>
          </div>
        </div>
        <div class="resi-kembali-box" id="resiKembaliBox" style="display:none">
          <span class="resi-kembali-lbl"><i class="bi bi-cash-coin"></i> Kembalian</span>
          <span class="resi-kembali-amount" id="resiKembaliAmount">Rp 0</span>
        </div>
        <div class="resi-content-grid">
          <div>
            <div class="resi-sec-label"><i class="bi bi-person-fill"></i> PENERIMA</div>
            <div class="resi-cust-name" id="resiCustName">-</div>
            <div class="resi-cust-addr" id="resiCustAddr">-</div>
            <div style="font-size:10.5px;color:var(--gray-400);margin-top:5px" id="resiPeriode"></div>
          </div>
          <div>
            <div class="resi-sec-label"><i class="bi bi-box-seam-fill"></i> ISI PAKET</div>
            <div class="resi-items-list" id="resiItemsList">-</div>
            <div class="resi-total-row">
              <div class="resi-total-lbl">TOTAL BAYAR</div>
              <div class="resi-total-val" id="resiTotalVal">Rp 0</div>
            </div>
          </div>
        </div>
        <div class="resi-footer-row">
          <div class="resi-print-date" id="resiPrintDate">Dicetak pada: -</div>
          <div class="resi-barcode-wrap">
            <svg id="resiBarcodeSvg"></svg>
            <div class="resi-barcode-code" id="resiBarcodeCode">-</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ════ KONFIRMASI PENGEMBALIAN ════ --}}
<div class="pos-ov" id="konfirmOv" onclick="if(event.target===this)closeKonfirmKembali()">
  <div class="konfirm-modal">
    <div class="km-head">
      <div class="km-title">↩️ Konfirmasi Pengembalian</div>
      <button class="km-close" onclick="closeKonfirmKembali()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="km-body">
      <div class="km-info" id="kmInfo"></div>
      <div class="km-alert" id="kmAlert"></div>
      <div class="km-total">
        <div class="km-total-lbl">Total Tagihan Saat Kembali</div>
        <div class="km-total-val" id="kmTotalVal">Rp 0</div>
      </div>
      <form id="kmForm" method="POST">
        @csrf
        @method('PUT')
        <button type="submit" class="btn-km-konfirm"><i class="bi bi-check-circle"></i> Konfirmasi Pengembalian &amp; Selesai</button>
      </form>
      <button class="btn-km-cancel" onclick="closeKonfirmKembali()">Batal</button>
    </div>
  </div>
</div>

{{-- Toast --}}
<div class="pos-toast" id="posToast"></div>

<form id="posHiddenForm" action="{{ route('transaksi.store.pos') }}" method="POST" style="display:none">@csrf</form>

<script>
const POS_BARANG     = @json($posBarangData);
const POS_PELANGGANS = @json($pelanggans);
const TRX_AKTIF      = @json($trxJsData);
const DENDA_PER_HARI = {{ $dendaPerHari ?? 50000 }};
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const FMT  = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

// ── STATE ──
let posCart=[], posCust=null, posMetode='lunas';
let posPicked=null, posPickedSz=null, posPickedQty=1, posFilter='Semua';
const FILTERS = ['Semua','Tersedia','Disewa','Laundry','Rusak'];

// ── CLOCK (update elemen di topbar app.blade) ──
function updateClock(){
  const now=new Date();
  const clk=document.getElementById('posClock');
  const dt=document.getElementById('posDate');
  if(clk) clk.textContent=String(now.getHours()).padStart(2,'0')+':'+String(now.getMinutes()).padStart(2,'0');
  const days=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const mons=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
  if(dt) dt.textContent=days[now.getDay()]+', '+now.getDate()+' '+mons[now.getMonth()]+' '+now.getFullYear();
}
updateClock();
setInterval(updateClock, 10000);

// ── TAB SWITCHING ──
function switchTab(tab) {
  document.querySelectorAll('.pos-tab[data-tab]').forEach(el=>
    el.classList.toggle('active', el.dataset.tab===tab)
  );
  document.getElementById('panePos').classList.toggle('active', tab==='pos');
  document.getElementById('paneKembali').classList.toggle('active', tab==='kembali');
  history.replaceState(null,'', tab==='kembali' ? '?tab=kembali' : location.pathname);
}
window.switchTab = switchTab;

// ── CHIPS & GRID ──
function posRenderChips(){
  const counts = POS_BARANG.reduce((acc, b) => {
    acc.Semua += 1;
    if (b.available) acc.Tersedia += 1;
    if (b.active_rental) acc.Disewa += 1;
    if (b.status === 'Laundry') acc.Laundry += 1;
    if (b.status === 'Rusak') acc.Rusak += 1;
    return acc;
  }, { Semua: 0, Tersedia: 0, Disewa: 0, Laundry: 0, Rusak: 0 });

  document.getElementById('posChips').innerHTML=FILTERS.map(f=>
    `<div class="pos-chip${f===posFilter?' active':''}" onclick="posSetFilter('${f}')">${f}<span class="pos-chip-count">${counts[f] || 0}</span></div>`
  ).join('');
}
function posSetFilter(f){posFilter=f;posRenderGrid();}
function posRenderGrid(){
  const q=document.getElementById('posSearch').value.toLowerCase();
  const data=POS_BARANG.filter(b=>{
    if(posFilter==='Tersedia'&&!b.available)return false;
    if(posFilter==='Disewa'&&!b.active_rental)return false;
    if(posFilter==='Laundry'&&b.status!=='Laundry')return false;
    if(posFilter==='Rusak'&&b.status!=='Rusak')return false;
    if(q&&!b.nama.toLowerCase().includes(q))return false;
    return true;
  });
  const grid=document.getElementById('posGrid');
  if(!data.length){grid.innerHTML='<div style="grid-column:1/-1;padding:40px;text-align:center;color:#bbb;font-size:12px">Tidak ada barang yang sesuai</div>';posRenderChips();return;}
  grid.innerHTML=data.map(b=>{
    const isOut=!b.available;
    const isRented=!!b.active_rental;
    const stokTot=Object.values(b.stok).reduce((a,v)=>a+v,0);
    const hasFoto=b.foto&&b.foto!=='null';
    const badgeLabel = b.status === 'Laundry' || b.status === 'Rusak'
      ? b.status.toUpperCase()
      : isRented
        ? 'DISEWA'
        : b.available
          ? 'TERSEDIA'
          : 'DISEWA';
    const badgeClass = b.status === 'Laundry'
      ? 'out'
      : b.status === 'Rusak'
        ? 'out'
        : isRented
          ? 'out'
          : 'ok';
    return `<div class="pos-card${isOut?' disewa':''}" onclick="${isOut?'':'openSizePicker('+b.id+')'}">
      <div class="pos-card-img">
        ${hasFoto?`<img src="/${b.foto}" onerror="this.style.display='none'">`:''}
        <span style="font-size:32px${hasFoto?';display:none':''}"><i class='bi bi-bag-heart' style='color:var(--pos-gold-dk);opacity:.4'></i></span>
        <div class="pos-status ${badgeClass}">${badgeLabel}</div>
      </div>
      <div class="pos-card-body">
        <div class="pos-card-name">${b.nama}</div>
        <div class="pos-card-meta">${b.status === 'Laundry' || b.status === 'Rusak'
          ? b.status
          : isOut
            ? 'Stok habis'
            : 'Stok: '+stokTot+' pcs' + (isRented ? ' · Ada yang disewa' : '')}</div>
        <div class="pos-card-price">${FMT(b.harga)}<small>/hari</small></div>
      </div>
    </div>`;
  }).join('');
  posRenderChips();
}

// ── SIZE PICKER ──
function openSizePicker(id){
  posPicked=POS_BARANG.find(b=>b.id===id);
  posPickedSz=null;posPickedQty=1;
  document.getElementById('spName').textContent=posPicked.nama;
  document.getElementById('spPrice').textContent=FMT(posPicked.harga)+' / hari';
  document.getElementById('spQtyN').textContent=1;
  document.getElementById('spBtnAdd').disabled=true;
  const sizes=Object.keys(posPicked.stok);
  document.getElementById('szGrid').innerHTML=sizes.length
    ?sizes.map(s=>`<div class="sz-opt${posPicked.stok[s]===0?' sz-dis':''}" id="sz${s}" onclick="pickSize('${s}')"><div class="sz-lbl">${s}</div><div class="sz-stok">${posPicked.stok[s]} pcs</div></div>`).join('')
    :'<div style="grid-column:1/-1;text-align:center;font-size:11px;color:#bbb;padding:8px">Tidak ada stok</div>';
  document.getElementById('sizeOv').classList.add('show');
}
function closeSizePicker(){document.getElementById('sizeOv').classList.remove('show');}
function pickSize(s){
  if(!posPicked||posPicked.stok[s]===0)return;
  posPickedSz=s;posPickedQty=1;
  document.getElementById('spQtyN').textContent=1;
  document.querySelectorAll('.sz-opt').forEach(e=>e.classList.remove('sz-active'));
  document.getElementById('sz'+s).classList.add('sz-active');
  document.getElementById('spBtnAdd').disabled=false;
}
function adjQty(d){
  if(!posPickedSz)return;
  posPickedQty=Math.min(posPicked.stok[posPickedSz],Math.max(1,posPickedQty+d));
  document.getElementById('spQtyN').textContent=posPickedQty;
}
function addToCart(){
  if(!posPicked||!posPickedSz)return;
  const key=posPicked.id+'-'+posPickedSz;
  const ex=posCart.find(c=>c.key===key);
  if(ex)ex.qty=Math.min(posPicked.stok[posPickedSz],ex.qty+posPickedQty);
  else posCart.push({key,id:posPicked.id,nama:posPicked.nama,size:posPickedSz,qty:posPickedQty,harga:posPicked.harga});
  closeSizePicker();posRenderCart();posRecalc();
  showToast('✅ '+posPicked.nama+' ('+posPickedSz+') ditambahkan');
}

// ── CART ──
function posRenderCart(){
  const badge=document.getElementById('posCartBadge');
  const body=document.getElementById('posCartBody');
  const count=posCart.reduce((s,c)=>s+c.qty,0);
  badge.textContent=count;
  if(!posCart.length){body.innerHTML=`<div class="pos-cart-empty"><div class="pos-cart-empty-ico"><i class="bi bi-cart3"></i></div><div class="pos-cart-empty-txt">Keranjang kosong</div></div>`;return;}
  const dur=getKhDurasi();
  body.innerHTML=posCart.map((c,i)=>{
    const sub=c.harga*c.qty*(dur||1);
    return `<div class="pos-cart-item">
      <div class="pos-item-name">${c.nama}</div>
      <div class="pos-item-sub">Ukuran ${c.size} · ${FMT(c.harga)}/hari</div>
      <div class="pos-item-row">
        <div class="pos-qc">
          <button class="pos-qb" onclick="adjCartQty(${i},-1)">−</button>
          <div class="pos-qn">${c.qty}</div>
          <button class="pos-qb" onclick="adjCartQty(${i},1)">+</button>
        </div>
        <div class="pos-item-price">${FMT(sub)}</div>
      </div>
      <button class="pos-item-del" onclick="removeCart(${i})"><i class="bi bi-x-lg"></i></button>
    </div>`;
  }).join('');
}
function adjCartQty(i,d){
  const b=POS_BARANG.find(x=>x.id===posCart[i].id);
  posCart[i].qty=Math.min(b?(b.stok[posCart[i].size]||99):99,Math.max(1,posCart[i].qty+d));
  posRenderCart();posRecalc();
}
function removeCart(i){posCart.splice(i,1);posRenderCart();posRecalc();}

// ── CUSTOMER PICKER ──
let localPelanggans=[...POS_PELANGGANS];
function openCustModal(){
  document.getElementById('cmSearch').value='';cmFilter();
  document.getElementById('cmNewForm').style.display='none';
  document.getElementById('custOv').classList.add('show');
  setTimeout(()=>document.getElementById('cmSearch').focus(),100);
}
function closeCustModal(){document.getElementById('custOv').classList.remove('show');}
function cmFilter(){
  const q=document.getElementById('cmSearch').value.toLowerCase();
  const list=document.getElementById('cmList');
  const filtered=localPelanggans.filter(p=>p.nama.toLowerCase().includes(q)||p.telp.includes(q));
  if(!filtered.length){list.innerHTML='<div class="cm-empty">Tidak ada pelanggan ditemukan</div>';return;}
  list.innerHTML=filtered.map(p=>`<div class="cm-item" onclick="selectCust(${p.id})"><div class="cm-ava">${p.nama.charAt(0).toUpperCase()}</div><div><div class="cm-name">${p.nama}</div><div class="cm-telp">${p.telp}</div></div></div>`).join('');
}
function selectCust(id){
  posCust=localPelanggans.find(p=>p.id===id);if(!posCust)return;
  document.getElementById('posCustIco').textContent=posCust.nama.charAt(0).toUpperCase();
  document.getElementById('posCustIco').style.fontSize='11px';
  document.getElementById('posCustName').textContent=posCust.nama;
  document.getElementById('posCustName').classList.remove('pos-cust-placeholder');
  document.getElementById('posCustBtn').classList.add('selected');
  closeCustModal();posUpdatePayBtn();
}
function showNewCustForm(){document.getElementById('cmNewForm').style.display='block';}
function hideNewCustForm(){document.getElementById('cmNewForm').style.display='none';}
function saveNewCust(){
  const nama=document.getElementById('cmNewNama').value.trim();
  const telp=document.getElementById('cmNewTelp').value.trim();
  const alamat=document.getElementById('cmNewAlamat').value.trim();
  if(!nama||!telp){showToast('⚠️ Nama dan telepon wajib diisi');return;}
  const fd=new FormData();
  fd.append('nama_pelanggan',nama);fd.append('no_telp',telp);fd.append('alamat',alamat);fd.append('_token',CSRF);
  fetch('{{ route("pelanggan.store") }}',{method:'POST',body:fd})
    .then(r=>r.json()).then(d=>{
      if(d.success){
        const p={id:d.data.id_pelanggan,nama:d.data.nama_pelanggan,telp:d.data.no_telp,alamat:d.data.alamat||''};
        localPelanggans.push(p);selectCust(p.id);hideNewCustForm();showToast('✅ Pelanggan baru disimpan');
      } else showToast('❌ '+(d.message||'Gagal simpan'));
    }).catch(()=>showToast('❌ Kesalahan jaringan'));
}

// ── DATES & RECALC ──
function getKhDurasi(){
  const s=document.getElementById('posTglSewa').value;
  const k=document.getElementById('posTglKembali').value;
  if(!s||!k)return 0;
  return Math.max(1,Math.ceil((new Date(k)-new Date(s))/86400000));
}
function posRecalc(){
  const dur=getKhDurasi();
  const chip=document.getElementById('posDurasiChip');
  if(dur>0){chip.style.display='inline-block';chip.textContent=dur+' hari';}else chip.style.display='none';
  const perHari=posCart.reduce((s,c)=>s+c.harga*c.qty,0);
  const rawSub=perHari*(dur||0);
  const diskon=parseInt(document.getElementById('posDiskon').value)||0;
  const ongkir=parseInt(document.getElementById('posOngkir').value)||0;
  const total=Math.max(0,rawSub-diskon+ongkir);
  document.getElementById('posSubtotal').textContent=FMT(rawSub);
  document.getElementById('posDiskonRow').style.display=diskon>0?'flex':'none';
  document.getElementById('posOngkirRow').style.display=ongkir>0?'flex':'none';
  if(diskon>0)document.getElementById('posDiskonVal').textContent='-'+FMT(diskon);
  if(ongkir>0)document.getElementById('posOngkirVal').textContent='+'+FMT(ongkir);
  document.getElementById('posTotal').textContent=FMT(total);
  posHitungKembali();posUpdatePayBtn();posRenderCart();
}
function posHitungKembali(){
  const total=parseTotal();
  const bayar=parseInt(document.getElementById('posJumlahBayar').value)||0;
  const kembali=bayar-total;
  const el=document.getElementById('posKembali');
  if(kembali>=0){el.textContent=FMT(kembali);el.className='pos-kembali-val';}
  else{el.textContent='-'+FMT(-kembali);el.className='pos-kembali-val minus';}
  posUpdatePayBtn();
}
function parseTotal(){
  const t=document.getElementById('posTotal').textContent.replace('Rp','').replace(/\./g,'').trim();
  return parseInt(t)||0;
}
function posSetMetode(m){
  posMetode=m;
  document.getElementById('posOptLunas').classList.toggle('active',m==='lunas');
  document.getElementById('posOptDP').classList.toggle('active',m==='dp');
  posUpdatePayBtn();
}
function posQuickAmount(amt){
  const cur=parseInt(document.getElementById('posJumlahBayar').value)||0;
  document.getElementById('posJumlahBayar').value=cur+amt;
  posHitungKembali();
}
function posUpdatePayBtn(){
  const total=parseTotal();
  const bayar=parseInt(document.getElementById('posJumlahBayar').value)||0;
  const dur=getKhDurasi();
  const valid=posCart.length>0&&posCust&&dur>0&&(posMetode==='dp'?true:bayar>=total);
  document.getElementById('posBtnPay').disabled=!valid;
}

// ── PROCESS PAYMENT ──
function posProses(){
  const total=parseTotal();
  const bayar=parseInt(document.getElementById('posJumlahBayar').value)||0;
  const dur=getKhDurasi();
  if(!posCart.length){showToast('⚠️ Keranjang kosong');return;}
  if(!posCust){showToast('⚠️ Pilih pelanggan terlebih dahulu');return;}
  if(dur<1){showToast('⚠️ Pilih periode sewa');return;}
  if(posMetode==='lunas'&&bayar<total){showToast('⚠️ Jumlah bayar kurang dari total');return;}
  const btn=document.getElementById('posBtnPay');
  btn.disabled=true;btn.innerHTML='<i class="bi bi-arrow-clockwise"></i> Memproses...';
  const diskon=parseInt(document.getElementById('posDiskon').value)||0;
  const ongkir=parseInt(document.getElementById('posOngkir').value)||0;
  const payload={
    _token:CSRF,id_pelanggan:posCust.id,nama_pelanggan:posCust.nama,
    no_telp:posCust.telp,alamat:posCust.alamat||'',id_barang:posCart[0].id,
    items:JSON.stringify(posCart.map(c=>({size:c.size,jumlah:c.qty,harga:c.harga}))),
    tgl_sewa:document.getElementById('posTglSewa').value,
    tgl_jatuh_tempo:document.getElementById('posTglKembali').value,
    metode_bayar:posMetode==='lunas'?'Lunas':'DP',
    jumlah_dp:posMetode==='dp'?Math.round(total*0.5):total,
    diskon,ongkir,jumlah_bayar:bayar,
  };
  fetch('{{ route("transaksi.store.pos") }}',{
    method:'POST',
    headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
    body:JSON.stringify(payload),
  })
  .then(r=>r.json()).then(d=>{
    if(d.success)showResi(d);
    else showToast('❌ '+(d.message||'Gagal memproses'));
  })
  .catch(()=>showToast('❌ Terjadi kesalahan jaringan'))
  .finally(()=>{btn.disabled=false;btn.innerHTML='<i class="bi bi-receipt-cutoff"></i> Proses Pembayaran';});
}

// ── RESI ──
function showResi(d){
  document.getElementById('resiInvNo').textContent=d.invoice_no;
  document.getElementById('resiInvDate').textContent=d.tgl_created;
  document.getElementById('resiCustName').textContent=d.pelanggan.nama;
  document.getElementById('resiCustAddr').textContent=d.pelanggan.alamat||'Makassar';
  document.getElementById('resiPeriode').textContent='Sewa: '+d.tgl_sewa+' s/d '+d.tgl_jatuh;
  document.getElementById('resiItemsList').innerHTML=d.items.map(it=>
    `<div class="resi-item-row"><span>${it.nama} (${it.size}) <span style="color:#aaa">×${it.qty}</span></span></div>`
  ).join('');

  let payBlock = document.getElementById('resiTotalVal').parentNode;
  payBlock.className = '';
  payBlock.innerHTML = '';
  
  if (d.metode_bayar === 'DP') {
    payBlock.innerHTML = `
      <div style="display:flex;justify-content:space-between;padding-top:9px;margin-top:7px;border-top:1px solid var(--gray-200)">
        <div style="font-size:10.5px;font-weight:700;color:var(--gray-500)">TOTAL BIAYA</div>
        <div style="font-family:var(--ff-mono);font-size:13px;font-weight:700">${FMT(d.total_biaya)}</div>
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:6px">
        <div style="font-size:10.5px;font-weight:700;color:var(--gray-500)">DP DIBAYAR</div>
        <div style="font-family:var(--ff-mono);font-size:14px;font-weight:800;color:var(--green)">${FMT(d.jumlah_dp)}</div>
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:6px">
        <div style="font-size:10.5px;font-weight:700;color:#c0392b">SISA TAGIHAN</div>
        <div style="font-family:var(--ff-mono);font-size:14px;font-weight:800;color:#c0392b">${FMT(d.sisa_tagihan)}</div>
      </div>
    `;
    if (d.is_pengembalian) {
        payBlock.innerHTML += `
          <div style="display:flex;justify-content:space-between;margin-top:6px">
            <div style="font-size:10.5px;font-weight:700;color:#e67e22">DENDA KELAMBATAN</div>
            <div style="font-family:var(--ff-mono);font-size:13px;font-weight:700;color:#e67e22">${FMT(d.total_denda || 0)}</div>
          </div>
          <div style="display:flex;justify-content:space-between;padding-top:6px;margin-top:6px;border-top:1px dashed var(--gray-200)">
            <div style="font-size:11px;font-weight:800;color:var(--black)">TOTAL PELUNASAN KEMBALI</div>
            <div style="font-family:var(--ff-mono);font-size:16px;font-weight:800;color:var(--black)">${FMT(d.total_bayar_kembali || 0)}</div>
          </div>
        `;
    }
  } else {
    payBlock.innerHTML = `
      <div style="display:flex;justify-content:space-between;padding-top:9px;margin-top:7px;border-top:1px solid var(--gray-200)">
        <div style="font-size:10.5px;font-weight:700;color:var(--gray-500)">TOTAL BAYAR</div>
        <div style="font-family:var(--ff-mono);font-size:15px;font-weight:800">${FMT(d.total_biaya)}</div>
      </div>
    `;
    if (d.is_pengembalian && d.total_denda > 0) {
        payBlock.innerHTML += `
          <div style="display:flex;justify-content:space-between;margin-top:6px">
            <div style="font-size:10.5px;font-weight:700;color:#e67e22">DENDA KELAMBATAN</div>
            <div style="font-family:var(--ff-mono);font-size:13px;font-weight:700;color:#e67e22">${FMT(d.total_denda || 0)}</div>
          </div>
          <div style="display:flex;justify-content:space-between;padding-top:6px;margin-top:6px;border-top:1px dashed var(--gray-200)">
            <div style="font-size:11px;font-weight:800;color:var(--black)">TOTAL BAYAR KEMBALI</div>
            <div style="font-family:var(--ff-mono);font-size:16px;font-weight:800;color:var(--black)">${FMT(d.total_denda || 0)}</div>
          </div>
        `;
    }
  }

  document.getElementById('resiPrintDate').textContent='Dicetak pada: '+d.printed_at;
  const kembali=(parseInt(document.getElementById('posJumlahBayar').value)||0)-d.total_biaya;
  if(!d.is_pengembalian && d.metode_bayar==='Lunas'&&kembali>=0){
    document.getElementById('resiKembaliBox').style.display='flex';
    document.getElementById('resiKembaliAmount').textContent=FMT(kembali);
  } else { document.getElementById('resiKembaliBox').style.display='none'; }
  try{JsBarcode('#resiBarcodeSvg',d.invoice_no,{format:'CODE128',width:1.5,height:36,displayValue:false,background:'#ffffff',lineColor:'#222'});}catch(e){}
  document.getElementById('resiBarcodeCode').textContent=d.invoice_no;
  document.getElementById('resiOverlay').classList.add('show');
  posReset(true);
}
let reloadOnCloseResi = false;
function closeResi(){
  document.getElementById('resiOverlay').classList.remove('show');
  if (reloadOnCloseResi) {
      window.location.href = "{{ route('transaksi.index') }}?tab=kembali";
  }
}
function printResi(){
  const card=document.getElementById('resiCard').outerHTML;
  const win=window.open('','_blank','width=600,height=700');
  win.document.write(`<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Resi NM Gallery</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
    <style>body{font-family:'Plus Jakarta Sans',sans-serif;background:#f8f7f4;display:flex;justify-content:center;padding:24px;-webkit-print-color-adjust:exact}
    :root{--gold:#C9A84C;--gold-lt:#e0c06e;--gold-dk:#a07830;--black:#0a0a0a;--gray-200:#e4e4e7;--gray-400:#a1a1aa;--gray-500:#71717a;--green:#1a8050;--ff-mono:'JetBrains Mono',monospace}
    .resi-card{background:#fff;border:1px solid #e4e4e7;border-radius:14px;overflow:hidden;width:100%;max-width:520px;border-left:4px solid #C9A84C}
    .resi-card-inner{padding:20px 22px}.resi-header-row{display:flex;align-items:flex-start;justify-content:space-between;padding-bottom:16px;margin-bottom:16px;border-bottom:1px dashed #e4e4e7}
    .resi-store{display:flex;align-items:center;gap:10px}.resi-store-ico{width:40px;height:40px;border-radius:9px;background:linear-gradient(135deg,#e0c06e,#C9A84C,#a07830);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:#0a0a0a}
    .resi-store-name{font-size:14px;font-weight:700}.resi-store-phone{font-size:10.5px;color:#a1a1aa;margin-top:2px}
    .resi-invoice-box{text-align:right}.resi-inv-label{font-size:9px;font-weight:700;color:#a1a1aa;text-transform:uppercase;letter-spacing:1px}
    .resi-inv-no{font-family:'JetBrains Mono',monospace;font-size:16px;font-weight:700;color:#a07830;margin-top:2px}
    .resi-inv-date{font-size:10.5px;color:#71717a;margin-top:3px}
    .resi-kembali-box{background:rgba(26,128,80,.06);border:1px solid rgba(26,128,80,.2);border-radius:6px;padding:9px 13px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center}
    .resi-kembali-lbl{font-size:11px;font-weight:600;color:#1a8050}.resi-kembali-amount{font-family:'JetBrains Mono',monospace;font-size:14px;font-weight:700;color:#1a8050}
    .resi-content-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;padding:14px 0;border-bottom:1px dashed #e4e4e7;margin-bottom:14px}
    .resi-sec-label{font-size:8.5px;font-weight:700;color:#a1a1aa;text-transform:uppercase;letter-spacing:1px;margin-bottom:7px}
    .resi-cust-name{font-size:13.5px;font-weight:700;margin-bottom:3px}.resi-cust-addr{font-size:11px;color:#71717a;line-height:1.5}
    .resi-items-list{font-size:11.5px;color:#52525b;line-height:1.8}.resi-item-row{display:flex;justify-content:space-between;padding:2px 0}
    .resi-total-row{display:flex;justify-content:space-between;padding-top:9px;margin-top:7px;border-top:1px solid #e4e4e7}
    .resi-total-lbl{font-size:10.5px;font-weight:700;color:#71717a;text-transform:uppercase;letter-spacing:.5px}
    .resi-total-val{font-family:'JetBrains Mono',monospace;font-size:15px;font-weight:800}
    .resi-footer-row{display:flex;align-items:flex-end;justify-content:space-between;padding-top:14px;border-top:1px dashed #f4f4f5}
    .resi-print-date{font-size:10px;color:#a1a1aa}.resi-barcode-wrap{text-align:center}
    .resi-barcode-code{font-family:'JetBrains Mono',monospace;font-size:9px;color:#71717a;margin-top:3px;letter-spacing:1px}
    @media print{body{padding:0}@page{margin:10mm}}</style>
  </head><body>${card}
  <script>window.onload=function(){try{JsBarcode('svg',document.querySelector('.resi-barcode-code').textContent,{format:'CODE128',width:1.5,height:36,displayValue:false,background:'#ffffff',lineColor:'#222'})}catch(e){}setTimeout(()=>window.print(),400)}<\/script>
  </body></html>`);
  win.document.close();
}

// ── KONFIRMASI PENGEMBALIAN ──
function openKonfirmKembali(trxId){
  const trx=TRX_AKTIF.find(t=>t.id===trxId);if(!trx)return;
  document.getElementById('kmInfo').innerHTML=`
    <div class="km-info-row"><span>No. Transaksi</span><span style="font-family:monospace;font-weight:600">${trx.no_trx}</span></div>
    <div class="km-info-row"><span>Pelanggan</span><span style="font-weight:600">${trx.pelanggan}</span></div>
    <div class="km-info-row"><span>Barang</span><span>${trx.barang} (${trx.ukuran})</span></div>
    <div class="km-info-row"><span>Tgl Sewa → Jatuh Tempo</span><span>${trx.tgl_sewa} → ${trx.tgl_jatuh}</span></div>
    <div class="km-info-row"><span>Total biaya sewa</span><span>${FMT(trx.total_biaya)}</span></div>
    ${trx.sisa_tagihan>0?`<div class="km-info-row"><span>Sisa DP belum lunas</span><span style="color:var(--pos-red)">${FMT(trx.sisa_tagihan)}</span></div>`:''}
    ${trx.terlambat?`<div class="km-info-row"><span>Denda (${trx.hari_telat} hari × ${FMT(trx.denda_per_hari)})</span><span style="color:var(--pos-red)">${FMT(trx.total_denda)}</span></div>`:''}
  `;
  const alertEl=document.getElementById('kmAlert');
  if(trx.terlambat){
    alertEl.className='km-alert late';
    alertEl.innerHTML=`<i class="bi bi-exclamation-triangle-fill"></i> Terlambat <strong>${trx.hari_telat} hari</strong> dari jatuh tempo ${trx.tgl_jatuh}. Denda: <strong>${FMT(trx.total_denda)}</strong>`;
  } else {
    alertEl.className='km-alert ok';
    alertEl.innerHTML='<i class="bi bi-check-circle-fill"></i> Pengembalian tepat waktu — tidak ada denda';
  }
  const totalEl=document.getElementById('kmTotalVal');
  totalEl.textContent=FMT(trx.total_bayar_kembali);
  totalEl.className='km-total-val'+(trx.total_bayar_kembali>0?' danger':'');
  document.getElementById('kmForm').action=`/transaksi/${trxId}`;
  document.getElementById('konfirmOv').classList.add('show');
}
function closeKonfirmKembali(){document.getElementById('konfirmOv').classList.remove('show');}
window.openKonfirmKembali=openKonfirmKembali;
window.closeKonfirmKembali=closeKonfirmKembali;

document.getElementById('kmForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const btn = this.querySelector('button');
  btn.disabled = true;
  btn.innerHTML = 'Memproses...';
  
  let formData = new FormData(this);
  formData.append('wantsJson', 'true');

  fetch(this.action, {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    }
  }).then(r => r.json())
    .then(d => {
      if(d.success) {
        closeKonfirmKembali();
        reloadOnCloseResi = true;
        showResi(d);
      } else {
        showToast('❌ ' + (d.message || 'Gagal'));
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Konfirmasi Pengembalian &amp; Selesai';
      }
    }).catch(e => {
        showToast('❌ Kesalahan jaringan');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Konfirmasi Pengembalian &amp; Selesai';
    });
});

// ── RESET & HELPERS ──
function posReset(silent){
  posCart=[];posCust=null;posMetode='lunas';
  posRenderCart();posRecalc();
  document.getElementById('posJumlahBayar').value='';
  document.getElementById('posKembali').textContent='Rp 0';
  document.getElementById('posKembali').className='pos-kembali-val';
  document.getElementById('posCustIco').innerHTML='<i class="bi bi-person" style="font-size:12px"></i>';
  document.getElementById('posCustName').textContent='Pilih pelanggan...';
  document.getElementById('posCustName').classList.add('pos-cust-placeholder');
  document.getElementById('posCustBtn').classList.remove('selected');
  document.getElementById('posDiskon').value='0';
  document.getElementById('posOngkir').value='0';
  document.getElementById('posOptLunas').classList.add('active');
  document.getElementById('posOptDP').classList.remove('active');
  if(!silent)showToast('🗑️ Keranjang dikosongkan');
  const now=new Date(),d3=new Date();d3.setDate(d3.getDate()+3);
  document.getElementById('posTglSewa').value=now.toISOString().slice(0,10);
  document.getElementById('posTglKembali').value=d3.toISOString().slice(0,10);
  posRecalc();
}
function showToast(msg){
  swalToast(msg);
}

// ── INIT ──
posRenderChips();
posRenderGrid();
const _now=new Date(),_d3=new Date();_d3.setDate(_d3.getDate()+3);
document.getElementById('posTglSewa').value=_now.toISOString().slice(0,10);
document.getElementById('posTglKembali').value=_d3.toISOString().slice(0,10);
posRecalc();

// Bind tab buttons
document.querySelectorAll('.pos-tab[data-tab]').forEach(function(btn){
  btn.addEventListener('click',function(){switchTab(this.dataset.tab);});
});
// Auto-switch dari URL ?tab=kembali
(function(){
  if(new URLSearchParams(location.search).get('tab')==='kembali')switchTab('kembali');
})();

document.addEventListener('keydown',e=>{
  if(e.key!=='Escape')return;
  closeSizePicker();closeCustModal();closeResi();closeKonfirmKembali();
});
</script>

@endsection