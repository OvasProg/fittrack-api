<?php

namespace App\Models;

use App\Enums\ExperienceLevel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Represents a workout program or template (like "Full Body for Beginners").
 *
 * Instructors create these training plans, and learners can either schedule
 * them for the future or start them right away.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $difficulty_level
 * @property string|null $image_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Exercise[] $exercises
 * @property-read Collection|ScheduledWorkout[] $scheduledWorkouts
 * @property-read Collection|WorkoutSession[] $workoutSessions
 */
class Training extends Model
{
    protected $fillable = [
        'name',
        'description',
        'difficulty_level',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'difficulty_level' => ExperienceLevel::class,
        ];
    }

    public function exercises(): BelongsToMany
    {
        // We load default sets and reps from the pivot table because
        // the same exercise can have different targets.
        // For example, "Pushups" might be 3 sets of 10 in a beginner
        // training, but 5 sets of 20 in an advanced one.
        return $this->belongsToMany(Exercise::class, 'training_exercises')
            ->withPivot('default_sets', 'default_reps')
            ->withTimestamps();
    }

    public function scheduledWorkouts(): HasMany
    {
        return $this->hasMany(ScheduledWorkout::class);
    }

    public function workoutSessions(): HasMany
    {
        return $this->hasMany(WorkoutSession::class);
    }
}
