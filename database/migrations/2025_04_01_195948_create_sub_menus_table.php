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
        Schema::create('SubMenu', function (Blueprint $table) {
            $table->uuid('id_sub_menu')->primary();
            $table->uuid('menu_id');
            $table->string('name')->default('/');
            $table->string('path')->default('/');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('menu_id')->references('id_menu')->on('Menu')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('SubMenu');
    }
};