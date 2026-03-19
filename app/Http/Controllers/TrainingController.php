<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Training;

class TrainingController extends Controller
{
    public function index(Request $request)
    {
        $query = Training::query();

        if ($request->has('level')) {
            $level = $request->query('level');

            if (in_array($level, ['Beginner', 'Intermediate', 'Advanced'])) {
                $query->where('difficulty_level', $level);
            }
        }

        $trainings = $query->get();

        return response()->json([
            'trainings' => $trainings
        ], 200);
    }

    public function show(Training $training)
    {
        $training->load('exercises');

        return response()->json([
            'training' => $training
        ], 200);
    }
}
