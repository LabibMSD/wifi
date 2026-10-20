<?php

namespace Database\Seeders;

use App\Models\Customer;
use DB;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            Customer::factory(5)->create();
        });
    }
}
