<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::create([
            'username' => 'admin',
            'password' => Hash::make('12345678'),
            'email'    => 'admin@example.com',
            'name'     => 'Super Admin',
            'role'     => 'admin',
        ]);

        // Host, untuk room di Android
        User::create([
            'username' => 'host',
            'password' => Hash::make('12345678'),
            'name'     => 'Default Host',
            'role'     => 'host',
        ]);

        // User biasa
        User::create([
            'username' => 'user',
            'password' => Hash::make('12345678'),
            'name'     => 'Regular User',
            'role'     => 'user',
        ]);
    }
}
