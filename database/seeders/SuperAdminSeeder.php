<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if super admin already exists
        $superAdmin = User::where('username', 'SUPERADMIN')->first();
        
        if (!$superAdmin) {
            // Create super admin user
            $superAdmin = User::create([
                'username' => 'SUPERADMIN',
                'password' => Hash::make('123456'),
                'nama' => 'Super Administrator',
                'nik' => '9999999999999999',
                'email' => 'superadmin@system.com',
                'no_hp' => '6281234567890',
                'online' => false,
                'status' => 'active',
            ]);

            // Add a special flag to identify super admin (optional)
            // You can add a column 'is_super_admin' to your user table if needed
            // For now, we'll use the username as identifier
            
            $this->command->info('Super Admin created successfully!');
            $this->command->info('Username: SUPERADMIN');
            $this->command->info('Password: 123456 (Default)');
        } else {
            // Update existing super admin with proper jabatan and level
            $superAdmin->update([
                'jabatan' => 'Super Admin',
                'level' => 'Full Access',
            ]);
            $this->command->info('Super Admin already exists! Updated jabatan and level.');
        }
    }
}
