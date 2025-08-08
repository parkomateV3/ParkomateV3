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
        Schema::create('eecs_sensor_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('site_id');
            $table->integer('device_id');
            $table->integer('sensor_number');
            $table->integer('detection_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eecs_sensor_infos');
    }
};
