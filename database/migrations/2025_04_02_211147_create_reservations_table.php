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
        Schema::create('Reservation', function (Blueprint $table) {
            $table->uuid('id_reservation')->primary();
            $table->uuid('customer_id');
            $table->uuid('property_id');
            $table->dateTime('check_in_date');
            $table->dateTime('check_out_date');
            $table->string('invoice_number')->unique();
            $table->integer('total_guest');
            $table->double('total_price');
            $table->string('payment_status');
            $table->string('reservation_status');
            $table->timestamps();

            $table->foreign('property_id')->references('id_property')->on('Property')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('customer_id')->references('id_user')->on('User')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Reservation');
    }
};