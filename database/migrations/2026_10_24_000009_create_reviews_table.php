<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ulasan/testimoni pelanggan untuk seksi "Apa Kata Pelanggan"
     * di beranda (instruksi pemilik 24 Okt 2026) — termasuk ulasan
     * berbentuk gambar tangkapan layar (screenshot chat/Google).
     * Dikelola penuh dari panel admin.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('source')->nullable();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('text')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
