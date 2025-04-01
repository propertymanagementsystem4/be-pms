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
        Schema::create('Menu', function (Blueprint $table) {
            $table->uuid('id_menu')->primary();
            $table->string('role_id')->default('all');
            $table->string('name');
            $table->string('prefix')->default('/');
            $table->string('path')->default('/');
            $table->string('icon')->default('md-');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('role_id')->references('id_role')->on('Role')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Menu');
    }
};