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
        // Create superadmin user (email verified)
        User::create([
            'first_name' => 'Super',
            'middle_name' => null,
            'last_name' => 'Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@techfixpro.com',
            'email_verified_at' => now(),
            'phone' => '09123456780',
            'user_type' => 'superadmin',
            'password' => Hash::make('password'),
        ]);

        // Create admin user (email verified)
        User::create([
            'first_name' => 'Admin',
            'middle_name' => null,
            'last_name' => 'User',
            'username' => 'admin',
            'email' => 'admin@techfixpro.com',
            'email_verified_at' => now(),
            'phone' => '09123456789',
            'user_type' => 'admin',
            'password' => Hash::make('password'),
        ]);

        // Create incharge/technician users (email verified)
        User::create([
            'first_name' => 'John',
            'middle_name' => 'Michael',
            'last_name' => 'Technician',
            'username' => 'john_tech',
            'email' => 'john@techfixpro.com',
            'email_verified_at' => now(),
            'phone' => '09123456788',
            'user_type' => 'incharge',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'first_name' => 'Jane',
            'middle_name' => null,
            'last_name' => 'Expert',
            'username' => 'jane_tech',
            'email' => 'jane@techfixpro.com',
            'email_verified_at' => now(),
            'phone' => '09123456787',
            'user_type' => 'incharge',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'first_name' => 'Mike',
            'middle_name' => 'James',
            'last_name' => 'Specialist',
            'username' => 'mike_spec',
            'email' => 'mike@techfixpro.com',
            'email_verified_at' => now(),
            'phone' => '09123456785',
            'user_type' => 'incharge',
            'password' => Hash::make('password'),
        ]);

        // Create regular users (email verified)
        User::create([
            'first_name' => 'Test',
            'middle_name' => null,
            'last_name' => 'User',
            'username' => 'testuser',
            'email' => 'user@techfixpro.com',
            'email_verified_at' => now(),
            'phone' => '09123456786',
            'user_type' => 'user',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'first_name' => 'Maria',
            'middle_name' => 'Clara',
            'last_name' => 'Santos',
            'username' => 'maria_santos',
            'email' => 'maria@example.com',
            'email_verified_at' => now(),
            'phone' => '09198765432',
            'user_type' => 'user',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'first_name' => 'Carlos',
            'middle_name' => null,
            'last_name' => 'Reyes',
            'username' => 'carlos_r',
            'email' => 'carlos@example.com',
            'email_verified_at' => now(),
            'phone' => '09187654321',
            'user_type' => 'user',
            'password' => Hash::make('password'),
        ]);
    }
}

