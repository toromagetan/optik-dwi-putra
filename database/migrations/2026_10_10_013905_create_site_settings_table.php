<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name')->default('Optik Dwi Putra');
            $table->string('whatsapp_number')->nullable();
            $table->string('theme_color')->default('#000000');
            $table->string('hero_title')->nullable();
            $table->string('hero_subtitle')->nullable();
            $table->text('usp_text')->nullable(); // Unique Selling Proposition
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('site_settings'); }
};
