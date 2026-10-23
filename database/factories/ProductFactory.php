<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Produk '.Str::title(fake()->unique()->words(3, true));

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'sku' => 'SKU-'.fake()->unique()->numberBetween(10000, 999999),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(50, 2000) * 1000,
            'stock' => fake()->numberBetween(1, 50),
            'publication_status' => 'published',
            'availability_status' => 'available',
            'is_featured' => false,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['publication_status' => 'draft']);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['publication_status' => 'archived']);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0, 'availability_status' => 'out_of_stock']);
    }

    public function contactForAvailability(): static
    {
        return $this->state(fn () => ['availability_status' => 'contact_for_availability']);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}
