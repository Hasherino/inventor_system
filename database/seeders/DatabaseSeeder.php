<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//        \App\Models\Company::factory(5)->create();
        \App\Models\User::factory(1)->create();
        \App\Models\Gear::factory(20)->create();
    }
}
