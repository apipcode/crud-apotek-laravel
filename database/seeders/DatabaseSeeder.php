<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@apotek.com'],
            [
                'name' => 'Admin Apotek',
                // Jangan pakai bcrypt di sini karena field password sudah di-cast sebagai 'hashed'
                // di App\Models\User, sehingga akan otomatis di-hash sekali.
                'password' => 'password',
            ]
        );
    }
}
