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
        Schema::create('history_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_shipment')->constrained('shipments')->cascadeOnDelete();
            $table->enum('status_pengiriman', [
                'dibuat',
                'dijadwalkan',
                'kurir_ditugaskan',
                'dalam_proses',
                'tiba',
                'diterima',
                'batal',
            ])->default('dibuat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_shipments');
    }
};
