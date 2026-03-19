<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Records a single set of an exercise during a workout.
 *
 * This model tracks the actual effort a user puts in, saving details 
 * like which set number it is, how much weight they lifted, and how 
 * many reps they finished.
 *
 * @property int $id
 * @property int $workout_session_id
 * @property int $exercise_id
 * @property int $set_number
 * @property float|null $weight_used
 * @property int|null $reps_completed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\WorkoutSession $workoutSession
 * @property-read \App\Models\Exercise $exercise
 */
class WorkoutSet extends Model
{
    protected $fillable = [
        'workout_session_id',
        'exercise_id',
        'set_number',
        'weight_used',
        'reps_completed',
    ];

    public function workoutSession(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
