<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the library of physical exercises.
 * This table stores every individual movement available in the app. 
 * These exercises are then pulled into different training plans.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('target_muscle');

            // This multiplier helps our adaptive system suggest the right 
            // weight. For example, if it is set to 0.5, the app might 
            // suggest the user starts by lifting 50% of their own body 
            // weight for this exercise.
            $table->float('base_multiplier');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
