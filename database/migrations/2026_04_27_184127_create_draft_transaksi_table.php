<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel untuk menyimpan draft transaksi sementara.
     * Digunakan saat pelanggan masih ragu-ragu menyewa.
     * Data bisa di-load kembali ke form kapan saja.
     */
    public function up(): void
    {
        Schema::create('draft_transaksi', function (Blueprint $table) {
            $table->id('id_draft');
            $table->foreignId('id_user')->constrained('pengguna', 'id_user')->onDelete('cascade');
            $table->string('nama_pelanggan', 100);
            $table->string('no_telp', 15);
            $table->text('alamat')->nullable();
            $table->foreignId('id_barang')->constrained('barang', 'id_barang')->onDelete('cascade');
            $table->string('ukuran_dipilih')->nullable(); // JSON: {"S":1,"M":2}
            $table->datetime('tgl_sewa')->nullable();
            $table->datetime('tgl_jatuh_tempo')->nullable();
            $table->decimal('total_biaya', 12, 2)->default(0);
            $table->enum('metode_bayar', ['Lunas', 'DP'])->default('Lunas');
            $table->decimal('jumlah_dp', 12, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draft_transaksi');
    }
};