<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBattlePass extends Model
{
    use HasFactory;

    protected $fillable = [
        'jid',
        'battle_pass_id',
        'level',
        'experience',
        'is_active',
        'is_completed',
        'completed_at',
        'started_at',
        'ended_at',
        'status',
        'type',
        'name',
        'description',
        'total_levels',
        'total_experience',
        'is_claimed',
        'claimed_at',
        'is_visible',
        'is_premium',
        'is_active_for_user'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_completed' => 'boolean',
        'is_claimed' => 'boolean',
        'is_visible' => 'boolean',
        'is_premium' => 'boolean',
        'is_active_for_user' => 'boolean',
        'completed_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    /**
     * Relationship với BattlePass
     */
    public function battlePass()
    {
        return $this->belongsTo(BattlePass::class);
    }

    /**
     * Relationship với TB_User qua JID
     * Assuming you have a User model that maps to TB_User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'jid', 'JID');
    }

    /**
     * Scope để lấy battle pass đang active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope để lấy battle pass đã hoàn thành
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope để lấy battle pass của user cụ thể
     */
    public function scopeForUser($query, $jid)
    {
        return $query->where('jid', $jid);
    }

    /**
     * Scope để lấy battle pass premium
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Tính phần trăm hoàn thành
     */
    public function getCompletionPercentageAttribute()
    {
        if ($this->total_levels <= 0) {
            return 0;
        }

        return min(100, ($this->level / $this->total_levels) * 100);
    }

    /**
     * Kiểm tra xem có thể claim reward không
     */
    public function canClaimReward()
    {
        return $this->is_completed && !$this->is_claimed;
    }

    /**
     * Tăng experience và level
     */
    public function addExperience($amount)
    {
        $this->experience += $amount;
        $this->total_experience += $amount;

        // Tính level mới dựa trên experience (giả sử mỗi level cần 1000 exp)
        $expPerLevel = 1000;
        $newLevel = min($this->total_levels, floor($this->total_experience / $expPerLevel));

        if ($newLevel > $this->level) {
            $this->level = $newLevel;

            // Kiểm tra xem đã hoàn thành chưa
            if ($this->level >= $this->total_levels) {
                $this->is_completed = true;
                $this->completed_at = now();
            }
        }

        $this->save();

        return $this;
    }

    /**
     * Claim reward
     */
    public function claimReward()
    {
        if ($this->canClaimReward()) {
            $this->is_claimed = true;
            $this->claimed_at = now();
            $this->save();

            return true;
        }

        return false;
    }
}
