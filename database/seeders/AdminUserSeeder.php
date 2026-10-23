<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Akun admin awal untuk pengembangan. Kata sandi WAJIB diganti
     * sebelum produksi (tidak ada kredensial produksi di repository).
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@optikdwiputra.id'],
            [
                'name' => 'Admin Dwi Putra',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
    }
}
