<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed 2 user admin: super_admin dan ppid_staff.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Super Admin PPID',
            'email' => 'admin@pa-penajam.go.id',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        User::factory()->create([
            'name' => 'Staff PPID',
            'email' => 'staff@pa-penajam.go.id',
            'password' => Hash::make('password'),
            'role' => 'ppid_staff',
        ]);
    }
}
