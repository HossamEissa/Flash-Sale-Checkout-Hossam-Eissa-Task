<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class  UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'password' => '12345678',
            'profile_type' => 'super-admin',
            'profile_id' => 1,
        ]);
        $superAdmin->assignRole('super-admin');


        $admin = User::factory()
            ->withProfileType(Supplier::class)
            ->create([
                'name' => 'Shrouk',
                'email' => 'shroukybendary@gmail.com',
                'password' => '12345678'
            ]);
        $admin->assignRole('supplier');



    }
}
