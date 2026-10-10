<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentGuideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'equipment_id' => [
                'required',
                'integer',
                Rule::exists('equipment', 'id'),
            ],
            'title' => ['required', 'string', 'max:255'],
            'usage_context' => ['required', 'string', 'max:255'],
            'instructions' => ['required', 'string', 'min:20'],
            'safety_precautions' => ['nullable', 'string'],
            'difficulty_level' => [
                'required',
                Rule::in(['beginner', 'intermediate', 'advanced']),
            ],
            'video_url' => ['nullable', 'url', 'max:255'],
            'status' => [
                'required',
                Rule::in(['draft', 'pending', 'published']),
            ],
        ];
    }
}
