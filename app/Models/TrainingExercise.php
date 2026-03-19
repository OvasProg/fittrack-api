<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Connects a training plan to a specific exercise.
 *
 * This pivot model does more than just link two tables together. It holds 
 * the actual rules (sets and reps) for how an exercise should be performed 
 * inside a specific training program.
 *
 * @property int $training_id
 * @property int $exercise_id
 * @property int $default_sets
 * @property int $default_reps
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class TrainingExercise extends Pivot
{
    protected $table = 'training_exercises';

    protected function casts(): array
    {
        return [
            'default_sets' => 'integer',
            'default_reps' => 'integer',
        ];
    }
}
