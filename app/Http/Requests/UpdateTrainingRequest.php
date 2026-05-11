<?php

namespace App\Http\Requests;

use App\Enums\ExperienceLevel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTrainingRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'difficulty_level' => ['sometimes', 'string', Rule::enum(ExperienceLevel::class)],
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'exercise_ids' => 'sometimes|array|min:1',
            'exercise_ids.*' => 'exists:exercises,id',
        ];
    }
}
