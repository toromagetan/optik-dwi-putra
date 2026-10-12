<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('announcement_active')->default(false);
            $table->string('announcement_text')->nullable();
            $table->string('announcement_link')->nullable();
            $table->text('operating_hours')->nullable();
            $table->json('benefits')->nullable();
        });
    }
    public function down(): void {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['announcement_active', 'announcement_text', 'announcement_link', 'operating_hours', 'benefits']);
        });
    }
};
