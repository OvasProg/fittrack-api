<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks when a user actually starts and finishes a workout.
 * This table records the real-time activity of a learner. It differs from
 * a "scheduled" workout because this represents the actual time they
 * spent training in the app.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_sessions', function (Blueprint $table) {
            $table->id();

            // If a user is deleted, we remove their entire history
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // If the original training plan is deleted, we keep the
            // session record so the user doesn't lose their personal
            // progress and history.
            $table->foreignId('training_id')->nullable()->constrained()->nullOnDelete();

            // We automatically set the start time to "now" when the record
            // is created.
            $table->timestamp('started_at')->useCurrent();

            // This stays empty until the user hits the "Finish" button in
            // the app.
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_sessions');
    }
};
