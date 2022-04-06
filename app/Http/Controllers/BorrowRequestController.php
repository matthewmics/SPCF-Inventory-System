<?php

namespace App\Http\Controllers;

use App\Models\BorrowRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\InventoryItem;
use App\Models\Notification;
use App\Models\Room;

class BorrowRequestController extends Controller
{

    function processableRequests()
    {
        $role = auth()->user()->role;

        $statuses = ['returned', 'rejected'];

        $query = BorrowRequest::with([
            'item', 'current_room', 'current_room.building',
            'destination_room', 'destination_room.building',
            'requestor', 'item.inventory_parent_item', 'handler'
        ]);

        if ($role === 'its') {
            $query->where('item_type', 'PC');
        }

        if ($role === 'ppfo') {
            $query->where('item_type', '<>', 'PC');
        }

        $query->whereNotIn('status', $statuses)
            ->orderBy('created_at', 'desc');

        return $query->get();
    }

    function getRequests()
    {
        $role = auth()->user()->role;

        $query = BorrowRequest::with(['item', 'current_room', 'destination_room', 'requestor', 'item.inventory_parent_item', 'handler']);

        if ($role === 'department') {
            $query->where('requestor_user_id', auth()->user()->id);
        }

        if ($role === 'its') {
            $query->where('item_type', 'PC');
        }

        if ($role === 'ppfo') {
            $query->where('item_type', '<>', 'PC');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    function requestBorrow(Request $request)
    {
        $userId = auth()->user()->id;

        $request->validate([
            'destination_room_id' => 'required',
            'item_id' => 'required',
            'details' => 'required'
        ], [
            'destination_room_id.required' => 'Destination room is required'
        ]);

        return DB::transaction(function () use ($request, $userId) {

            $item = InventoryItem::with(['inventory_parent_item', 'room'])->find($request->item_id);

            $request['requestor_user_id'] = $userId;
            $request['current_room_id'] = $item->room_id;
            $request['item_type'] = $item->inventory_parent_item->item_type;

            $borrow_request = BorrowRequest::create($request->all());

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
            $room_current_name = $item->room->name;
            $item_to_transfer_name = $item->inventory_parent_item->name;

            foreach ($notified_users as $notified_user) {
                array_push($notifications_to_insert, [
                    'user_id' => $notified_user->id,
                    'message' => "<b>$requestor_name</b> has requested to borrow a <b>$item_to_transfer_name</b> from $room_current_name 
                                for <b>$room_destination_name</b>",
                    "created_at" =>  \Carbon\Carbon::now('UTC'),
                    "updated_at" => \Carbon\Carbon::now('UTC'),
                ]);
            }


            Notification::insert($notifications_to_insert);

            return $borrow_request;
        });
    }

    function setInProgress($id) {
        $handler_id = auth()->user()->id;

        $borrowRequest = BorrowRequest::with(['item', 'item.inventory_parent_item'])->find($id);

        $borrowRequest->status = 'in progress';
        $borrowRequest->handler_user_id = $handler_id;

        $borrowRequest->save();

        Notification::create([
            'user_id' => $borrowRequest->requestor_user_id,
            'message' => "Your request to borrow a <b>" . $borrowRequest->item->inventory_parent_item->name . "</b> is now in progress"
        ]);

        return $borrowRequest;
    }

    function reject($id) {
        $handler_id = auth()->user()->id;

        $borrowRequest = BorrowRequest::with(['item', 'item.inventory_parent_item'])->find($id);

        $borrowRequest->status = 'rejected';
        $borrowRequest->handler_user_id = $handler_id;

        $borrowRequest->save();

        Notification::create([
            'user_id' => $borrowRequest->requestor_user_id,
            'message' => "Your request to borrow a <b>" . $borrowRequest->item->inventory_parent_item->name . "</b> has been rejected"
        ]);

        return $borrowRequest;
    }

    function setAsBorrowed($id) {
        $handler_id = auth()->user()->id;

        $borrowRequest = BorrowRequest::with(['item', 'item.inventory_parent_item'])->find($id);

        $borrowRequest->status = 'borrowed';
        $borrowRequest->handler_user_id = $handler_id;

        $borrowRequest->save();

        Notification::create([
            'user_id' => $borrowRequest->requestor_user_id,
            'message' => "Your request to borrow a <b>" . $borrowRequest->item->inventory_parent_item->name . "</b> has been completed"
        ]);

        return $borrowRequest;
    }

    function setAsReturned($id) {
        $handler_id = auth()->user()->id;

        $borrowRequest = BorrowRequest::with(['item', 'item.inventory_parent_item'])->find($id);

        $borrowRequest->status = 'returned';
        $borrowRequest->handler_user_id = $handler_id;

        $borrowRequest->save();

        Notification::create([
            'user_id' => $borrowRequest->requestor_user_id,
            'message' => "Your borrowed <b>" . $borrowRequest->item->inventory_parent_item->name . "</b> has been returned"
        ]);

        return $borrowRequest;
    }
}
