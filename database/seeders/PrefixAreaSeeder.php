<?php

namespace Database\Seeders;

use App\Models\PrefixArea;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrefixAreaSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['AA', 'BB', 'CC', 'DD', 'EE'] as $prefix) {
            PrefixArea::factory()->create([
                'prefix' => $prefix,
            ]);
        }
    }
}
