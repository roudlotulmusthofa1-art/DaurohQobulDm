<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        User::updateOrCreate(
        ['email' => 'roudlotulmusthofa1@gmail.com'],
        [
            'name' => 'Toyyib Faisol',
            'password' => Hash::make('0toy19101991'),
            'role' => 'admin',
        ],
        );
    }
}
