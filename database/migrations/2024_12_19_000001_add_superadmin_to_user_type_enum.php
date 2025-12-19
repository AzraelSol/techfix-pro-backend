<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to modify the enum
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('user', 'incharge', 'admin', 'superadmin') DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First update any superadmin users to admin
        DB::table('users')->where('user_type', 'superadmin')->update(['user_type' => 'admin']);
        
        // Then revert the enum
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('user', 'incharge', 'admin') DEFAULT 'user'");
    }
};

