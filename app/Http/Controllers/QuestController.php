<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use App\Http\Requests\StoreQuestRequest;
use App\Http\Requests\UpdateQuestRequest;

class QuestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $quests = Quest::with(['battlePass.season'])->get();

            // Transform the data to use camelCase for consistency with frontend
            $quests = $quests->map(function ($quest) {
                $questArray = $quest->toArray();

                // Add camelCase alias for frontend compatibility
                if (isset($questArray['battle_pass'])) {
                    $questArray['battlePass'] = $questArray['battle_pass'];
                }

                return $questArray;
            });

            // Debug: Log the data to see what's happening
            \Log::info('Quests data:', $quests->toArray());

            return response()->json($quests);

        } catch (\Exception $e) {
            \Log::error('Error loading quests: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Failed to load quests',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuestRequest $request)
    {
        try {
            $quest = Quest::create($request->validated());
            $quest->load(['battlePass.season']);
            return response()->json($quest, 201);
        } catch (\Exception $e) {
            \Log::error('Error creating quest: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to create quest',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Quest $quest)
    {
        $quest->load(['battlePass.season', 'rewards']);
        return $quest;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quest $quest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuestRequest $request, Quest $quest)
    {
        try {
            $quest->update($request->validated());
            $quest->load(['battlePass.season']);
            return response()->json($quest);
        } catch (\Exception $e) {
            \Log::error('Error updating quest: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update quest',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quest $quest)
    {
        try {
            $quest->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            \Log::error('Error deleting quest: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete quest',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
