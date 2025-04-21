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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            //$table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('nama_product');
            $table->text('deskripsi');
            $table->string('harga');
            $table->integer('stock');
            $table->string('berat');
            $table->string('foto_cover');   
            $table->enum('status_produk', ['draft', 'publish']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
