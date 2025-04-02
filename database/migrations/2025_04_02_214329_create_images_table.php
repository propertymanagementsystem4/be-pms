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
        Schema::create('Image', function (Blueprint $table) {
            $table->uuid('id_image')->primary();
            $table->uuid('property_id')->nullable();
            $table->uuid('room_id')->nullable();
            $table->uuid('reservation_id')->nullable();
            $table->string('img_url');
            $table->timestamps();

            $table->foreign('room_id')->references('id_room')->on('Room')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('property_id')->references('id_property')->on('Property')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('reservation_id')->references('id_reservation')->on('Reservation')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Image');
    }
};