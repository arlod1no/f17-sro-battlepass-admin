<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:255',
            'required_action' => 'nullable|string|max:255',
            'required_count' => 'sometimes|integer|min:1',
            'is_active' => 'boolean',
            'battle_pass_id' => 'required|exists:battle_passes,id',
            'completed_at' => 'nullable|date'
        ];
    }
}
