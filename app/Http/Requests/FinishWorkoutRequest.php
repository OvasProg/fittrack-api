<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FinishWorkoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scheduled_workout_id' => 'nullable|exists:scheduled_workouts,id',
            'sets' => 'required|array|min:0',
            'sets.*.exercise_id' => 'required|exists:exercises,id',
            'sets.*.set_number' => 'required|integer|min:1',
            'sets.*.weight_used' => 'required|numeric|min:0',
            'sets.*.reps_completed' => 'required|integer|min:0',
        ];
    }
}
