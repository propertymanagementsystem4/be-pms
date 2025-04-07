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
        Schema::create('Type', function (Blueprint $table) {
            $table->uuid('id_type')->primary();
            $table->uuid('property_id');
            $table->string('name');
            $table->float('price_per_night');
            $table->timestamps();

            $table->foreign('property_id')->references('id_property')->on('Property')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Type');
    }
};
