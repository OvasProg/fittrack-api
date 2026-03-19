<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Connects exercises to specific training plans.
 * This table is where we define which exercises belong in a plan. It also 
 * stores the "starting point" for sets and reps, which the adaptive 
 * system can then adjust based on how the user performs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_exercises', function (Blueprint $table) {
            $table->id();

            // If an exercise or a training plan is removed, we also remove 
            // this connection to prevent "ghost" exercises in a plan.
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();

            // We set these defaults so that every exercise in a plan has a 
            // clear starting goal. An instructor can change these when 
            // they create the training program.
            $table->integer('default_sets')->default(3);
            $table->integer('default_reps')->default(10);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_exercises');
    }
};
