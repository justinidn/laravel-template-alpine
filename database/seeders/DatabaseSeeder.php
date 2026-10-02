<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            MasterDepartmentSeeder::class,
            MasterUserSeeder::class,
            MasterMenuSeeder::class,
            PermissionSeeder::class,
            RolesSeeder::class,
            RoleHasPermissionsSeeder::class,
            ModelHasPermissionSeeeder::class,
            ModelHasRoleSeeeder::class,
        ]);
    }
}
