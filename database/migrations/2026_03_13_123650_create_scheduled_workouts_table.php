<?php

use App\Enums\WorkoutStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the table for the user's workout calendar.
 *
 * This table links a user to a training plan for a specific date. It acts
 * as a plan that they intend to follow, which we then track to see if
 * they actually finished it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_workouts', function (Blueprint $table) {
            $table->id();

            // If a user or a training plan is deleted from the app,
            // we also remove these scheduled entries to keep the database
            // clean.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();

            $table->date('date');

            // We start every workout as 'pending'. It changes to
            // 'completed' when user finishes the session, or 'missed' if
            // the date passes and user didn't start it.
            $table->string('status')->default(WorkoutStatus::PENDING->value);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_workouts');
    }
};
