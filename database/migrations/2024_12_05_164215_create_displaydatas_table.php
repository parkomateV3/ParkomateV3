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
        Schema::create('displaydatas', function (Blueprint $table) {
            $table->id('data_id');
            $table->integer('display_id');
            $table->string('coordinates');
            $table->text('floor_zonal_sensor_ids');
            $table->string('logic_calculate_number');
            $table->string('display_format');
            $table->string('font_size');
            $table->string('color');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('displaydatas');
    }
};
