<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'super-admin' => [


            ],
            'company' => [

            ],
            'user' => [

            ],
        ];


        foreach ($permissions as $key => $value) {
            foreach ($value as $permission) {
                Permission::query()->firstOrCreate([
                    'name' => $key . '.' . $permission,
                    'role_name' => $key,
                ]);
            }
        }

    }
}
