<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@mooc.local',
            'phone' => '082111111111',
            'role' => 'admin',
            'password' => Hash::make('password'),
            'xp' => 10000,
            'level' => 50,
            'next_level_xp' => 318242,
        ]);

        // Create instructors
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@mooc.local',
            'phone' => '082112112112',
            'role' => 'instructor',
            'password' => Hash::make('password'),
            'xp' => 5000,
            'level' => 25,
            'next_level_xp' => 13955,
        ]);

        User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@mooc.local',
            'phone' => '082113113113',
            'role' => 'instructor',
            'password' => Hash::make('password'),
            'xp' => 4500,
            'level' => 22,
            'next_level_xp' => 7570,
        ]);

        // Create regular users
        $userData = [
            ['name' => 'Raka Wijaya', 'email' => 'raka@mooc.local', 'xp' => 2500, 'level' => 12],
            ['name' => 'Dina Kusuma', 'email' => 'dina@mooc.local', 'xp' => 1800, 'level' => 9],
            ['name' => 'Ahmad Hendra', 'email' => 'ahmad@mooc.local', 'xp' => 3200, 'level' => 15],
            ['name' => 'Lina Permata', 'email' => 'lina@mooc.local', 'xp' => 850, 'level' => 5],
            ['name' => 'Eko Prasetyo', 'email' => 'eko@mooc.local', 'xp' => 1200, 'level' => 7],
            ['name' => 'Maya Cahyani', 'email' => 'maya@mooc.local', 'xp' => 2100, 'level' => 10],
        ];

        foreach ($userData as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => '0821' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'role' => 'user',
                'password' => Hash::make('password'),
                'xp' => $user['xp'],
                'level' => $user['level'],
                'next_level_xp' => rand(300, 3000),
            ]);
        }

        echo "\n✅ UserSeeder: 10 users created\n";
    }
}
