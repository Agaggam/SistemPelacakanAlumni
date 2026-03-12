<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique();
            $table->string('nama');
            $table->string('prodi');
            $table->string('fakultas')->nullable();
            $table->year('angkatan');
            $table->year('tahun_lulus')->nullable();
            $table->string('email')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('domisili')->nullable();
            $table->enum('status', [
                'Belum Dilacak',
                'Teridentifikasi dari PDDIKTI',
                'Perlu Verifikasi Manual',
                'Belum Ditemukan',
            ])->default('Belum Dilacak');
            $table->float('skor_kecocokan')->nullable();
            $table->json('data_pddikti')->nullable();
            $table->timestamp('last_tracked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni');
    }
};
