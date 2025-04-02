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
        Schema::create('RoomReservationDetail', function (Blueprint $table) {
            $table->uuid('id_room_reservation_detail')->primary();
            $table->uuid('reservation_id');
            $table->uuid('room_id');
            $table->integer('quantity');
            $table->double('price');
            $table->timestamps();

            $table->foreign('reservation_id')->references('id_reservation')->on('Reservation')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('room_id')->references('id_room')->on('Room')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('RoomReservationDetail');
    }
};