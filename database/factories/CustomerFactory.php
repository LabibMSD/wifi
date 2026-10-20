<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\District;
use App\Models\Hamlet;
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
        return [
            'name' => fake()->name(),
            'district_id' => District::query()->inRandomOrder()->first()->id,
            'village_id' => Village::query()->inRandomOrder()->first()->id,
            'hamlet_id' => Hamlet::query()->inRandomOrder()->first()->id,
            'package_id' => Package::query()->inRandomOrder()->first()->id,
            'system_id' => System::query()->inRandomOrder()->first()->id,
        ];
    }
}
