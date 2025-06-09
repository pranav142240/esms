<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('superadmins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        // Insert default superadmin
        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'superadmin@esms.com',
            'password' => bcrypt('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::getPdo()->lastInsertId();

        DB::table('superadmins')->insert([
            'user_id' => $userId,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('superadmins');
    }
};
