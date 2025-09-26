<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => bcrypt('password'), 'role' => 'admin']
        );
        User::updateOrCreate(
            ['email' => 'guest@example.com'],
            ['name' => 'Guest', 'password' => bcrypt('password'), 'role' => 'guest']
        );
    }
}
