<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom untuk mendukung pembayaran DP vs Lunas.
     * Juga menambahkan ukuran di detail_transaksi yang sebelumnya tidak diisi.
     */
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksi', 'metode_bayar')) {
                // Lunas = bayar penuh di awal, DP = bayar sebagian dulu
                $table->enum('metode_bayar', ['Lunas', 'DP'])->default('Lunas')->after('status_transaksi');
            }
            if (!Schema::hasColumn('transaksi', 'jumlah_dp')) {
                // Berapa yang sudah dibayar di depan (saat sewa)
                $table->decimal('jumlah_dp', 12, 2)->default(0)->after('metode_bayar');
            }
            if (!Schema::hasColumn('transaksi', 'sisa_tagihan')) {
                // Sisa yang harus dibayar saat pengembalian
                $table->decimal('sisa_tagihan', 12, 2)->default(0)->after('jumlah_dp');
            }
        });

        // Tambah kolom ukuran di detail_transaksi jika belum ada
        Schema::table('detail_transaksi', function (Blueprint $table) {
            if (!Schema::hasColumn('detail_transaksi', 'ukuran')) {
                $table->string('ukuran')->nullable()->after('id_barang');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn(['metode_bayar', 'jumlah_dp', 'sisa_tagihan']);
        });
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropColumn('ukuran');
        });
    }
};