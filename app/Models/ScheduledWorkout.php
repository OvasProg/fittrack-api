<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledWorkout extends Model
{
    protected $fillable = [
        'user_id',
        'training_id',
        'date',
        'status',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function training()
    {
        return $this->belongsTo(Training::class);
    }
}
