<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('instagram_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->string('warranty_url')->nullable();
            $table->string('size_guide_url')->nullable();
            $table->text('address')->nullable();
        });
    }
    public function down(): void {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['instagram_url', 'facebook_url', 'tiktok_url', 'warranty_url', 'size_guide_url', 'address']);
        });
    }
};
