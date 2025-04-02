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
        Schema::create('FacilityReservationDetail', function (Blueprint $table) {
            $table->uuid('id_facility_reservation_detail')->primary();
            $table->uuid('reservation_id');
            $table->uuid('facility_id');
            $table->integer('quantity');
            $table->double('price');
            $table->timestamps();

            $table->foreign('reservation_id')->references('id_reservation')->on('Reservation')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('facility_id')->references('id_facility')->on('Facility')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('FacilityReservationDetail');
    }
};