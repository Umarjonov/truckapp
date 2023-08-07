<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create roles
        $adminRole = Role::create(['name' => 'Admin']);
        $userRole = Role::create(['name' => 'User']);

        // Create permissions
        $manageUsersPermission = Permission::create(['name' => 'Manage Users', 'slug' => 'manage_users']);


        // Attach permissions to roles
        $adminRole->permissions()->attach($manageUsersPermission);
    }
}
