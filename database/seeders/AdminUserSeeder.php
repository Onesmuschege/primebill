<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(['email' => 'admin@primebill.co.ke'], [
            'name'     => 'Super Admin',
            'password' => Hash::make('Admin@123'),
        ]);
        $admin->assignRole('super_admin');

        $staff = User::updateOrCreate(['email' => 'staff@primebill.co.ke'], [
            'name'     => 'Staff User',
            'password' => Hash::make('Staff@123'),
        ]);
        $staff->assignRole('staff');
    }
}