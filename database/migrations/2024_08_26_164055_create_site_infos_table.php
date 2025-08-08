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
        Schema::create('site_infos', function (Blueprint $table) {
            $table->id('site_id');
            $table->string('site_username', 20);
            $table->string('password', 20);
            $table->string('site_password', 20);
            $table->string('site_city', 30);
            $table->string('site_state', 30);
            $table->string('site_country', 20);
            $table->string('site_location', 50);
            $table->string('site_status', 20);
            $table->string('site_type_of_product', 20);
            $table->integer('number_of_floors');
            $table->integer('number_of_zonals');
            $table->integer('number_of_sensors');
            $table->integer('number_of_displays');
            $table->string('email');
            $table->string('report_frequency', 20);
            $table->string('site_logo');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_infos');
    }
};
