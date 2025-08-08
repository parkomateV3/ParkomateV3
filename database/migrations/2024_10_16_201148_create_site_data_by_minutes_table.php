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
        Schema::create('site_data_by_minutes', function (Blueprint $table) {
            $table->id();
            $table->integer('site_id');
            $table->mediumText('data');
            $table->timestamp('date_time');
            $table->integer('total_occupied');
            $table->integer('total_available');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_data_by_minutes');
    }
};
