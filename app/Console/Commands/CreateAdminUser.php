<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin {username} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $password = $this->argument('password');

        // Check if user already exists
        if (User::where('username', $username)->exists()) {
            $this->error("User with username '{$username}' already exists!");
            return 1;
        }

        // Create admin user
        $user = User::create([
            'username' => $username,
            'password' => Hash::make($password),
            'nama' => 'Administrator',
            'nik' => '1234567890123456',
            'email' => $username . '@admin.com',
            'no_hp' => '081234567890',
            'online' => false,
            'status' => 'active',
        ]);

        $this->info("Admin user '{$username}' created successfully!");
        $this->info("Email: {$user->email}");
        $this->info("Password: {$password}");

        return 0;
    }
}
