<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view clients', 'create clients', 'edit clients', 'delete clients',
            'suspend clients', 'activate clients',
            'view plans', 'create plans', 'edit plans', 'delete plans',
            'view invoices', 'create invoices', 'edit invoices', 'delete invoices',
            'view payments', 'create payments', 'delete payments',
            'view tickets', 'create tickets', 'edit tickets', 'delete tickets',
            'assign tickets', 'close tickets',
            'view routers', 'create routers', 'edit routers', 'delete routers',
            'view radius', 'sync radius',
            'view sms', 'send sms',
            'view reports', 'export reports',
            'view finance', 'create expenditure', 'view commissions',
            'approve commissions',
            'view inventory', 'create inventory', 'edit inventory',
            'delete inventory',
            'view settings', 'edit settings',
            'view logs',
            'view users', 'create users', 'edit users', 'delete users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo([
            'view clients', 'create clients', 'edit clients',
            'suspend clients', 'activate clients',
            'view plans', 'create plans', 'edit plans',
            'view invoices', 'create invoices', 'edit invoices',
            'view payments', 'create payments',
            'view tickets', 'create tickets', 'edit tickets',
            'assign tickets', 'close tickets',
            'view routers', 'view radius', 'sync radius',
            'view sms', 'send sms',
            'view reports', 'export reports',
            'view finance', 'create expenditure',
            'view commissions', 'approve commissions',
            'view inventory', 'create inventory', 'edit inventory',
            'view settings', 'view logs',
        ]);

        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->givePermissionTo([
            'view clients', 'create clients', 'edit clients',
            'suspend clients', 'activate clients',
            'view plans',
            'view invoices', 'create invoices',
            'view payments', 'create payments',
            'view tickets', 'create tickets', 'edit tickets',
            'close tickets',
            'view sms', 'send sms',
            'view reports',
            'view inventory',
        ]);

        $client = Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
        $client->givePermissionTo([
            'view invoices',
            'view payments',
            'view tickets', 'create tickets',
        ]);
    }
}