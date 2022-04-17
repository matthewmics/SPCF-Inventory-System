<?php

namespace App\Http\Controllers;

use App\Models\FileStorage;
use App\Models\InventoryItem;
use App\Models\Notification;
use App\Models\Room;
use App\Models\TransferRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\ActivityLogController;

class TransferRequestController extends Controller
{

    public function getRequests()
    {

        $role = auth()->user()->role;

        if ($role === 'department') {
            return TransferRequest::with(['item', 'current_room', 'destination_room', 'requestor', 'item.inventory_parent_item', 'handler'])
                ->orderBy('created_at', 'desc')
                ->where('requestor_user_id', auth()->user()->id)
                ->get();
        } else if ($role === 'its') {
            return TransferRequest::with(['item', 'current_room', 'destination_room', 'requestor', 'item.inventory_parent_item', 'handler'])
                ->where('item_type', 'PC')
                ->orderBy('created_at', 'desc')
                ->get();
        } else if ($role === 'ppfo') {
            return TransferRequest::with(['item', 'current_room', 'destination_room', 'requestor', 'item.inventory_parent_item', 'handler'])
                ->where('item_type', '<>', 'PC')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return TransferRequest::with(['item', 'current_room', 'destination_room', 'requestor', 'item.inventory_parent_item', 'handler'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function requestTransfer(Request $request)
    {
        $userId = auth()->user()->id;

        $request->validate([
            'destination_room_id' => 'required',
            'item_id' => 'required',
            'details' => 'required',
            'file' => 'required'
        ], [
            'file.required' => 'File attachment is required',
            'destination_room_id.required' => 'Destination room is required'
        ]);

        $base64file = base64_encode(file_get_contents($request->file('file')->path()));

        $file_storage = FileStorage::create([
            'name' => uniqid() . "_" . $request->file('file')->getClientOriginalName(),
            'base64' => $base64file
        ]);

        $item = InventoryItem::with('inventory_parent_item')->find($request->item_id);

        $request['requestor_user_id'] = $userId;
        $request['current_room_id'] = $item->room_id;
        $request['file_storage_id'] = $file_storage->id;
        $request['item_type'] = $item->inventory_parent_item->item_type;

        $transfer_request = TransferRequest::create($request->all());

        $room_destination = Room::find($request['destination_room_id']);
        $notified_users = null;
        $notifications_to_insert = [];

        if ($request['item_type'] === 'PC') {
            $notified_users = User::select('id')->where('role', 'admin')
                ->orWhere('role', 'its')
                ->get();
        } else {
            $notified_users = User::select('id')->where('role', 'admin')
                ->orWhere('role', 'ppfo')
                ->get();
        }

        $requestor_name = auth()->user()->name;
        $room_destination_name = $room_destination->name;
        $item_to_transfer_name = $item->inventory_parent_item->name;

        $activity_text = "<b>$requestor_name</b> has requested to transfer a <b>$item_to_transfer_name</b> to <b>$room_destination_name</b>";

        foreach ($notified_users as $notified_user) {
            array_push($notifications_to_insert, [
                'user_id' => $notified_user->id,
                'message' => $activity_text,
                "created_at" =>  \Carbon\Carbon::now('UTC'),
                "updated_at" => \Carbon\Carbon::now('UTC'),
            ]);
        }        

        ActivityLogController::store(auth()->user(), $activity_text);

        Notification::insert($notifications_to_insert);

        return $transfer_request;
    }
}
