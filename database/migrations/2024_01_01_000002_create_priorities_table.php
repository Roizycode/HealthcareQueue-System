<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Priorities table: Emergency, Senior, Regular
     */
    public function up(): void
    {
        Schema::create('priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Emergency, Senior Citizen, PWD, Regular
            $table->string('code', 10)->unique(); // EMG, SNR, PWD, REG
            $table->text('description')->nullable();
            $table->integer('level')->default(0); // Higher = more priority
            $table->string('color', 7)->default('#6B7280'); // Hex color
            $table->string('icon')->nullable(); // FontAwesome icon
            $table->integer('max_wait_time')->default(60); // Max wait before escalation (minutes)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('priorities');
    }
};
