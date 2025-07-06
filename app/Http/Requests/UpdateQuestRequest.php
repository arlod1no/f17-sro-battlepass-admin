<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'type' => 'sometimes|required|string|max:255',
            'required_action' => 'sometimes|nullable|string|max:255',
            'required_count' => 'sometimes|integer|min:1',
            'is_active' => 'sometimes|boolean',
            'battle_pass_id' => 'sometimes|required|exists:battle_passes,id',
            'completed_at' => 'sometimes|nullable|date'
        ];
    }
}
