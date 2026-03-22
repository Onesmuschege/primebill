<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@primebill.co.ke',
            'password' => Hash::make('Admin@1234'),
        ]);

        $admin->assignRole('super_admin');

        $staff = User::create([
            'name'     => 'Staff User',
            'email'    => 'staff@primebill.co.ke',
            'password' => Hash::make('Staff@1234'),
        ]);

        $staff->assignRole('staff');
    }
}
