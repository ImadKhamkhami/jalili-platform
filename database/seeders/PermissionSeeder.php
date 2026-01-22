<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // Projects
            'view projects','create projects','edit projects','delete projects',

            // Buildings
            'view buildings','create buildings','edit buildings','delete buildings',

            // Apartments
            'view apartments','create apartments','edit apartments','delete apartments','print apartments',

            // Shops
            'view shops','create shops','edit shops','delete shops','print shops',

            // Lands
            'view lands','create lands','edit lands','delete lands','print lands',

            // Payments
            'view payments','create payments','delete payments','print payments',

            // Users
            'view users','create users','edit users','delete users','assign roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
