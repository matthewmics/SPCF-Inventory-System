<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ActivityLogController;
use App\Models\Building;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use App\Models\Room;

class BuildingController extends Controller
{

    public function items($id)
    {
        return InventoryItem::with(['room','inventory_parent_item'])->where('is_disposed', false)
            ->whereIn(
                'room_id',
                Room::select('id')->whereIn('building_id', Building::select('id')->find($id))
            )
            ->get();
    }

    public function index()
    {
        return Building::orderBy('id')->get();
    }


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


    public function show($id)
    {
        return Building::find($id);
    }


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
