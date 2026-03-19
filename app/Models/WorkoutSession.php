<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tracks a single instance of a user performing a workout.
 *
 * This model connects a learner to a specific training plan and records 
 * exactly when they started and finished it, along with the actual sets they did.
 *
 * @property int $id
 * @property int $user_id
 * @property int $training_id
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * * @property-read \App\Models\User $user
 * @property-read \App\Models\Training $training
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WorkoutSet[] $workoutSets
 */
class WorkoutSession extends Model
{
    protected $fillable = [
        'user_id',
        'training_id',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function workoutSets(): HasMany
    {
        return $this->hasMany(WorkoutSet::class);
    }
}
