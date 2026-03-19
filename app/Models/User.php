<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
        'training_days'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'training_days' => 'array',
    ];

    protected static function booted()
    {
        // PERMANENT delete, not soft delete.
        static::forceDeleted(function ($user) {
            $user->workoutSessions()->forceDelete();
            $user->scheduledWorkouts()->forceDelete();
        });
    }

    // Relationships
    public function scheduledWorkouts()
    {
        return $this->hasMany(ScheduledWorkout::class);
    }

    public function workoutSessions()
    {
        return $this->hasMany(WorkoutSession::class);
    }
}
