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
        Schema::create('sensor_reservations', function (Blueprint $table) {
            $table->id();
            $table->integer('site_id');
            $table->integer('zonal_id');
            $table->integer('barrier_id');
            $table->integer('is_blocked');
            $table->datetime('from_date_time');
            $table->datetime('to_date_time');
            $table->integer('otp');
            $table->datetime('unblocked_on');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_reservations');
    }
};
