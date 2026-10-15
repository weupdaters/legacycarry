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
        if (!Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_number', 30)->unique();
                $table->foreignId('society_id')->constrained('societies')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Resident who created ticket
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // Admin/Staff assigned
                $table->string('category', 50); // plumbing, electrical, maintenance, security, noise, cleanliness, parking, other
                $table->string('title', 255);
                $table->text('description');
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
                $table->string('attachment')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();

                // Multi-tenant indexes for society isolation & fast filtering
                $table->index(['society_id', 'status'], 'idx_tickets_society_status');
                $table->index(['user_id', 'status'], 'idx_tickets_user_status');
                $table->index(['society_id', 'category'], 'idx_tickets_society_category');
            });
        }

        if (!Schema::hasTable('ticket_replies')) {
            Schema::create('ticket_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->text('message');
                $table->string('attachment')->nullable();
                $table->boolean('is_internal')->default(false);
                $table->timestamps();

                $table->index('ticket_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_replies');
        Schema::dropIfExists('tickets');
    }
};
