<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Represents a single physical exercise, like a Pushup or Barbell Squat.
 *
 * These are the basic building blocks of the app. Instructors combine these
 * to make full training plans. The 'base_multiplier' field is used by our
 * adaptive system to calculate recommended weights.
 *
 * @property int $id
 * @property string $name
 * @property string|null $target_muscle
 * @property float $base_multiplier
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TrainingExercise|null $pivot
 * @property-read Collection|Training[] $trainings
 * @property-read Collection|WorkoutSet[] $workoutSets
 */
class Exercise extends Model
{
    protected $fillable = [
        'name',
        'target_muscle',
        'base_multiplier',
    ];

    public function trainings(): BelongsToMany
    {
        // We grab the pivot data (sets and reps) because an exercise
        // might be done differently depending on which training plan it is
        // part of.
        return $this->belongsToMany(Training::class, 'training_exercises')
            ->withPivot('default_sets', 'default_reps')
            ->withTimestamps();
    }

    public function workoutSets(): HasMany
    {
        return $this->hasMany(WorkoutSet::class);
    }
}
