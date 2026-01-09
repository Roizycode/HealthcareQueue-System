<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Main queues table - Core of the queue system
     */
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('priority_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counter_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('called_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Queue number: CON-001, LAB-002, etc.
            $table->string('queue_number', 20);
            $table->integer('queue_position')->default(0);
            
            // Status tracking
            $table->enum('status', [
                'waiting',      // In queue, waiting to be called
                'called',       // Called but not yet at counter
                'serving',      // Being served at counter
                'completed',    // Service completed
                'skipped',      // Skipped/No show
                'cancelled'     // Cancelled by patient/staff
            ])->default('waiting');
            
            // Queue type
            $table->enum('queue_type', ['walk_in', 'virtual', 'appointment'])->default('walk_in');
            
            // Time tracking
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('serving_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('estimated_wait_time')->nullable(); // Minutes
            $table->integer('actual_wait_time')->nullable(); // Minutes
            $table->integer('service_duration')->nullable(); // Minutes
            
            // Additional info
            $table->text('notes')->nullable();
            $table->text('reason_for_visit')->nullable();
            $table->integer('recall_count')->default(0); // How many times called
            $table->boolean('was_escalated')->default(false);
            $table->timestamp('escalated_at')->nullable();
            
            // Notification tracking
            $table->boolean('near_notification_sent')->default(false);
            $table->boolean('called_notification_sent')->default(false);
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['status', 'service_id']);
            $table->index(['queue_number']);
            $table->index(['created_at']);
            $table->index(['patient_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
