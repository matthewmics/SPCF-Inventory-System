<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\InventoryItem;
use App\Models\Room;
use Illuminate\Http\Request;
use App\Models\InventoryParentItem;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ActivityLogController;

class RoomController extends Controller
{


    public function getItemParents($room_id)
    {
        // $parentItems = InventoryParentItem::with(['inventory_items' => function ($query) use ($room_id) {
        //     $query->select('id', 'inventory_parent_item_id')->where('room_id', $room_id)->where('is_disposed', false);
        // }])
        //     ->orderBy('id')->get();
        // 'name','item_type','created_at','updated_at'
        return DB::table('inventory_parent_items')
            ->select(DB::raw("id,name,item_type,created_at,updated_at,
            (SELECT count(1) FROM inventory_items b
            where is_disposed is false 
            AND inventory_parent_items.id = inventory_parent_item_id
            AND room_id = $room_id
            ) as qty_available
            "))
            ->get();
    }

    public function getItems($room_id, $inventory_parent_item_id)
    {
        return InventoryItem::with(['room', 'transfer_requests' => function ($query) {
            $query->whereNotIn('status', ['completed', 'rejected', 'disposed']);
        }, 'repair_requests' => function ($query) {
            $query->whereNotIn('status', ['completed', 'rejected', 'disposed', 'replaced', 'PO created', 'repaired']);
        }])
            ->where('room_id', $room_id)
            ->where('is_disposed', false)
            ->where('inventory_parent_item_id', $inventory_parent_item_id)
            ->orderBy('id')
            ->get();
    }

    public function getAllItems($id)
    {
        // return InventoryItem::where('room_id', $id)
        //     ->where('is_disposed', false)
        //     ->orderBy('id')
        //     ->get();

        return Room::with(['inventory_items', 'inventory_items.inventory_parent_item'])->find($id);
    }

    public function index()
    {
        return Room::with('building')->whereNotNull('building_id')->orderBy('id')->get();
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'room_type' => 'required'
        ]);

        $current_user = auth()->user();

        ActivityLogController::store(auth()->user(), "<b>$current_user->name</b> created room <b>$request->name</b>");

        return Room::create($request->all());
    }


    public function show($id)
    {
        return Room::with(['building'])->find($id);
    }


    public function update(Request $request, $id)
    {

        $request->validate([
            'name' => 'required',
            'room_type' => 'required'
        ]);

        $room = Room::find($id);

        $current_user = auth()->user();

        ActivityLogController::store(auth()->user(), "<b>$current_user->name</b> edited room <b>$room->name</b>");

        $room->update($request->all());
        $room->load('building');
        return $room;
    }


    public function destroy($id)
    {
        $room = Room::find($id);

        $current_user = auth()->user();

        ActivityLogController::store(auth()->user(), "<b>$current_user->name</b> deleted room <b>$room->name</b>");

        $room->delete();
        return response()->noContent();
    }
}
