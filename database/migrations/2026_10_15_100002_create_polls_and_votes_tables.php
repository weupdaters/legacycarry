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
        if (!Schema::hasTable('polls')) {
            Schema::create('polls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('society_id')->constrained('societies')->onDelete('cascade');
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                $table->string('title', 255);
                $table->text('description')->nullable();
                $table->dateTime('start_date');
                $table->dateTime('end_date');
                $table->enum('status', ['draft', 'scheduled', 'active', 'closed'])->default('draft');
                $table->enum('results_visibility', ['always', 'after_vote', 'after_close'])->default('after_vote');
                $table->enum('target_audience', ['all', 'owners', 'tenants'])->default('all');
                $table->timestamps();

                $table->index(['society_id', 'status']);
                $table->index(['start_date', 'end_date']);
            });
        }

        if (!Schema::hasTable('poll_options')) {
            Schema::create('poll_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('poll_id')->constrained('polls')->onDelete('cascade');
                $table->string('option_text', 255);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index('poll_id');
            });
        }

        if (!Schema::hasTable('poll_votes')) {
            Schema::create('poll_votes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('poll_id')->constrained('polls')->onDelete('cascade');
                $table->foreignId('poll_option_id')->constrained('poll_options')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('society_id')->constrained('societies')->onDelete('cascade');
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('voted_at')->useCurrent();
                $table->timestamps();

                // Database-level enforcement of 1 vote per user per poll
                $table->unique(['poll_id', 'user_id'], 'uniq_user_poll_vote');
                $table->index('poll_option_id');
                $table->index('society_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('poll_options');
        Schema::dropIfExists('polls');
    }
};
