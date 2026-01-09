<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add role and additional fields to users table
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'staff', 'patient'])->default('patient')->after('email');
            $table->string('phone')->nullable()->after('role');
            $table->string('employee_id')->nullable()->after('phone');
            $table->foreignId('assigned_service_id')->nullable()->after('employee_id');
            $table->boolean('is_active')->default(true)->after('assigned_service_id');
            $table->string('avatar')->nullable()->after('is_active');
            $table->timestamp('last_login_at')->nullable();
            
            $table->index(['role', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'is_active']);
            $table->dropColumn([
                'role',
                'phone',
                'employee_id',
                'assigned_service_id',
                'is_active',
                'avatar',
                'last_login_at'
            ]);
        });
    }
};
