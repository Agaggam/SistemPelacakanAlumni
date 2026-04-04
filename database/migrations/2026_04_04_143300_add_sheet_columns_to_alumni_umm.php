<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_umm', function (Blueprint $table) {
            $table->string('fakultas')->nullable()->after('prodi');
            $table->string('tahun_masuk')->nullable()->after('fakultas');
            $table->string('tanggal_lulus')->nullable()->after('tahun_masuk');
        });

        // Add index for faster search on 142K records
        Schema::table('alumni_umm', function (Blueprint $table) {
            $table->index('nama');
            $table->index('nim');
            $table->index('prodi');
            $table->index('fakultas');
        });
    }

    public function down(): void
    {
        Schema::table('alumni_umm', function (Blueprint $table) {
            $table->dropIndex(['nama']);
            $table->dropIndex(['nim']);
            $table->dropIndex(['prodi']);
            $table->dropIndex(['fakultas']);
            $table->dropColumn(['fakultas', 'tahun_masuk', 'tanggal_lulus']);
        });
    }
};
