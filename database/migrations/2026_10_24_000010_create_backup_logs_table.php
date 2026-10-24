<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->unsignedBigInteger('size_bytes')->nullable();
            // Status per tahap: pending | success | failed | skipped
            $table->string('local_status', 16)->default('pending');
            $table->string('drive_status', 16)->default('pending');
            $table->string('drive_file_id')->nullable();
            $table->boolean('encrypted')->default(false);
            // Pemicu: manual (dari panel) | scheduled (scheduler)
            $table->string('trigger', 16)->default('manual');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
    }
};
