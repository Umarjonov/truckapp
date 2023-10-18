<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'SupperAdmin']);
        Role::create(['name' => 'SupperManager']);
        Role::create(['name' => 'CompanyAdmin']);
        Role::create(['name' => 'Manager']);
        Role::create(['name' => 'Hr']);
        Role::create(['name' => 'User']);
    }

}
