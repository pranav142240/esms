<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant database.
     */
    public function run(): void
    {
        $this->call([
            TenantRolesAndPermissionsSeeder::class,
            TenantUsersSeeder::class,
            TenantClassesSeeder::class,
            TenantSubjectsSeeder::class,
            TenantStudentsSeeder::class,
            TenantTeachersSeeder::class,
            TenantBooksSeeder::class,
            TenantStudentFeesSeeder::class,
            TenantExpensesSeeder::class,
            TenantExamsSeeder::class,
            TenantNoticesSeeder::class,
        ]);
    }
}
