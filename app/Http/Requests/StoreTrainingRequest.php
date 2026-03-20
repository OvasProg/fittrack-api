<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'difficulty_level' => 'required|string|in:Beginner,Intermediate,Advanced',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'exercise_ids' => 'required|array|min:1',
            'exercise_ids.*' => 'exists:exercises,id',
        ];
    }
}
