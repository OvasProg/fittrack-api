<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Training;
use App\Models\User;

class TrainingService
{
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
}
