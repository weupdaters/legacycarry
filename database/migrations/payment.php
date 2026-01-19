<?php
// database/migrations/2025_xx_xx_create_payments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_post_id');
            $table->unsignedBigInteger('job_application_id')->nullable();
            $table->unsignedBigInteger('payer_id'); // employer user id
            $table->unsignedBigInteger('payee_id')->nullable(); // worker user id
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('INR');
            $table->string('payment_method')->nullable(); // e.g., UPI, card
            $table->string('payment_reference')->unique(); // external txn id or internal id
            $table->text('notes')->nullable();
            $table->string('status')->default('success'); // success | failed | pending
            $table->timestamps();

            $table->foreign('job_post_id')->references('id')->on('job_posts')->onDelete('cascade');
            $table->foreign('job_application_id')->references('id')->on('job_applications')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
