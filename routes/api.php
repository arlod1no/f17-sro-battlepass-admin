<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\BattlePassController;
use App\Http\Controllers\QuestController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\UserBattlePassController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get("/test-me", function () {
    return 'Hello from Laravel!';
});

Route::get('/users', [UserController::class, 'index']);

// Seasons CRUD
Route::apiResource('seasons', SeasonController::class);

// Battlepasses CRUD
Route::apiResource('battlepasses', BattlePassController::class);

// Quests CRUD
Route::apiResource('quests', QuestController::class);

// Rewards CRUD
Route::apiResource('rewards', RewardController::class);

// User Battle Passes CRUD
Route::apiResource('user-battle-passes', UserBattlePassController::class);

// User-specific battle pass routes
Route::prefix('users/{jid}/battle-passes')->group(function () {
    // Get all battle passes for a user
    Route::get('/', [UserBattlePassController::class, 'getUserBattlePasses']);

    // Get active battle passes for a user
    Route::get('/active', [UserBattlePassController::class, 'getActiveBattlePasses']);

    // Get user battle pass statistics
    Route::get('/statistics', [UserBattlePassController::class, 'getStatistics']);

    // Add experience to specific battle pass
    Route::post('/{battlePassId}/experience', [UserBattlePassController::class, 'addExperience']);

    // Claim reward for specific battle pass
    Route::post('/{battlePassId}/claim-reward', [UserBattlePassController::class, 'claimReward']);
});

// Battle pass specific routes
Route::prefix('battle-passes/{battlePassId}')->group(function () {
    // Get all users enrolled in this battle pass
    Route::get('/users', function ($battlePassId) {
        $userBattlePasses = App\Models\UserBattlePass::with(['user', 'battlePass'])
            ->where('battle_pass_id', $battlePassId)
            ->orderBy('level', 'desc')
            ->orderBy('experience', 'desc')
            ->get();

        return response()->json($userBattlePasses);
    });

    // Get leaderboard for this battle pass
    Route::get('/leaderboard', function ($battlePassId) {
        $leaderboard = App\Models\UserBattlePass::with(['user'])
            ->where('battle_pass_id', $battlePassId)
            ->where('is_active', true)
            ->orderBy('level', 'desc')
            ->orderBy('experience', 'desc')
            ->take(100)
            ->get()
            ->map(function ($userBattlePass, $index) {
                return [
                    'rank' => $index + 1,
                    'jid' => $userBattlePass->jid,
                    'username' => $userBattlePass->user->StrUserID ?? 'Unknown',
                    'level' => $userBattlePass->level,
                    'experience' => $userBattlePass->experience,
                    'completion_percentage' => $userBattlePass->completion_percentage,
                    'is_completed' => $userBattlePass->is_completed,
                    'is_premium' => $userBattlePass->is_premium
                ];
            });

        return response()->json($leaderboard);
    });
});

// Admin routes for managing user battle passes
Route::prefix('admin')->group(function () {
    // Bulk operations
    Route::post('/battle-passes/bulk-assign', function (Request $request) {
        $request->validate([
            'jids' => 'required|array',
            'jids.*' => 'integer|exists:TB_User,JID',
            'battle_pass_id' => 'required|exists:battle_passes,id'
        ]);

        $successCount = 0;
        $errors = [];

        foreach ($request->jids as $jid) {
            try {
                // Check if user already has this battle pass
                $existing = App\Models\UserBattlePass::where('jid', $jid)
                    ->where('battle_pass_id', $request->battle_pass_id)
                    ->exists();

                if (!$existing) {
                    App\Models\UserBattlePass::create([
                        'jid' => $jid,
                        'battle_pass_id' => $request->battle_pass_id,
                        'started_at' => now(),
                        'status' => 'active'
                    ]);
                    $successCount++;
                } else {
                    $errors[] = "JID {$jid} already has this battle pass";
                }
            } catch (Exception $e) {
                $errors[] = "Failed to assign battle pass to JID {$jid}: " . $e->getMessage();
            }
        }

        return response()->json([
            'message' => "Successfully assigned battle pass to {$successCount} users",
            'success_count' => $successCount,
            'errors' => $errors
        ]);
    });

    // Bulk experience addition
    Route::post('/battle-passes/bulk-experience', function (Request $request) {
        $request->validate([
            'jids' => 'required|array',
            'jids.*' => 'integer',
            'battle_pass_id' => 'required|exists:battle_passes,id',
            'experience' => 'required|integer|min:1|max:10000'
        ]);

        $updated = App\Models\UserBattlePass::whereIn('jid', $request->jids)
            ->where('battle_pass_id', $request->battle_pass_id)
            ->where('is_active', true)
            ->get();

        $levelUps = 0;
        foreach ($updated as $userBattlePass) {
            $oldLevel = $userBattlePass->level;
            $userBattlePass->addExperience($request->experience);
            if ($userBattlePass->level > $oldLevel) {
                $levelUps++;
            }
        }

        return response()->json([
            'message' => "Added {$request->experience} experience to " . $updated->count() . " users",
            'updated_count' => $updated->count(),
            'level_ups' => $levelUps
        ]);
    });
});
