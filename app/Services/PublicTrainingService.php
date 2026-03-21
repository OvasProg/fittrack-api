<?php

namespace App\Services;

use App\Enums\ExperienceLevel;
use App\Models\Training;
use Illuminate\Database\Eloquent\Collection;

class PublicTrainingService
{
    public function getTrainings(?string $level): Collection
    {
        $query = Training::query();

        if ($level) {
            if (in_array($level, array_column(ExperienceLevel::cases(), 'value'))) {
                $query->where('difficulty_level', $level);
            }
        }

        return $query->get();
    }
}
