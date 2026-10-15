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
        if (!Schema::hasTable('amenity_bookings')) {
            Schema::create('amenity_bookings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('society_id')->constrained('societies')->onDelete('cascade');
                $table->foreignId('amenity_id')->constrained('amenities')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->date('booking_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->enum('status', ['pending', 'confirmed', 'rejected', 'cancelled'])->default('pending');
                $table->text('rejection_reason')->nullable();
                $table->text('admin_notes')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                // Composite index for slot availability query and transaction-level conflict checks
                $table->index(['amenity_id', 'booking_date', 'status'], 'idx_amenity_date_status');
                $table->index(['user_id', 'status'], 'idx_user_booking_status');
                $table->index(['society_id', 'booking_date'], 'idx_society_booking_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amenity_bookings');
    }
};
