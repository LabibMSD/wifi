<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Package;
use App\Models\System;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $village = Village::query()
            ->whereHas('hamlets')
            ->with('hamlets')
            ->inRandomOrder()
            ->firstOrFail();

        return [
            'name' => fake()->name(),
            'district_id' => $village->district_id,
            'village_id' => $village->id,
            'hamlet_id' => $village->hamlets->random()->id,
            'package_id' => Package::query()->inRandomOrder()->first()->id,
            'system_id' => System::query()->inRandomOrder()->first()->id,
        ];
    }
}
