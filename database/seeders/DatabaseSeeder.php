<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call seeders
        $this->call([
            SuperAdminSeeder::class,
            SlikStatusSeeder::class,
        ]);

        // Create some test users (optional)
        // User::factory(5)->create();
    }
}
