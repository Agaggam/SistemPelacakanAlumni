<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->constrained('alumni')->onDelete('cascade');
            $table->string('status_sebelum');
            $table->string('status_sesudah');
            $table->float('skor_kecocokan')->nullable();
            $table->text('query_pencarian')->nullable();
            $table->text('hasil_mentah')->nullable();
            $table->text('catatan')->nullable();
            $table->string('diverifikasi_oleh')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_histories');
    }
};
