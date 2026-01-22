<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /* ================= Permissions ================= */

        $permissions = [

            // Users
            'view users',
            'create users',
            'edit users',
            'delete users',
            'assign roles',

            // Payments
            'view payments',
            'create payments',
            'edit payments',
            'delete payments',
            'print payments',

            // Lands
            'view lands',
            'create lands',
            'edit lands',
            'delete lands',
            'print lands',

            // Apartments
            'view apartments',
            'create apartments',
            'edit apartments',
            'delete apartments',
            'print apartments',

            // Shops
            'view shops',
            'create shops',
            'edit shops',
            'delete shops',
            'print shops',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /* ================= Roles ================= */

        $admin      = Role::firstOrCreate(['name' => 'admin']);
        $manager    = Role::firstOrCreate(['name' => 'manager']);
        $accountant = Role::firstOrCreate(['name' => 'accountant']);
        $viewer     = Role::firstOrCreate(['name' => 'viewer']);

        /* ================= Assign Permissions ================= */

        // Admin → كل شيء
        $admin->givePermissionTo(Permission::all());

        // Manager → إدارة بدون حذف
        $manager->givePermissionTo([
            'view users',
            'view payments','create payments','edit payments','print payments',
            'view lands','create lands','edit lands','print lands',
            'view apartments','create apartments','edit apartments','print apartments',
            'view shops','create shops','edit shops','print shops',
        ]);

        // Accountant → الدفوعات فقط
        $accountant->givePermissionTo([
            'view payments','create payments','print payments',
        ]);

        // Viewer → مشاهدة فقط
        $viewer->givePermissionTo([
            'view users',
            'view payments',
            'view lands',
            'view apartments',
            'view shops',
        ]);
    }
}
