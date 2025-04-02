<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        $adminUser = User::where('email', 'admin@bakehub.com')->first();
        
        if (!$adminUser) {
            // Create new admin user
            User::create([
                'name' => 'Admin',
                'email' => 'admin@bakehub.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'role' => 'admin', // Set role to admin
            ]);
            
            $this->command->info('Admin user created successfully.');
        } else {
            // Update existing admin user's role if needed
            if ($adminUser->role !== 'admin') {
                $adminUser->update(['role' => 'admin']);
                $this->command->info('Admin user role updated to admin.');
            } else {
                $this->command->info('Admin user already exists with correct role.');
            }
        }
    }
}
