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
        Schema::create('sensor_infos', function (Blueprint $table) {
            $table->id('sensor_id');
            $table->integer('zonal_id');
            $table->string('sensor_unique_no', 100);
            $table->string('sensor_name', 50);
            $table->integer('sensor_range');
            $table->string('color_occupied', 20);
            $table->string('color_available', 20);
            $table->string('role', 10);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_infos');
    }
};
