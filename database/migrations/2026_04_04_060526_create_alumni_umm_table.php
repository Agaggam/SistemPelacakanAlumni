<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alumni_umm', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nim')->nullable();
            $table->string('prodi')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('email')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('tempat_kerja')->nullable();
            $table->string('alamat_kerja')->nullable();
            $table->string('posisi')->nullable();
            $table->string('status_kerja')->nullable(); // PNS, Swasta, Wirausaha
            $table->string('sosmed_perusahaan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_umm');
    }
};
