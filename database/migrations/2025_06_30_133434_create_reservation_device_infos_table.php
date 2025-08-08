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
        Schema::create('reservation_device_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('site_id');
            $table->integer('floor_id');
            $table->integer('zonal_id');
            $table->string('reservation_number');
            $table->string('reservation_name');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_device_infos');
    }
};
