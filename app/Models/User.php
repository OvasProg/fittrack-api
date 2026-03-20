<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * The main User model for the FitTrack app.
 *
 * A user can be an admin, instructor, or learner. This role controls
 * what they can see and do in the app.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property int|null $age
 * @property float|null $weight
 * @property float|null $height
 * @property string|null $experience_level
 * @property string $role
 * @property array|null $training_days
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'age',
        'weight',
        'height',
        'experience_level',
        'role',
        'training_days',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'training_days' => 'array',
        ];
    }

    protected static function booted(): void
    {
        // When a user is permanently deleted, we also need to permanently delete
        // their workout history. We use forceDelete() here so the related records
        // don't just get soft-deleted by mistake.
        static::forceDeleted(function (User $user) {
            $user->workoutSessions()->forceDelete();
            $user->scheduledWorkouts()->forceDelete();
        });
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
