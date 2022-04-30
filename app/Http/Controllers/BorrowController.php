<?php

namespace App\Http\Controllers;

use App\Models\BorrowRequest2;
use Illuminate\Http\Request;
use App\Http\Controllers\ActivityLogController;
use App\Models\Room;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\DepartmentBuilding;
use App\Models\InventoryItem;
use App\Models\FileStorage;

class BorrowController extends Controller
{
    public function index()
    {
        $query = BorrowRequest2::with(['items', 'items.inventory_parent_item', 'destination', 'worker']);

        $role = auth()->user()->role;

        if ($role === 'department') {
            $query->where('requested_by', auth()->user()->id);
        }

        if ($role === 'its') {
            $query->where('worker', auth()->user()->id);
        }

        if ($role === 'ppfo') {
            $query->where('worker', auth()->user()->id);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function show($id)
    {
        return BorrowRequest2::with(['destination'])->find($id);
    }

    public function processableRequests(Request $request)
    {
        $role = auth()->user()->role;
        $user_id = auth()->user()->id;

        $query = BorrowRequest2::with(['items', 'items.inventory_parent_item', 'destination', 'worker']);

        if ($role === 'department') {
            $query->where(function ($q) {
                $q->where('status', 'pending')
                    ->orWhere('status', 'borrowed');
            });
            $query->whereIn(
                'destination_room',
                Room::select('id')->whereIn(
                    'building_id',
                    DepartmentBuilding::select('building_id')->where('user_id', $user_id)
                )
            );
        } else {
            $query->where('status', 'pending');
        }

        return $query->get();
    }

    public function createRequest(Request $request)
    {
        $userId = auth()->user()->id;

        $request->validate([
            'destination_room' => 'required',
            'purpose' => 'required',
            'borrow_details' => 'required',
            'from' => 'required',
            'to' => 'required',
            'borrower' => 'required',
            'department' => 'required'
        ], [
            'destination_room.required' => 'Borrow for is required',
            'from.required' => 'Borrow date is required',
            'to.required' => 'Return date is required'
        ]);

        return DB::transaction(function () use ($request, $userId) {

            $request['requested_by'] = $userId;

            $borrow_req = BorrowRequest2::create($request->all());

            $room_destination = Room::find($request['destination_room']);
            $notifications_to_insert = [];
            $borrower = $request['borrower'];
            $room_destination_name = $room_destination->name;

            $notified_users = null;
            $notified_users = User::select('id')->where('role', 'admin')
                ->orWhere('role', 'its')
                ->orWhere('role', 'ppfo')
                ->get();

            $activity_text = "<b>$borrower</b> has requested to borrow items for <b>$room_destination_name</b>";

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

            return $borrow_req;
        });
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_details' => 'required'
        ]);

        return DB::transaction(function () use ($request, $id) {
            $handler_id = auth()->user()->id;

            $borrowRequest = BorrowRequest2::find($id);

            $borrowRequest->status = 'rejected';
            $borrowRequest->worker = $handler_id;
            $borrowRequest->rejection_details = $request['rejection_details'];
            $borrowRequest->date_processed = \Carbon\Carbon::now('UTC');

            $borrowRequest->save();

            $current_user_name = auth()->user()->name;

            $activity_text = "<b>$current_user_name</b> rejected <b>$borrowRequest->borrower</b>'s request to borrow <b>$borrowRequest->borrow_details</b>";

            Notification::create([
                'user_id' => $borrowRequest->requested_by,
                'message' => $activity_text
            ]);

            ActivityLogController::store(auth()->user(), $activity_text);

            return $borrowRequest;
        });
    }

    public function borrow(Request $request, $id)
    {
        $request->validate([
            'items' => 'required'
        ]);

        return DB::transaction(function () use ($request, $id) {

            $items = $request['items'];

            $handler_id = auth()->user()->id;

            $borrowRequest = BorrowRequest2::with(['destination'])->find($id);

            $borrowRequest->status = 'borrowed';
            $borrowRequest->worker = $handler_id;
            $borrowRequest->date_processed = \Carbon\Carbon::now('UTC');

            $borrowRequest->save();

            InventoryItem::whereIn('id', $items)->update([
                'borrow_request_id' => $borrowRequest->id
            ]);

            $current_user_name = auth()->user()->name;

            $activity_text = "<b>$current_user_name</b> processed <b>$borrowRequest->borrower</b>'s request to borrow <b>$borrowRequest->borrow_details</b>";

            ActivityLogController::store(auth()->user(), $activity_text);

            Notification::create([
                'user_id' => $borrowRequest->requested_by,
                'message' => $activity_text
            ]);

            return $borrowRequest;
        });
    }

    public function return(Request $request, $id)
    {

        $request->validate([
            'borrower_note' => 'required'
        ], [
            'borrower_note.required' => 'Note is required'
        ]);

        return DB::transaction(function () use ($request, $id) {
            $handler_id = auth()->user()->id;

            $borrowRequest = BorrowRequest2::with(['items', 'items.inventory_parent_item', 'destination', 'worker'])->find($id);
            $borrowRequest->status = 'returned';
            $borrowRequest->worker = $handler_id;

            $borrowRequest->borrow_data = json_encode($borrowRequest);
            $borrowRequest->date_returned = \Carbon\Carbon::now('UTC');

            if ($request->hasFile('file')) {
                $base64file = base64_encode(file_get_contents($request->file('file')->path()));

                $file_storage = FileStorage::create([
                    'name' => uniqid() . "_" . $request->file('file')->getClientOriginalName(),
                    'base64' => $base64file
                ]);

                $borrowRequest->borrower_file = $file_storage->id;
            }

            $borrowRequest->borrower_note = $request['borrower_note'];
            $borrowRequest->save();

            $items = array_map(function ($item) {
                return $item['id'];
            }, $borrowRequest->items->toArray());

            InventoryItem::whereIn('id', $items)->update([
                'borrow_request_id' => null
            ]);

            $activity_text = "<b>$borrowRequest->borrower</b> has returned <b>$borrowRequest->borrow_details</b>";

            ActivityLogController::store(auth()->user(), $activity_text);

            Notification::create([
                'user_id' => $borrowRequest->worker,
                'message' => $activity_text
            ]);

            return $borrowRequest;
        });
    }
}
