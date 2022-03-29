<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\InventoryItem;
use App\Models\Room;
use Illuminate\Http\Request;
use App\Models\InventoryParentItem;

class RoomController extends Controller
{


    public function getItemParents($room_id)
    {
        $parentItems = InventoryParentItem::with(['inventory_items' => function ($query) use ($room_id) {
            $query->select('id', 'inventory_parent_item_id')->where('room_id', $room_id)->where('is_disposed', false);
        }])
            ->orderBy('id')->get();

        return $parentItems;
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
        return Room::with('building')->orderBy('id')->get();
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'room_type' => 'required'
        ]);

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
        $room->update($request->all());
        $room->load('building');
        return $room;
    }


    public function destroy($id)
    {
        $room = Room::find($id);
        $room->delete();
        return response()->noContent();
    }
}
