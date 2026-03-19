<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $fillable = [
        'name',
        'description',
        'difficulty_level',
        'image_url',
    ];

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, 'training_exercises')
            ->withPivot('default_sets', 'default_reps')
            ->withTimestamps();
    }

    public function scheduledWorkouts()
    {
        return $this->hasMany(ScheduledWorkout::class);
    }

    public function workoutSessions()
    {
        return $this->hasMany(WorkoutSession::class);
    }
}
