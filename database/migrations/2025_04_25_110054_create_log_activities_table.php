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
        Schema::create('LogActivity', function (Blueprint $table) {
            $table->uuid('id_log_activity')->primary();
            $table->uuid('user_id');
            $table->uuid('property_id')->nullable();
            $table->string('module_name');
            $table->string('action');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->dateTime('date');
            $table->timestamps();

            $table->foreign('user_id')->references('id_user')->on('user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('LogActivity');
    }
};
