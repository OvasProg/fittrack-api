<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index()
    {
        $exercises = Exercise::all();
        return response()->json($exercises, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:exercises,name',
            'target_muscle' => 'required|string|max:255',
            'base_multiplier' => 'nullable|numeric|min:0'
        ]);

        if (!isset($validated['base_multiplier'])) {
            $validated['base_multiplier'] = 1.0;
        }

        $exercise = Exercise::create($validated);

        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'created_exercise',
            'details' => json_encode(['exercise_id' => $exercise->id, 'name' => $exercise->name])
        ]);

        return response()->json([
            'message' => 'Exercise added to library successfully.',
            'exercise' => $exercise
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $exercise = Exercise::findOrFail($id);
        $exerciseName = $exercise->name;

        $exercise->delete();

        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'deleted_exercise',
            'details' => json_encode(['exercise_name' => $exerciseName])
        ]);

        return response()->json(['message' => 'Exercise removed from library.'], 200);
    }
}
