<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->longText('description')->nullable();
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->string('publication_status')->default('draft');
            $table->string('availability_status')->default('available');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index(['category_id', 'publication_status']);
            $table->index('publication_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
