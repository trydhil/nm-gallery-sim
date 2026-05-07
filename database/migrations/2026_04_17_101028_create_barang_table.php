<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration ini dibuat karena tabel 'barang' sebelumnya dibuat secara manual
 * via phpMyAdmin, sehingga tidak ada file migration-nya.
 * 
 * Akibatnya, saat migrate:fresh dijalankan, tabel 'detail_transaksi' gagal
 * dibuat karena foreign key ke 'barang' tidak bisa dibentuk (tabel belum ada).
 * 
 * Struktur di sini mencerminkan persis tabel yang ada di database aktual,
 * termasuk kolom 'warna' yang ada di database tapi tidak ada di model Barang.
 * 
 * Urutan eksekusi: nama file ini ('_create_barang_table') secara alfabetikal
 * berada SEBELUM '_create_detail_transaksi_table', sehingga tabel 'barang'
 * selalu dibuat duluan — memenuhi syarat foreign key di detail_transaksi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->id('id_barang');

            $table->string('nama_barang', 100);

            // Kolom 'warna' ada di database aktual meskipun tidak ada
            // di Model $fillable. Kita tetap buat di migration agar
            // struktur database konsisten dengan data yang sudah ada.
            $table->string('warna')->nullable();

            // Menyimpan label ukuran sebagai string, contoh: "S, M, L, XL"
            $table->string('ukuran')->nullable();

            $table->string('status_barang', 50)->default('Tersedia');
            $table->string('foto')->nullable();
            $table->decimal('harga_sewa', 12, 2);

            // Menyimpan stok per ukuran sebagai JSON, contoh: {"S":2,"M":3,"L":1}
            // Tipe TEXT digunakan agar kompatibel dengan data lama yang berisi
            // angka biasa (mis. "1") sebelum fitur stok-per-ukuran ditambahkan.
            $table->text('stok')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};