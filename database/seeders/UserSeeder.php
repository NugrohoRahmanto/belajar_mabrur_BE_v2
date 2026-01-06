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
            'email'    => 'admin@belajarmabrur.com',
            'name'     => 'Super Admin',
            'role'     => 'admin',
            'group_id' => 'default',
        ]);

        // Host, untuk room di Android
        User::create([
            'username' => 'host',
            'password' => Hash::make('12345678'),
            'name'     => 'Default Host',
            'role'     => 'host',
            'group_id' => 'default',
        ]);

        // User biasa
        User::create([
            'username' => 'user',
            'password' => Hash::make('12345678'),
            'name'     => 'Regular User',
            'role'     => 'user',
            'group_id' => 'default',
        ]);
    }
}
