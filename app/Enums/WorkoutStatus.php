<?php

namespace App\Enums;

enum WorkoutStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case MISSED = 'missed';
    case REST_DAY = 'rest_day';
}
