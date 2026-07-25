<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super_admin',
            'school_admin',
            'teacher',
            'student',
        ];


        foreach ($roles as $role) {
            Role::create([
                'name' => $role,
            ]);
        }
    }
}