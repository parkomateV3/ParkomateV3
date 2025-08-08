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
        Schema::create('site_data_by_hours', function (Blueprint $table) {
            $table->id();
            $table->integer('site_id'); // Foreign key reference
            $table->string('date_time_slot'); // Slot description or range
            $table->integer('check_in_count')->default(0); // Count of check-ins
            $table->integer('check_out_count')->default(0); // Count of check-outs
            $table->integer('max_count')->nullable(); // Maximum count
            $table->integer('min_count')->nullable(); // Minimum count
            $table->time('min_time')->nullable(); // Minimum time
            $table->time('max_time')->nullable(); // Maximum time
            $table->float('avg_time', 8, 2)->nullable(); // Average time with precision
            $table->timestamps(); // Created and updated timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_data_by_hours');
    }
};
