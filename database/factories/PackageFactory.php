<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'speed_mbps' => fake()->numberBetween(1, 20) * 5,
            'price' => fake()->numberBetween(1, 40) * 25000,
        ];
    }
}
