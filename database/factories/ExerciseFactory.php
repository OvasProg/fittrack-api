<?php

namespace Database\Factories;

use App\Models\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'target_muscle' => $this->faker->randomElement(['Chest', 'Back', 'Legs', 'Shoulders', 'Arms']),
            'base_multiplier' => $this->faker->randomFloat(2, 0.5, 2.0),
        ];
    }
}
