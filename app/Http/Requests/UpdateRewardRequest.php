<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRewardRequest extends FormRequest
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
            'quest_id' => 'sometimes|required|exists:quests,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'type' => 'sometimes|required|string|in:points,item,currency,cosmetic,experience',
            'reward_points' => 'sometimes|required|integer|min:0',
            'reward_item' => 'sometimes|nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'is_claimed' => 'sometimes|boolean',
            'claimed_at' => 'sometimes|nullable|date'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'quest_id.required' => 'Please select a quest for this reward.',
            'quest_id.exists' => 'The selected quest does not exist.',
            'name.required' => 'Reward name is required.',
            'name.max' => 'Reward name cannot exceed 255 characters.',
            'type.required' => 'Reward type is required.',
            'type.in' => 'Reward type must be one of: points, item, currency, cosmetic, experience.',
            'reward_points.required' => 'Reward points is required.',
            'reward_points.integer' => 'Reward points must be a number.',
            'reward_points.min' => 'Reward points must be at least 0.',
            'reward_item.max' => 'Reward item cannot exceed 255 characters.',
            'claimed_at.date' => 'Claimed date must be a valid date.'
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'quest_id' => 'quest',
            'reward_points' => 'reward points',
            'reward_item' => 'reward item',
            'is_active' => 'active status',
            'is_claimed' => 'claimed status',
            'claimed_at' => 'claimed date'
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Convert string 'true'/'false' to boolean if needed
        if ($this->has('is_active') && is_string($this->is_active)) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        if ($this->has('is_claimed') && is_string($this->is_claimed)) {
            $this->merge([
                'is_claimed' => filter_var($this->is_claimed, FILTER_VALIDATE_BOOLEAN)
            ]);
        }
    }
}
