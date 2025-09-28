<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'super-admin',
            'company',
            'member'
        ];

        foreach ($roles as $role) {
            $role = Role::query()->firstOrCreate([
                'name' => $role,
            ]);

            $permissions = Permission::query()
                ->where('role_name', $role->name)
                ->get()
                ->pluck('name')
                ->toArray();


            $role->syncPermissions($permissions);
        }
    }
}
