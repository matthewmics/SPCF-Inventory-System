<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DepartmentBuilding;
use App\Models\Room;
use App\Models\User;

class DepartmentController extends Controller
{
    public function setBuildings(Request $request, $user_id)
    {

        $building_ids = $request->building_ids;

        DepartmentBuilding::where('user_id', $user_id)->delete();

        $toInsert = [];

        foreach ($building_ids as $building_id) {
            array_push($toInsert, ['user_id' => $user_id, 'building_id' => $building_id]);
        }

        return DepartmentBuilding::insert($toInsert);
    }

    public function getBuildings($user_id)
    {
        $user = User::with('buildings')->where('id', $user_id)->first();

        return $user->buildings;
    }

    public function getRooms()
    {
        $user_id = auth()->user()->id;
        $rooms = Room::whereIn(
            'building_id',
            DepartmentBuilding::select('building_id')->where('user_id', $user_id)
        )
            ->get();
        // $rooms = Room::get();

        return ['rooms' => $rooms];
    }

    public function getRooms2()
    {
        $user_id = auth()->user()->id;
        $rooms = Room::with('building')->whereIn(
            'building_id',
            DepartmentBuilding::select('building_id')->where('user_id', $user_id)
        )
            ->get();

        return $rooms;
    }
}
