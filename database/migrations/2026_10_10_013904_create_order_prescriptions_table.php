<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('order_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->string('lens_type'); // ex: Single Vision, Progressive
            // Mata Kanan (OD - Oculus Dexter)
            $table->string('right_sph')->nullable();
            $table->string('right_cyl')->nullable();
            $table->string('right_axis')->nullable();
            // Mata Kiri (OS - Oculus Sinister)
            $table->string('left_sph')->nullable();
            $table->string('left_cyl')->nullable();
            $table->string('left_axis')->nullable();
            // Jarak Pupil
            $table->string('pd')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('order_prescriptions'); }
};
