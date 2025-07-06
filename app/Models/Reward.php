<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'description',
        'reward_points',
        'reward_item',
        'quest_id',
        'is_active',
        'is_claimed',
        'claimed_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_claimed' => 'boolean',
        'claimed_at' => 'datetime',
        'reward_points' => 'integer',
    ];

    // Relationships
    public function quest()
    {
        return $this->belongsTo(Quest::class);
    }
}
