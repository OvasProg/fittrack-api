<?php

namespace App\Services;

use App\Enums\ExperienceLevel;
use App\Models\Training;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PublicTrainingService
{
    public function getTrainings(?string $level): Collection
    {
        $trainings = Cache::rememberForever('trainings.all', function () {
            return Training::with('exercises')->get();
        });


        if ($level) {
            if (in_array($level, array_column(ExperienceLevel::cases(), 'value'))) {
                return $trainings->where('difficulty_level', $level)->values();
            }
        }

        return $trainings;
    }
}
