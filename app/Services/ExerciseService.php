<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Exercise;
use App\Models\User;

class ExerciseService
{
    public function getAllExercises(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Exercise::query();

        if (!empty($filters['target_muscle'])) {
            $query->where('target_muscle', 'like', '%' . $filters['target_muscle'] . '%');
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        return $query->paginate(10);
    }

    public function createExercise(User $admin, array $data): Exercise
    {
        if (! isset($data['base_multiplier'])) {
            $data['base_multiplier'] = 1.0;
        }

        $exercise = Exercise::create($data);

        AuditLog::create([
            'admin_id' => $admin->id,
            'action' => 'created_exercise',
            'details' => json_encode([
                'exercise_id' => $exercise->id,
                'name' => $exercise->name,
            ]),
        ]);

        return $exercise;
    }

    public function updateExercise(User $admin, int $id, array $data): Exercise
    {
        $exercise = Exercise::findOrFail($id);

        $exercise->update($data);

        AuditLog::create([
            'admin_id' => $admin->id,
            'action' => 'updated_exercise',
            'details' => json_encode([
                'exercise_id' => $exercise->id,
                'name' => $exercise->name,
            ]),
        ]);

        return $exercise;
    }

    public function deleteExercise(User $admin, int $id): void
    {
        $exercise = Exercise::findOrFail($id);
        $exerciseName = $exercise->name;

        $exercise->delete();

        AuditLog::create([
            'admin_id' => $admin->id,
            'action' => 'deleted_exercise',
            'details' => json_encode(['exercise_name' => $exerciseName]),
        ]);
    }
}
