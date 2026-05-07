<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE transaksi DROP FOREIGN KEY transaksi_id_user_foreign');
        DB::statement('ALTER TABLE transaksi MODIFY id_user BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE transaksi ADD CONSTRAINT transaksi_id_user_foreign FOREIGN KEY (id_user) REFERENCES pengguna(id_user) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE transaksi DROP FOREIGN KEY transaksi_id_user_foreign');
        DB::statement('ALTER TABLE transaksi MODIFY id_user BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE transaksi ADD CONSTRAINT transaksi_id_user_foreign FOREIGN KEY (id_user) REFERENCES pengguna(id_user) ON DELETE RESTRICT ON UPDATE CASCADE');
    }
};