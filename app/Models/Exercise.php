<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    protected $fillable = [
        'name',
        'target_muscle',
        'base_multiplier',
    ];

    public function trainings()
    {
        return $this->belongsToMany(Training::class, 'training_exercises')
            ->withPivot('default_sets', 'default_reps')
            ->withTimestamps();
    }

    public function workoutSets()
    {
        return $this->hasMany(WorkoutSet::class);
    }
}
