<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserBattlePassRequest extends FormRequest
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
            'user_id' => 'required|exists:TB_User,JID',
            'battle_pass_id' => 'required|exists:battle_passes,id',
            'level' => 'sometimes|integer|min:0|max:1000',
            'experience' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'is_completed' => 'sometimes|boolean',
            'completed_at' => 'sometimes|nullable|date',
            'started_at' => 'sometimes|nullable|date',
            'ended_at' => 'sometimes|nullable|date',
            'status' => 'sometimes|string|in:active,inactive,completed,expired',
            'type' => 'sometimes|string|in:standard,premium',
            'name' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'total_levels' => 'sometimes|integer|min:1|max:1000',
            'total_experience' => 'sometimes|integer|min:0',
            'is_claimed' => 'sometimes|boolean',
            'claimed_at' => 'sometimes|nullable|date',
            'is_visible' => 'sometimes|boolean',
            'is_premium' => 'sometimes|boolean',
            'is_active_for_user' => 'sometimes|boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_id.required' => 'User is required.',
            'user_id.exists' => 'Selected user does not exist.',
            'battle_pass_id.required' => 'Battle pass is required.',
            'battle_pass_id.exists' => 'Selected battle pass does not exist.',
            'level.min' => 'Level cannot be negative.',
            'level.max' => 'Level cannot exceed 1000.',
            'total_levels.min' => 'Total levels must be at least 1.',
            'total_levels.max' => 'Total levels cannot exceed 1000.',
            'status.in' => 'Status must be one of: active, inactive, completed, expired.',
            'type.in' => 'Type must be either standard or premium.',
        ];
    }
}
