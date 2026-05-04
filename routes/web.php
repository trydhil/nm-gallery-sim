<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PenggunaController;

// ─── Root → login ─────────────────────────────────────────────────────────────
Route::get('/', [AuthController::class, 'showLogin'])->name('home');

// ─── Guest routes ─────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// ─── Authenticated routes ──────────────────────────────────────────────────────
Route::middleware('auth.session')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');

    // ── Inventaris — semua role ──────────────────────────────────────────────
    Route::get('/barang', [BarangController::class, 'index'])->name('barang.index');
    Route::post('/barang/{id}/stok', [BarangController::class, 'adjustStok'])
         ->name('barang.adjust-stok');

    // ── Transaksi — Owner DAN Karyawan (Owner sebelumnya tidak bisa akses) ──
    Route::middleware('role:Karyawan,Owner')->group(function () {
        Route::get('/transaksi',         [TransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('/transaksi/create',  [TransaksiController::class, 'create'])->name('transaksi.create');
        Route::post('/transaksi',        [TransaksiController::class, 'store'])->name('transaksi.store');
        Route::get('/transaksi/{id}',    [TransaksiController::class, 'show'])->name('transaksi.show');
        Route::put('/transaksi/{id}',    [TransaksiController::class, 'update'])->name('transaksi.update');
        Route::delete('/transaksi/{id}', [TransaksiController::class, 'destroy'])->name('transaksi.destroy');

        // PDF nota
        Route::get('/transaksi/{id}/print-sewa',    [TransaksiController::class, 'printPdf'])->name('transaksi.print');
        Route::get('/transaksi/{id}/print-kembali', [TransaksiController::class, 'printReturnPdf'])->name('transaksi.print.kembali');
        Route::post('/transaksi/preview-pdf',       [TransaksiController::class, 'previewPdf'])->name('transaksi.preview-pdf');

        // Draft
        Route::post('/draft/save',       [TransaksiController::class, 'saveDraft'])->name('draft.save');
        Route::get('/draft/list',        [TransaksiController::class, 'getDrafts'])->name('draft.list');
        Route::delete('/draft/{id}',     [TransaksiController::class, 'deleteDraft'])->name('draft.delete');

        Route::post('/transaksi/pos/store', [TransaksiController::class, 'storePos'])->name('transaksi.store.pos');
    });

    // ── Owner-only routes ────────────────────────────────────────────────────
    Route::middleware('role:Owner')->group(function () {

        // Barang CRUD
        Route::post('/barang',            [BarangController::class, 'store'])->name('barang.store');
        Route::match(['put','post'], '/barang/{id}', [BarangController::class, 'update'])->name('barang.update');
        Route::delete('/barang/{id}',     [BarangController::class, 'destroy'])->name('barang.destroy');

        // Laporan
        Route::get('/laporan',                [LaporanController::class, 'index'])->name('laporan');
        Route::get('/laporan/export/excel',   [LaporanController::class, 'exportExcel'])->name('laporan.export.excel');
        Route::get('/laporan/export/pdf',     [LaporanController::class, 'exportPdf'])->name('laporan.export.pdf');

        // Pengaturan
        Route::get('/pengaturan',             [PengaturanController::class, 'index'])->name('pengaturan');
        Route::post('/pengaturan/update-tarif',[PengaturanController::class, 'updateTarif'])->name('pengaturan.update.tarif');

        // Kelola User (baru)
        Route::get('/pengguna',           [PenggunaController::class, 'index'])->name('pengguna.index');
        Route::post('/pengguna',          [PenggunaController::class, 'store'])->name('pengguna.store');
        Route::put('/pengguna/{id}',      [PenggunaController::class, 'update'])->name('pengguna.update');
        Route::delete('/pengguna/{id}',   [PenggunaController::class, 'destroy'])->name('pengguna.destroy');
    });

    // ── Pelanggan — semua role ───────────────────────────────────────────────
    Route::get('/pelanggan/export-pdf', [PelangganController::class, 'exportPDF'])->name('pelanggan.export.pdf');
    Route::resource('pelanggan', PelangganController::class);
});