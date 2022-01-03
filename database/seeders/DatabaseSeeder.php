<?php

namespace Database\Seeders;

use App\Models\User;
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
        \App\Models\Company::factory(5)->create();
        $users = [
            ['first_name' => 'admin', 'last_name' => '1', 'email' => 'admin@gmail.com',
             'password' => bcrypt('password'), 'role' => 1, 'company_id' => 1],
            ['first_name' => 'admin', 'last_name' => '2', 'email' => 'admin@g.com',
                'password' => bcrypt('password'), 'role' => 1, 'company_id' => 1],
            ['first_name' => 'admin', 'last_name' => '3', 'email' => 'admin@gm.com',
                'password' => bcrypt('password'), 'role' => 1, 'company_id' => 1]
        ];
        foreach($users as $user){
            User::create($user);
        }
        \App\Models\Gear::factory(50)->create();
    }
}
