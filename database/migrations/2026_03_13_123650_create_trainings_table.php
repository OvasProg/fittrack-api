<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the table for workout plans or "trainings."
 *
 * This table stores the templates that instructors create. These are not 
 * the actual workouts users do, but rather the "blueprints" they can 
 * follow or schedule on their calendar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            // This label (like 'Beginner', 'Intermediate', or 'Advanced') 
            // helps users find a plan that matches their current fitness level.
            $table->string('difficulty_level');

            // We store a link to an image so the app can show a 
            // nice preview of the workout to the user.
            $table->string('image_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
