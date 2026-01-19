<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScanHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('scan_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('society_id')->nullable();
            $table->unsignedBigInteger('guard_id')->nullable();
            $table->string('token', 100)->index();
            $table->enum('token_type', ['pass', 'appointment', 'vehicle']);
            $table->unsignedBigInteger('subject_id')->nullable()->comment('id of pass/appointment/vehicle');
            $table->enum('action', ['identify', 'approve_entry', 'approve_exit', 'scan_failed'])->default('identify');
            $table->json('payload')->nullable()->comment('snapshot of record at time of action');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('scan_histories');
    }
}
