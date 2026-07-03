<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@ciogolf.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'user_type' => 'admin',
                'phone' => '+2348000000001',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@ciogolf.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'user_type' => 'admin',
                'phone' => '+2348000000002',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'committee@ciogolf.com'],
            [
                'name' => 'Committee Member',
                'password' => Hash::make('password'),
                'role' => 'committee',
                'user_type' => 'committee',
                'phone' => '+2348000000003',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin users seeded: superadmin@ciogolf.com, admin@ciogolf.com, committee@ciogolf.com (password: password)');
    }
}
