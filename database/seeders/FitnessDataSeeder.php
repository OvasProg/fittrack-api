<?php

namespace Database\Seeders;

use App\Enums\ExperienceLevel;
use App\Models\Exercise;
use App\Models\Training;
use Illuminate\Database\Seeder;

class FitnessDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the Global Exercise Library
        $exercisesData = [
            ['name' => 'Barbell Squat', 'target_muscle' => 'Quads', 'base_multiplier' => 1.0],
            ['name' => 'Romanian Deadlift', 'target_muscle' => 'Hamstrings', 'base_multiplier' => 0.9],
            ['name' => 'Barbell Bench Press', 'target_muscle' => 'Chest', 'base_multiplier' => 0.75],
            ['name' => 'Overhead Press', 'target_muscle' => 'Shoulders', 'base_multiplier' => 0.5],
            ['name' => 'Barbell Row', 'target_muscle' => 'Back', 'base_multiplier' => 0.6],
            ['name' => 'Lat Pulldown', 'target_muscle' => 'Back', 'base_multiplier' => 0.6],
            ['name' => 'Dumbbell Bicep Curl', 'target_muscle' => 'Biceps', 'base_multiplier' => 0.15],
            ['name' => 'Tricep Pushdown', 'target_muscle' => 'Triceps', 'base_multiplier' => 0.2],
            ['name' => 'Leg Press', 'target_muscle' => 'Quads', 'base_multiplier' => 1.5],
            ['name' => 'Leg Curl', 'target_muscle' => 'Hamstrings', 'base_multiplier' => 0.3],
            ['name' => 'Calf Raise', 'target_muscle' => 'Calves', 'base_multiplier' => 0.4],
            ['name' => 'Incline Dumbbell Press', 'target_muscle' => 'Chest', 'base_multiplier' => 0.3],
            ['name' => 'Lateral Raise', 'target_muscle' => 'Shoulders', 'base_multiplier' => 0.1],
            ['name' => 'Face Pull', 'target_muscle' => 'Rear Delts', 'base_multiplier' => 0.15],
            ['name' => 'Bulgarian Split Squat', 'target_muscle' => 'Legs', 'base_multiplier' => 0.3],
            ['name' => 'Cable Crunch', 'target_muscle' => 'Core', 'base_multiplier' => 0.4],
            ['name' => 'Conventional Deadlift', 'target_muscle' => 'Posterior Chain', 'base_multiplier' => 1.2],
            ['name' => 'Front Squat', 'target_muscle' => 'Quads', 'base_multiplier' => 0.8],
        ];

        $exercises = [];
        foreach ($exercisesData as $data) {
            // Save to DB and keep the model in an array mapped by name for easy attachment
            $exercises[$data['name']] = Exercise::create($data);
        }

        // 2. Define the Trainings (15 total: 5 Beginner, 5 Intermediate, 5 Advanced)
        $trainingsData = [
            // --- BEGINNER ---
            [
                'name' => 'Full Body Foundation A',
                'difficulty_level' => ExperienceLevel::BEGINNER,
                'description' => 'A basic introduction to compound movements.',
                'routine' => [
                    ['exercise' => 'Barbell Squat', 'sets' => 3, 'reps' => 10],
                    ['exercise' => 'Barbell Bench Press', 'sets' => 3, 'reps' => 10],
                    ['exercise' => 'Barbell Row', 'sets' => 3, 'reps' => 10],
                ],
            ],
            [
                'name' => 'Full Body Foundation B',
                'difficulty_level' => ExperienceLevel::BEGINNER,
                'description' => 'Secondary full body day to build baseline strength.',
                'routine' => [
                    ['exercise' => 'Romanian Deadlift', 'sets' => 3, 'reps' => 10],
                    ['exercise' => 'Overhead Press', 'sets' => 3, 'reps' => 10],
                    ['exercise' => 'Lat Pulldown', 'sets' => 3, 'reps' => 12],
                ],
            ],
            [
                'name' => 'Upper Body Starter',
                'difficulty_level' => ExperienceLevel::BEGINNER,
                'description' => 'Upper body focus for new lifters.',
                'routine' => [
                    ['exercise' => 'Barbell Bench Press', 'sets' => 3, 'reps' => 10],
                    ['exercise' => 'Lat Pulldown', 'sets' => 3, 'reps' => 12],
                    ['exercise' => 'Dumbbell Bicep Curl', 'sets' => 3, 'reps' => 12],
                ],
            ],
            [
                'name' => 'Lower Body Starter',
                'difficulty_level' => ExperienceLevel::BEGINNER,
                'description' => 'Lower body focus for new lifters.',
                'routine' => [
                    ['exercise' => 'Leg Press', 'sets' => 3, 'reps' => 12],
                    ['exercise' => 'Leg Curl', 'sets' => 3, 'reps' => 12],
                    ['exercise' => 'Calf Raise', 'sets' => 3, 'reps' => 15],
                ],
            ],
            [
                'name' => 'Core & Conditioning',
                'difficulty_level' => ExperienceLevel::BEGINNER,
                'description' => 'Light day focusing on core stability.',
                'routine' => [
                    ['exercise' => 'Cable Crunch', 'sets' => 3, 'reps' => 15],
                    ['exercise' => 'Face Pull', 'sets' => 3, 'reps' => 15],
                ],
            ],

            // --- INTERMEDIATE ---
            [
                'name' => 'Push Day (Hypertrophy)',
                'difficulty_level' => ExperienceLevel::INTERMEDIATE,
                'description' => 'Chest, shoulders, and triceps focused on muscle growth.',
                'routine' => [
                    ['exercise' => 'Barbell Bench Press', 'sets' => 4, 'reps' => 8],
                    ['exercise' => 'Incline Dumbbell Press', 'sets' => 3, 'reps' => 10],
                    ['exercise' => 'Overhead Press', 'sets' => 3, 'reps' => 8],
                    ['exercise' => 'Lateral Raise', 'sets' => 3, 'reps' => 15],
                    ['exercise' => 'Tricep Pushdown', 'sets' => 3, 'reps' => 12],
                ],
            ],
            [
                'name' => 'Pull Day (Hypertrophy)',
                'difficulty_level' => ExperienceLevel::INTERMEDIATE,
                'description' => 'Back and biceps focused on muscle growth.',
                'routine' => [
                    ['exercise' => 'Barbell Row', 'sets' => 4, 'reps' => 8],
                    ['exercise' => 'Lat Pulldown', 'sets' => 3, 'reps' => 10],
                    ['exercise' => 'Face Pull', 'sets' => 3, 'reps' => 15],
                    ['exercise' => 'Dumbbell Bicep Curl', 'sets' => 4, 'reps' => 10],
                ],
            ],
            [
                'name' => 'Leg Day (Hypertrophy)',
                'difficulty_level' => ExperienceLevel::INTERMEDIATE,
                'description' => 'Quad and hamstring focus.',
                'routine' => [
                    ['exercise' => 'Barbell Squat', 'sets' => 4, 'reps' => 8],
                    ['exercise' => 'Romanian Deadlift', 'sets' => 3, 'reps' => 10],
                    ['exercise' => 'Bulgarian Split Squat', 'sets' => 3, 'reps' => 10],
                    ['exercise' => 'Calf Raise', 'sets' => 4, 'reps' => 15],
                ],
            ],
            [
                'name' => 'Upper Body Power',
                'difficulty_level' => ExperienceLevel::INTERMEDIATE,
                'description' => 'Heavy upper body compound movements.',
                'routine' => [
                    ['exercise' => 'Barbell Bench Press', 'sets' => 5, 'reps' => 5],
                    ['exercise' => 'Barbell Row', 'sets' => 5, 'reps' => 5],
                    ['exercise' => 'Overhead Press', 'sets' => 3, 'reps' => 6],
                ],
            ],
            [
                'name' => 'Lower Body Power',
                'difficulty_level' => ExperienceLevel::INTERMEDIATE,
                'description' => 'Heavy lower body compound movements.',
                'routine' => [
                    ['exercise' => 'Barbell Squat', 'sets' => 5, 'reps' => 5],
                    ['exercise' => 'Conventional Deadlift', 'sets' => 3, 'reps' => 5],
                    ['exercise' => 'Leg Press', 'sets' => 3, 'reps' => 8],
                ],
            ],

            // --- ADVANCED ---
            [
                'name' => 'Push (Strength Focus)',
                'difficulty_level' => ExperienceLevel::ADVANCED,
                'description' => 'Maximal load pushing movements.',
                'routine' => [
                    ['exercise' => 'Barbell Bench Press', 'sets' => 5, 'reps' => 3],
                    ['exercise' => 'Overhead Press', 'sets' => 5, 'reps' => 5],
                    ['exercise' => 'Incline Dumbbell Press', 'sets' => 4, 'reps' => 8],
                ],
            ],
            [
                'name' => 'Pull (Strength Focus)',
                'difficulty_level' => ExperienceLevel::ADVANCED,
                'description' => 'Maximal load pulling movements.',
                'routine' => [
                    ['exercise' => 'Conventional Deadlift', 'sets' => 5, 'reps' => 3],
                    ['exercise' => 'Barbell Row', 'sets' => 5, 'reps' => 5],
                    ['exercise' => 'Lat Pulldown', 'sets' => 4, 'reps' => 8],
                ],
            ],
            [
                'name' => 'Legs (Strength Focus)',
                'difficulty_level' => ExperienceLevel::ADVANCED,
                'description' => 'Maximal load leg movements.',
                'routine' => [
                    ['exercise' => 'Barbell Squat', 'sets' => 5, 'reps' => 3],
                    ['exercise' => 'Front Squat', 'sets' => 4, 'reps' => 6],
                    ['exercise' => 'Romanian Deadlift', 'sets' => 4, 'reps' => 8],
                ],
            ],
            [
                'name' => 'Arnold Split: Chest & Back',
                'difficulty_level' => ExperienceLevel::ADVANCED,
                'description' => 'High volume antagonist supersets.',
                'routine' => [
                    ['exercise' => 'Barbell Bench Press', 'sets' => 4, 'reps' => 8],
                    ['exercise' => 'Barbell Row', 'sets' => 4, 'reps' => 8],
                    ['exercise' => 'Incline Dumbbell Press', 'sets' => 4, 'reps' => 10],
                    ['exercise' => 'Lat Pulldown', 'sets' => 4, 'reps' => 10],
                ],
            ],
            [
                'name' => 'Arnold Split: Shoulders & Arms',
                'difficulty_level' => ExperienceLevel::ADVANCED,
                'description' => 'High volume accessory targeting.',
                'routine' => [
                    ['exercise' => 'Overhead Press', 'sets' => 4, 'reps' => 8],
                    ['exercise' => 'Lateral Raise', 'sets' => 4, 'reps' => 12],
                    ['exercise' => 'Dumbbell Bicep Curl', 'sets' => 4, 'reps' => 10],
                    ['exercise' => 'Tricep Pushdown', 'sets' => 4, 'reps' => 10],
                ],
            ],
        ];

        // 3. Insert Trainings and Attach Exercises
        foreach ($trainingsData as $data) {
            $training = Training::create([
                'name' => $data['name'],
                'difficulty_level' => $data['difficulty_level'],
                'description' => $data['description'],
                'image_url' => 'https://via.placeholder.com/400x200.png?text=' . urlencode($data['name']), // Placeholder for future S3 images
            ]);

            foreach ($data['routine'] as $routineItem) {
                // Attach the exercise to the training using the pivot table mapping
                $exerciseId = $exercises[$routineItem['exercise']]->id;

                $training->exercises()->attach($exerciseId, [
                    'default_sets' => $routineItem['sets'],
                    'default_reps' => $routineItem['reps'],
                ]);
            }
        }
    }
}
