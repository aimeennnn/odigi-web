<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:superadmin {--username=SUPERADMIN} {--password=123456}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin user with default password 123456';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->option('username');
        $password = $this->option('password');

        // Check if super admin already exists
        $existingSuperAdmin = User::where('username', $username)->first();
        
        if ($existingSuperAdmin) {
            $this->error("Super Admin with username '{$username}' already exists!");
            return 1;
        }

        try {
            // Create super admin
            $superAdmin = User::create([
                'username' => $username,
                'password' => Hash::make($password),
                'nama' => 'Super Administrator',
                'nik' => '9999999999999999',
                'email' => 'superadmin@system.com',
                'no_hp' => '081234567890',
                'online' => false,
                'status' => 'active',
            ]);

            $this->info("Super Admin created successfully!");
            $this->info("Username: {$username}");
            $this->info("Password: {$password}");
            $this->info("Email: {$superAdmin->email}");
            $this->warn("Default password is 123456 - Please change after first login!");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to create Super Admin: " . $e->getMessage());
            return 1;
        }
    }
}
