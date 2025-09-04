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
        Schema::create('pengirimen', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->index();
            $table->string('kurir')->nullable();
            $table->string('plat_nomor')->nullable();
            $table->string('kode_resi')->nullable();
            $table->enum('status_pengiriman', [
                'dibuat',
                'dijadwalkan',
                'kurir_ditugaskan',
                'dalam_proses',
                'tiba'
            ])->default('dibuat');
            $table->timestamp('estimasi_tiba')->nullable();
            $table->string('bukti_pengiriman')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengirimen');
    }
};
