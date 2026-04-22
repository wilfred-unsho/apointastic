<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'provider.view',
            'provider.approve',
            'provider.reject',
            'appointment.view',
            'appointment.approve',
            'appointment.reject',
            'admin.dashboard.access',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $admin = Role::findOrCreate('admin');
        $provider = Role::findOrCreate('provider');
        $customer = Role::findOrCreate('customer');

        $admin->syncPermissions($permissions);
        $provider->syncPermissions([
            'provider.view',
            'appointment.view',
        ]);
        $customer->syncPermissions([]);
    }
}
