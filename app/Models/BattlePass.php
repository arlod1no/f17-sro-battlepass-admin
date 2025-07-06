<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BattlePass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'season_id',
        'is_active',
        'type'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function quests()
    {
        return $this->hasMany(Quest::class);
    }

    public function userBattlePasses()
    {
        return $this->hasMany(UserBattlePass::class);
    }
}
