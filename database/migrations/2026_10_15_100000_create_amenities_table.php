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
        if (!Schema::hasTable('amenities')) {
            Schema::create('amenities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('society_id')->constrained('societies')->onDelete('cascade');
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->enum('booking_mode', ['automatic', 'admin_approval'])->default('admin_approval');
                $table->time('opening_time')->default('06:00:00');
                $table->time('closing_time')->default('22:00:00');
                $table->json('available_days')->nullable(); // e.g. ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"]
                $table->unsignedInteger('slot_duration_minutes')->default(60);
                $table->unsignedInteger('max_booking_duration_minutes')->default(120);
                $table->unsignedInteger('advance_booking_days')->default(7);
                $table->decimal('fee', 10, 2)->default(0.00);
                $table->timestamps();

                $table->index(['society_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amenities');
    }
};
