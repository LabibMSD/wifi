<?php

namespace Database\Factories;

use App\Models\PrefixArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrefixArea>
 */
class PrefixAreaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'prefix' => strtoupper(fake()->unique()->lexify('??')),
            'descriptions' => fake()->sentence(),
        ];
    }
}
