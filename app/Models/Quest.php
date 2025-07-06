<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'required_action',
        'required_count',
        'is_active',
        'battle_pass_id',
        'completed_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'completed_at' => 'datetime',
        'required_count' => 'integer',
    ];

    // Relationships
    public function battlePass()
    {
        return $this->belongsTo(BattlePass::class, 'battle_pass_id');
    }

    public function rewards()
    {
        return $this->hasMany(Reward::class);
    }
}
