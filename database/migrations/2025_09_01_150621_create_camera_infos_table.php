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
        Schema::create('camera_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('site_id');
            $table->integer('processor_id');
            $table->string('local_ip_address');
            $table->string('camera_access_link');
            $table->string('camera_identifier');
            $table->text('parking_slot_details');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camera_infos');
    }
};
