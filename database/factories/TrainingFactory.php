<?php

namespace Database\Factories;

use App\Enums\ExperienceLevel;
use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingFactory extends Factory
{
    protected $model = Training::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'difficulty_level' => $this->faker->randomElement(ExperienceLevel::cases()),
        ];
    }
}
