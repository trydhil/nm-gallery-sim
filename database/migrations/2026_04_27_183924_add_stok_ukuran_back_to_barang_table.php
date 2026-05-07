<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration sebelumnya (remove_ukuran_stok) menghapus kolom stok & ukuran,
     * tapi BarangController dan Model masih menggunakannya.
     * Ini menambahkan kembali kedua kolom tersebut dengan tipe yang benar.
     */
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            if (!Schema::hasColumn('barang', 'ukuran')) {
                // ukuran menyimpan string seperti "S, M, L, XL"
                $table->string('ukuran')->nullable()->after('nama_barang');
            }
            if (!Schema::hasColumn('barang', 'stok')) {
                // stok menyimpan JSON: {"S": 2, "M": 3, "L": 1}
                $table->text('stok')->nullable()->after('ukuran');
            }
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn(['ukuran', 'stok']);
        });
    }
};