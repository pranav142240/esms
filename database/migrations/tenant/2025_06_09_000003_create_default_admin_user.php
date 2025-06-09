<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the default admin user
        $admin = User::create([
            'name' => 'School Admin',
            'email' => 'admin@school.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $adminRole = Role::findByName('admin', 'web');
        $admin->assignRole($adminRole);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete the admin user
        $admin = User::where('email', 'admin@school.com')->first();
        if ($admin) {
            $admin->delete();
        }
    }
};
