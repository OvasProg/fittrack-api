<?php

namespace App\Http\Requests;

use App\Enums\ExperienceLevel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBiometricsRequest extends FormRequest
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
            'age' => 'sometimes|integer|min:10|max:100',
            'weight' => 'sometimes|numeric|min:30|max:300',
            'height' => 'sometimes|numeric|min:100|max:250',
            'experience_level' => ['sometimes', 'string', Rule::enum(ExperienceLevel::class)],
            'training_days' => 'sometimes|array|min:1|max:7',
            'training_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ];
    }
}
