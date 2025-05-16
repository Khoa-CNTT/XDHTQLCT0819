<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'hale.02031982@gmail.com'],
            [
                'username' => 'admin',
                'password' => Hash::make('admin123456'),
                'phone' => '0983057130',
                'fullName' => 'Admin',
                'role' => 'admin',
                'isActived' => true,
                'status' => false,
            ]
        );
    }
}
