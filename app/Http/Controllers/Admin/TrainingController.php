<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Training;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin portal for managing the library of training programs.
 *
 * This controller allows administrators to design new workout
 * plans by grouping exercises together. It ensures every change
 * to the curriculum is logged for quality control.
 */
class TrainingController extends Controller
{
    public function index(): JsonResponse
    {
        // We include the exercises in the list so admins can
        // quickly see the movements associated with each plan.
        $trainings = Training::with('exercises')->get();

        return response()->json($trainings, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'difficulty_level' => 'required|string|in:Beginner,Intermediate,Advanced',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'exercise_ids' => 'required|array|min:1',
            'exercise_ids.*' => 'exists:exercises,id',
        ]);

        $training = Training::create([
            'name' => $validated['name'],
            'difficulty_level' => $validated['difficulty_level'],
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
        ]);

        // 'sync' used to manage the many-to-many
        // relationship in the pivot table.
        $training->exercises()->sync($validated['exercise_ids']);

        // Logging
        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'created_training',
            'details' => json_encode([
                'training_id' => $training->id,
                'name' => $training->name,
            ]),
        ]);

        return response()->json([
            'message' => 'Training created successfully.',
            'training' => $training->load('exercises'),
        ], 201);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $training = Training::findOrFail($id);
        $trainingName = $training->name;

        // Note: Because of our database migration (nullOnDelete),
        // deleting a training won't break a user's session history;
        // it just removes the template from the catalog.
        $training->delete();

        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'deleted_training',
            'details' => json_encode(['training_name' => $trainingName]),
        ]);

        return response()->json(['message' => 'Training deleted successfully.'], 200);
    }
}
