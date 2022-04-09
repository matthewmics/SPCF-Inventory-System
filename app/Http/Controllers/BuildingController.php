<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ActivityLogController;
use App\Models\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Building::orderBy('id')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $user = auth()->user();
        $building_name = $request['name'];

        ActivityLogController::store(auth()->user(), "<b>$user->name</b> created building <b>$building_name</b>");

        return Building::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Building::find($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required'
        ]);        

        $building = Building::find($id);
            
        $user = auth()->user();
        $building_name = $building->name;

        ActivityLogController::store(auth()->user(), "<b>$user->name</b> edited building <b>$building_name</b>");

        $building->update($request->all());
        return $building;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $building = Building::find($id);

        $user = auth()->user();
        $building_name = $building->name;

        ActivityLogController::store(auth()->user(), "<b>$user->name</b> deleted building <b>$building_name</b>");

        $building->delete();
        return response()->noContent();
    }
}
