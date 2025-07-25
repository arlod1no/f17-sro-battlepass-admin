<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Http\Requests\StoreRewardRequest;
use App\Http\Requests\UpdateRewardRequest;

class RewardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Reward::with(['quest.battlePass.season'])->get();
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
    public function store(StoreRewardRequest $request)
    {
        $reward = Reward::create($request->validated());
        $reward->load(['quest.battlePass.season']);
        return response()->json($reward, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Reward $reward)
    {
        $reward->load(['quest.battlePass.season']);
        return $reward;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reward $reward)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRewardRequest $request, Reward $reward)
    {
        $reward->update($request->validated());
        $reward->load(['quest.battlePass.season']);
        return response()->json($reward);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reward $reward)
    {
        $reward->delete();
        return response()->json(null, 204);
    }
}
