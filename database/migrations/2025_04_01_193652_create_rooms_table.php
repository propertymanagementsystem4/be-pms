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
        Schema::create('Room', function (Blueprint $table) {
            $table->uuid('id_room')->primary();
            $table->uuid('type_id');
            $table->string('name');
            $table->string('description');
            $table->string('room_code')->unique();
            $table->timestamps();

            $table->foreign('type_id')->references('id_type')->on('Type')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Room');
    }
};