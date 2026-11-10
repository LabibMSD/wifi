<?php

namespace Database\Seeders;

use App\Models\Hamlet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HamletSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Hamlet::factory(5)->create();
    }
}
