<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;


class AdminUserSeeder extends Seeder
{

    public function run(): void
    {


        // Create admin role if it does not exist
        $adminRole = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);




        // Create admin user

        $admin = User::updateOrCreate(

            [
                'email' => 'admin@example.com'
            ],

            [

                'name' => 'System Administrator',

                'password' => Hash::make(
                    'password123'
                ),

                'must_change_password' => false,

            ]

        );




        // Assign role

        $admin->assignRole($adminRole);



    }

}