<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $this->command->info('✓ Admin user created: admin@test.com / password');

        // Regular user
        $user = User::updateOrCreate(
            ['email' => 'user@test.com'],
            [
                'name' => 'Regular User',
                'email' => 'user@test.com',
                'password' => Hash::make('password'),
                'role' => 'user',
            ]
        );

        $this->command->info('✓ Regular user created: user@test.com / password');
    }
}
