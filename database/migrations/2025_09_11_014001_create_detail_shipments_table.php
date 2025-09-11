<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detail_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_shipment')->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('detail_transaksi_id')->constrained('detail_transactions')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_shipments');
    }
};
