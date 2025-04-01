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
        Schema::create('User', function (Blueprint $table) {
            $table->uuid('id_user')->primary();
            $table->uuid('role_id');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('fullname');
            $table->string('phone_number');
            $table->boolean('is_verified')->default(false);
            $table->string('image_url')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id_role')->on('Role')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('User');
    }
};
