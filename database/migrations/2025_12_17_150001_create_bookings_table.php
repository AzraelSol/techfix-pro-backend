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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('hardware_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('incharge_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('device_brand');
            $table->string('device_model');
            $table->string('serial_number')->nullable();
            $table->text('issue_description');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', [
                'pending',
                'assigned',
                'in_progress',
                'waiting_parts',
                'completed',
                'cancelled',
                'picked_up'
            ])->default('pending');
            $table->text('diagnosis')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('final_cost', 10, 2)->nullable();
            $table->date('estimated_completion_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('incharge_notes')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

