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
        Schema::create('Revenue', function (Blueprint $table) {
            $table->uuid('id_revenue')->primary();
            $table->uuid('property_id');
            $table->integer('month');
            $table->integer('year');
            $table->double('total_revenue');
            $table->timestamps();

            $table->foreign('property_id')->references('id_property')->on('Property')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Revenue');
    }
};