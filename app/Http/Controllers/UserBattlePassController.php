<?php

namespace App\Http\Controllers;

use App\Models\UserBattlePass;
use App\Models\User;
use App\Models\BattlePass;
use App\Http\Requests\StoreUserBattlePassRequest;
use App\Http\Requests\UpdateUserBattlePassRequest;
use Illuminate\Http\Request;

class UserBattlePassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = UserBattlePass::with(['user']);

        // Filter by JID if provided
        if ($request->has('jid')) {
            $query->forUser($request->jid);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by completion status
        if ($request->has('is_completed')) {
            $query->where('is_completed', $request->boolean('is_completed'));
        }

        $userBattlePasses = $query->orderBy('created_at', 'desc')->get();

        return response()->json($userBattlePasses);
    }

    /**
     * Get user's battle passes by JID
     */
    public function getUserBattlePasses($jid)
    {
        $user = User::where('JID', $jid)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $battlePasses = UserBattlePass::with(['battlePass.season'])
            ->forUser($jid)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'user' => [
                'jid' => $user->JID,
                'username' => $user->StrUserID,
                'is_vip' => $user->isVip(),
                'is_active' => $user->isActive()
            ],
            'battle_passes' => $battlePasses
        ]);
    }

    /**
     * Get active battle passes for user
     */
    public function getActiveBattlePasses($jid)
    {
        $battlePasses = UserBattlePass::with(['battlePass.season'])
            ->forUser($jid)
            ->active()
            ->get();

        return response()->json($battlePasses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserBattlePassRequest $request)
    {
        try {
            // Kiểm tra user tồn tại
        $user = User::where('JID', $request->user_id)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Kiểm tra battle pass tồn tại và active
        $battlePass = BattlePass::where('id', $request->battle_pass_id)
            ->where('is_active', true)
            ->first();
        if (!$battlePass) {
            return response()->json(['message' => 'Battle pass not found or inactive'], 404);
        }

        // Kiểm tra user đã có battle pass này chưa
        $existingUserBattlePass = UserBattlePass::where('jid', $request->user_id)
            ->where('battle_pass_id', $request->battle_pass_id)
            ->first();

        if ($existingUserBattlePass) {
            return response()->json(['message' => 'User already has this battle pass'], 409);
        }

        $userBattlePass = UserBattlePass::create([
            'jid' => $request->user_id,
            'battle_pass_id' => $request->battle_pass_id,
            'started_at' => now(),
            'status' => 'active',
            'type' => $request->type ?? 'standard',
            'is_premium' => $request->is_premium ?? false
        ]);

        $userBattlePass->load(['battlePass.season', 'user']);

        return response()->json($userBattlePass, 201);
        } catch (\Exception $e) {
            dd($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(UserBattlePass $userBattlePass)
    {
        $userBattlePass->load(['battlePass.season', 'user']);
        return response()->json($userBattlePass);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserBattlePassRequest $request, UserBattlePass $userBattlePass)
    {
        $userBattlePass->update($request->validated());
        $userBattlePass->load(['battlePass.season', 'user']);

        return response()->json($userBattlePass);
    }

    /**
     * Add experience to user's battle pass
     */
    public function addExperience(Request $request, $jid, $battlePassId)
    {
        $request->validate([
            'experience' => 'required|integer|min:1|max:10000'
        ]);

        $userBattlePass = UserBattlePass::where('jid', $jid)
            ->where('battle_pass_id', $battlePassId)
            ->active()
            ->first();

        if (!$userBattlePass) {
            return response()->json(['message' => 'Active battle pass not found for this user'], 404);
        }

        $userBattlePass->addExperience($request->experience);
        $userBattlePass->load(['battlePass.season']);

        return response()->json([
            'message' => 'Experience added successfully',
            'user_battle_pass' => $userBattlePass,
            'level_up' => $userBattlePass->wasChanged('level')
        ]);
    }

    /**
     * Claim reward for completed battle pass
     */
    public function claimReward($jid, $battlePassId)
    {
        $userBattlePass = UserBattlePass::where('jid', $jid)
            ->where('battle_pass_id', $battlePassId)
            ->first();

        if (!$userBattlePass) {
            return response()->json(['message' => 'Battle pass not found for this user'], 404);
        }

        if (!$userBattlePass->canClaimReward()) {
            return response()->json(['message' => 'Cannot claim reward - battle pass not completed or already claimed'], 400);
        }

        $userBattlePass->claimReward();

        return response()->json([
            'message' => 'Reward claimed successfully',
            'user_battle_pass' => $userBattlePass
        ]);
    }

    /**
     * Get battle pass statistics for user
     */
    public function getStatistics($jid)
    {
        $user = User::where('JID', $jid)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $stats = [
            'total_battle_passes' => UserBattlePass::forUser($jid)->count(),
            'active_battle_passes' => UserBattlePass::forUser($jid)->active()->count(),
            'completed_battle_passes' => UserBattlePass::forUser($jid)->completed()->count(),
            'premium_battle_passes' => UserBattlePass::forUser($jid)->premium()->count(),
            'total_experience' => UserBattlePass::forUser($jid)->sum('total_experience'),
            'total_levels' => UserBattlePass::forUser($jid)->sum('level'),
            'unclaimed_rewards' => UserBattlePass::forUser($jid)
                ->where('is_completed', true)
                ->where('is_claimed', false)
                ->count()
        ];

        return response()->json([
            'user' => [
                'jid' => $user->JID,
                'username' => $user->StrUserID,
                'is_vip' => $user->isVip()
            ],
            'statistics' => $stats
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserBattlePass $userBattlePass)
    {
        $userBattlePass->delete();
        return response()->json(null, 204);
    }
}
