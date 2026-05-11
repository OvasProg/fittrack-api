<?php

namespace Database\Factories;

use App\Enums\ExperienceLevel;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),

            // Biometric & Training Data
            'age' => fake()->numberBetween(18, 65),
            'weight' => fake()->randomFloat(1, 50, 120),
            'height' => fake()->numberBetween(150, 200),
            'training_days' => fake()->randomElements(
                ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                3 // Picks 3 random days
            ),

            // Enums
            'role' => UserRole::FREE,
            'experience_level' => ExperienceLevel::BEGINNER,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
}
