<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Notification logs for SMS/Email tracking
     */
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->cascadeOnDelete();
            
            $table->enum('type', ['sms', 'email', 'push'])->default('sms');
            $table->enum('notification_type', [
                'queue_joined',
                'queue_near',      // 3 positions away
                'queue_called',
                'queue_completed',
                'queue_cancelled',
                'reminder'
            ]);
            
            $table->string('recipient'); // Phone or email
            $table->text('message');
            $table->string('subject')->nullable(); // For email
            
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed'])->default('pending');
            $table->string('provider')->nullable(); // Twilio, etc.
            $table->string('provider_message_id')->nullable();
            $table->text('error_message')->nullable();
            
            $table->decimal('cost', 8, 4)->nullable(); // SMS cost tracking
            $table->integer('retry_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            $table->timestamps();

            $table->index(['queue_id', 'notification_type']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
