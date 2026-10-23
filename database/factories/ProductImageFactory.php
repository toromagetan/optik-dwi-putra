<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'path' => 'products/placeholder-'.fake()->numberBetween(1, 99999).'.jpg',
            'alt_text' => fake()->sentence(3),
            'sort_order' => 0,
        ];
    }
}
