<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DashboardPermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::firstOrCreate(
            ['name' => 'view_dashboard', 'guard_name' => 'web'],
            ['name' => 'view_dashboard', 'guard_name' => 'web']
        );
    }
}
