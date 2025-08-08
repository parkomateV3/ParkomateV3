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
        Schema::create('table_entries', function (Blueprint $table) {
            $table->id('entry_id');
            $table->integer('table_id');
            $table->integer('site_id');
            $table->string('entry_name');
            $table->string('floor_zonal_sensor_ids');
            $table->string('logic_to_calculate_numbers');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_entries');
    }
};
