<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Hamlet;
use App\Models\Package;
use App\Models\System;
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
        $hamlet = Hamlet::query()->inRandomOrder()->firstOrFail();

        return [
            'name' => fake()->name(),
            'hamlet_id' => $hamlet->id,
            'package_id' => Package::query()->inRandomOrder()->firstOrFail()->id,
            'system_id' => System::query()->inRandomOrder()->firstOrFail()->id,
        ];
    }
}
