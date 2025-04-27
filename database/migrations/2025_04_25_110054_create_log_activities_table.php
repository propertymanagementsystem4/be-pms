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
            $table->uuid(('system_logable_id'));
            $table->string('system_logable_type');
            $table->uuid('user_id');
            $table->string('guard_name');
            $table->string('module_name');
            $table->string('action');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->dateTime('date');
            $table->timestamps();
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
