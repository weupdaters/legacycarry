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
        // 1. Add tracking columns to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('users', 'last_active_endpoint')) {
                $table->string('last_active_endpoint', 255)->nullable()->after('last_seen_at');
            }
        });

        // 2. Create api_activity_logs table for real-time tracking
        if (!Schema::hasTable('api_activity_logs')) {
            Schema::create('api_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('user_name', 150)->nullable();
                $table->string('endpoint', 255);
                $table->string('method', 10)->default('GET');
                $table->string('ip_address', 45)->nullable();
                $table->string('device_type', 50)->nullable();
                $table->integer('status_code')->nullable();
                $table->integer('response_time_ms')->nullable();
                $table->timestamp('created_at')->nullable()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_activity_logs');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_seen_at')) {
                $table->dropColumn('last_seen_at');
            }
            if (Schema::hasColumn('users', 'last_active_endpoint')) {
                $table->dropColumn('last_active_endpoint');
            }
        });
    }
};
