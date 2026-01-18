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
        // Create default admin user if it doesn't exist
        $adminEmail = 'admin@meetui.com';
        
        $admin = User::where('email', $adminEmail)->first();
        
        if (!$admin) {
            User::create([
                'name' => 'Platform Admin',
                'email' => $adminEmail,
                'password' => Hash::make('admin123'), // Change this password after first login!
                'is_platform_admin' => true,
                'email_verified_at' => now(),
            ]);
            
            $this->command->info('Default admin user created successfully!');
            $this->command->info('Email: ' . $adminEmail);
            $this->command->warn('Password: admin123 (Please change this after first login!)');
        } else {
            // Update existing user to be admin if not already
            if (!$admin->is_platform_admin) {
                $admin->update(['is_platform_admin' => true]);
                $this->command->info('Existing user has been promoted to platform admin.');
            } else {
                $this->command->info('Admin user already exists.');
            }
        }
    }
}
