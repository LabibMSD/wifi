<?php

namespace Database\Factories;

use App\Models\District;
use App\Models\PrefixArea;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Village>
 */
class VillageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'district_id' => District::query()->inRandomOrder()->first()->id,
            'prefix_area_id' => PrefixArea::query()->inRandomOrder()->first()->id,
            'name' => fake()->name(),
        ];
    }
}
