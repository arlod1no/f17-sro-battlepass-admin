<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRewardRequest extends FormRequest
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
            'quest_id' => 'required|exists:quests,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:points,item,currency,cosmetic,experience',
            'reward_points' => 'required|integer|min:0',
            'reward_item' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_claimed' => 'boolean',
            'claimed_at' => 'nullable|date'
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
}
