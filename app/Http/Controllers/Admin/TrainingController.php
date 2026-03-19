<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    public function index()
    {
        $trainings = Training::with('exercises')->get();
        return response()->json($trainings, 200);
    }

    public function store(Request $request)
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

        $training->exercises()->sync($validated['exercise_ids']);

        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'created_training',
            'details' => json_encode(['training_id' => $training->id, 'name' => $training->name])
        ]);

        return response()->json([
            'message' => 'Training created successfully.',
            'training' => $training->load('exercises')
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $training = Training::findOrFail($id);
        $trainingName = $training->name;

        $training->delete();

        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'deleted_training',
            'details' => json_encode(['training_name' => $trainingName])
        ]);

        return response()->json(['message' => 'Training deleted successfully.'], 200);
    }
}
