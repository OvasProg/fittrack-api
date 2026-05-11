<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Training;
use App\Models\User;

class TrainingService
{
    public function getAllTrainings(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Training::with('exercises');

        if (!empty($filters['difficulty'])) {
            $query->where('difficulty_level', $filters['difficulty']);
        }

        if (!empty($filters['title'])) {
            $query->where('name', 'like', '%' . $filters['title'] . '%');
        }

        return $query->paginate(10);
    }

    public function createTraining(User $admin, array $data): Training
    {
        $training = Training::create([
            'name' => $data['name'],
            'difficulty_level' => $data['difficulty_level'],
            'description' => $data['description'] ?? null,
            'image_url' => $data['image_url'] ?? null,
        ]);

        $training->exercises()->sync($data['exercise_ids']);

        AuditLog::create([
            'admin_id' => $admin->id,
            'action' => 'created_training',
            'details' => json_encode([
                'training_id' => $training->id,
                'name' => $training->name,
            ]),
        ]);

        return $training;
    }

    public function updateTraining(User $admin, int $id, array $data): Training
    {
        $training = Training::findOrFail($id);

        $training->update([
            'name' => $data['name'] ?? $training->name,
            'difficulty_level' => $data['difficulty_level'] ?? $training->difficulty_level,
            'description' => $data['description'] ?? $training->description,
            'image_url' => $data['image_url'] ?? $training->image_url,
        ]);

        if (isset($data['exercise_ids'])) {
            $training->exercises()->sync($data['exercise_ids']);
        }

        AuditLog::create([
            'admin_id' => $admin->id,
            'action' => 'updated_training',
            'details' => json_encode([
                'training_id' => $training->id,
                'name' => $training->name,
            ]),
        ]);

        return $training;
    }

    public function deleteTraining(User $admin, int $id): void
    {
        $training = Training::findOrFail($id);
        $trainingName = $training->name;

        $training->delete();

        AuditLog::create([
            'admin_id' => $admin->id,
            'action' => 'deleted_training',
            'details' => json_encode(['training_name' => $trainingName]),
        ]);
    }

    public function getExercises(int $trainingId): \Illuminate\Database\Eloquent\Collection
    {
        $training = Training::findOrFail($trainingId);
        return $training->exercises;
    }

    public function attachExercise(User $admin, int $trainingId, int $exerciseId, array $pivotData = []): void
    {
        $training = Training::findOrFail($trainingId);
        $training->exercises()->syncWithoutDetaching([$exerciseId => $pivotData]);

        AuditLog::create([
            'admin_id' => $admin->id,
            'action' => 'attached_exercise',
            'details' => json_encode([
                'training_id' => $trainingId,
                'exercise_id' => $exerciseId,
            ]),
        ]);
    }

    public function detachExercise(User $admin, int $trainingId, int $exerciseId): void
    {
        $training = Training::findOrFail($trainingId);
        $training->exercises()->detach($exerciseId);

        AuditLog::create([
            'admin_id' => $admin->id,
            'action' => 'detached_exercise',
            'details' => json_encode([
                'training_id' => $trainingId,
                'exercise_id' => $exerciseId,
            ]),
        ]);
    }
}
