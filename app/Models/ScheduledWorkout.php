<?php

namespace App\Models;

use App\Enums\WorkoutStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Represents a workout that a user has planned for a specific day.
 *
 * This acts like a calendar event, linking a user to a specific training plan.
 * The 'status' field helps track if they actually did it, skipped it, or if it
 * is still upcoming.
 *
 * @property int $id
 * @property int $user_id
 * @property int $training_id
 * @property Carbon $date
 * @property WorkoutStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Training $training
 */
class ScheduledWorkout extends Model
{
    protected $fillable = [
        'user_id',
        'training_id',
        'date',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    protected function casts(): array
    {
        return [
            // We strip the time and only keep Year-Month-Day because
            // scheduled workouts belong to a calendar day, not an exact
            // time.
            'date' => 'date:Y-m-d',
            'status' => WorkoutStatus::class,
        ];
    }
}
