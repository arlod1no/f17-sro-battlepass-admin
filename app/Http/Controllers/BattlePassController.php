<?php

namespace App\Http\Controllers;

use App\Models\BattlePass;
use App\Http\Requests\StoreBattlePassRequest;
use App\Http\Requests\UpdateBattlePassRequest;

class BattlePassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return BattlePass::with('season')->get();
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
    public function store(StoreBattlePassRequest $request)
    {
        $battlePass = BattlePass::create($request->validated());
        $battlePass->load('season');
        return response()->json($battlePass, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(BattlePass $battlePass)
    {
        $battlePass->load(['season', 'quests']);
        return $battlePass;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BattlePass $battlePass)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBattlePassRequest $request, BattlePass $battlePass)
    {
        $battlePass->update($request->validated());
        $battlePass->load('season');
        return response()->json($battlePass);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BattlePass $battlePass)
    {
        $battlePass->delete();
        return response()->json(null, 204);
    }
}
