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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->string('withdrawal_id')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('INR');
            
            // Payment method snapshot / reference
            $table->unsignedBigInteger('user_payment_method_id')->nullable();
            $table->string('payout_type', 50)->default('bank'); // upi, bank, wallet
            $table->json('payout_details')->nullable(); // Snapshot of bank/upi info
            
            // Status: pending, processing, completed, failed, rejected, cancelled
            $table->string('status', 30)->default('pending');
            $table->boolean('is_auto_payout')->default(false);
            
            // Cashfree Payout info
            $table->string('cf_transfer_id')->nullable()->index();
            $table->string('cf_reference_id')->nullable(); // UTR / Cashfree internal ref
            $table->json('cf_response')->nullable();
            
            // Admin manual processing / rejection
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
