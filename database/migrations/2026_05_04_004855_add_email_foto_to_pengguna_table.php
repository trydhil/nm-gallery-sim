<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->string('email')->nullable()->after('username');
            $table->string('foto')->nullable()->after('role');
        });
    }
    public function down(): void {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn(['email', 'foto']);
        });
    }
};