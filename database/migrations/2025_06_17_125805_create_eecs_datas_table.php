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
        Schema::create('eecs_datas', function (Blueprint $table) {
            $table->id();
            $table->integer('site_id');
            $table->integer('floor_id');
            $table->integer('zonal_id');
            $table->integer('sensor_id');
            $table->integer('type_of_vehicle');
            $table->integer('from');
            $table->integer('to');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eecs_datas');
    }
};
