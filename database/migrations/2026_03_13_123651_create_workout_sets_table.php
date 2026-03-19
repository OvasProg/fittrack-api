<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the table for recording the actual performance of a workout.
 * This table stores the granular data for every single set a user 
 * performs. While other tables store "plans," this table stores 
 * the "truth" of what happened during the training session.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_sets', function (Blueprint $table) {
            $table->id();

            // If the parent session or the exercise itself is deleted, 
            // we remove these records to keep our history accurate.
            $table->foreignId('workout_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();

            // We track the order (set 1, set 2, etc.) so we can show 
            // the user their progress in the correct sequence later.
            $table->integer('set_number');

            $table->float('weight_used');

            $table->integer('reps_completed');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_sets');
    }
};
