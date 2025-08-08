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
        Schema::create('display_infos', function (Blueprint $table) {
            $table->id('display_id');
            $table->integer('site_id');
            $table->string('display_unique_no', 100);
            $table->text('floor_zonal_sensor_ids');
            $table->string('logic_to_calculate_no', 100);
            $table->string('display_format', 20);
            $table->string('location_of_the_display_on_site', 100);
            $table->integer('intensity');
            $table->string('font_size', 20);
            $table->string('color', 20);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('display_infos');
    }
};
