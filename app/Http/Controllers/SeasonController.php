<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Http\Requests\StoreSeasonRequest;
use App\Http\Requests\UpdateSeasonRequest;

class SeasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Season::all();
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
    public function store(StoreSeasonRequest $request)
    {
        $season = Season::create($request->validated());
        return response()->json($season, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Season $season)
    {
        return $season;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Season $season)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSeasonRequest $request, Season $season)
    {
        $season->update($request->validated());
        return response()->json($season);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Season $season)
    {
        $season->delete();
        return response()->json(null, 204);
    }
}
