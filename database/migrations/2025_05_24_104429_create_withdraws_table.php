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
        Schema::create('withdraws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->integer('jumlah');
            $table->enum('status', ['pending', 'accepted ', 'rejected']);
            $table->text('catatan');
            $table->enum('metode', ['bank_transfer', 'gopay', 'ovo', 'dana','shopeePay'])->default('bank_transfer');
            $table->integer('rekening_tujuan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraws');
    }
};
