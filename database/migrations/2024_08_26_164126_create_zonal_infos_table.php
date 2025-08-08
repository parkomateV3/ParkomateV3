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
        Schema::create('zonal_infos', function (Blueprint $table) {
            $table->id('zonal_id');
            $table->integer('floor_id');
            $table->string('zonal_unique_no', 100);
            $table->string('zonal_name', 20);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zonal_infos');
    }
};
