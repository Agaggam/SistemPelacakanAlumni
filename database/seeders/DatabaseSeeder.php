<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin — akses penuh ke semua fitur
        User::updateOrCreate(
            ['email' => 'admin@alumni.ac.id'],
            [
                'name'     => 'Admin Pelacakan',
                'email'    => 'admin@alumni.ac.id',
                'password' => Hash::make('password'),
                'role'     => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // User biasa — hanya akses Search Mahasiswa
        User::updateOrCreate(
            ['email' => 'user@alumni.ac.id'],
            [
                'name'     => 'Pengguna Umum',
                'email'    => 'user@alumni.ac.id',
                'password' => Hash::make('password'),
                'role'     => 'user',
                'email_verified_at' => now(),
            ]
        );
    }
}
