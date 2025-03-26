<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un utilisateur appelé Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => 'password123'
        ]);
    }
}
