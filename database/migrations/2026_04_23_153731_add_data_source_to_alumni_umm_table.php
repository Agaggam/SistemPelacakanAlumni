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
        Schema::table('alumni_umm', function (Blueprint $table) {
            $table->string('data_source')->nullable()->after('sosmed_perusahaan')
                  ->comment('scraped | generated | manual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumni_umm', function (Blueprint $table) {
            $table->dropColumn('data_source');
        });
    }
};
