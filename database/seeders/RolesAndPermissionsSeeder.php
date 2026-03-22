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
            // Clients
            'view clients', 'create clients', 'edit clients', 'delete clients',
            'suspend clients', 'activate clients',

            // Plans
            'view plans', 'create plans', 'edit plans', 'delete plans',

            // Invoices
            'view invoices', 'create invoices', 'edit invoices', 'delete invoices',

            // Payments
            'view payments', 'create payments', 'delete payments',

            // Tickets
            'view tickets', 'create tickets', 'edit tickets', 'delete tickets',
            'assign tickets', 'close tickets',

            // Network
            'view routers', 'create routers', 'edit routers', 'delete routers',
            'view radius', 'sync radius',

            // SMS
            'view sms', 'send sms',

            // Reports
            'view reports', 'export reports',

            // Finance
            'view finance', 'create expenditure', 'view commissions',
            'approve commissions',

            // Inventory
            'view inventory', 'create inventory', 'edit inventory',
            'delete inventory',

            // Settings
            'view settings', 'edit settings',

            // Logs
            'view logs',

            // Users
            'view users', 'create users', 'edit users', 'delete users',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Super Admin - has all permissions
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - most permissions
        $admin = Role::create(['name' => 'admin']);
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

        // Staff - limited permissions
        $staff = Role::create(['name' => 'staff']);
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

        // Client - portal only
        $client = Role::create(['name' => 'client']);
        $client->givePermissionTo([
            'view invoices',
            'view payments',
            'view tickets', 'create tickets',
        ]);
    }
}
