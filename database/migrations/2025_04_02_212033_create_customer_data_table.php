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
        Schema::create('CustomerData', function (Blueprint $table) {
            $table->uuid('id_customer_data')->primary();
            $table->uuid('reservation_id');
            $table->string('fullname');
            $table->string('nik');
            $table->dateTime('birth_date');
            $table->timestamps();

            $table->foreign('reservation_id')->references('id_reservation')->on('Reservation')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CustomerData');
    }
};