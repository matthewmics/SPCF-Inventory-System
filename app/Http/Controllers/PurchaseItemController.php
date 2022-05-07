<?php

namespace App\Http\Controllers;

use App\Models\FileStorage;
use App\Models\PurchaseItemRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\NotificationController;
use App\Models\Room;
use App\Models\Notification;

class PurchaseItemController extends Controller
{

    public function processAbles(Request $request){
        $role = auth()->user()->role;
        $user_id = auth()->user()->id;
    }

    public function create(Request $request)
    {
        $userId = auth()->user()->id;

        $request->validate([
            'destination_room' => 'required',
            'requestor' => 'required',
            'department' => 'required',
            'to_purchase' => 'required',
            'purpose' => 'required',
            'item_type' => 'required',
            'file' => 'required'
        ], [
            'destination_room.required' => 'Room is required',
            'file.required' => 'File Attachment is required'
        ]);

        return DB::transaction(function () use ($userId, $request) {

            $base64file = base64_encode(file_get_contents($request->file('file')->path()));

            $file_storage = FileStorage::create([
                'name' => uniqid() . "_" . $request->file('file')->getClientOriginalName(),
                'base64' => $base64file
            ]);

            $request['attached_file_id'] = $file_storage->id;
            $request['requested_by'] = $userId;

            $purchaseReq = PurchaseItemRequest::create($request->all());

            $room_destination = Room::find($request['destination_room']);
            $room_destination_name = $room_destination->name;
            $requestor_name = $request['requestor'];

            $notified_users = User::select('id')->where('role', 'admin');

            if ($request['item_type'] === 'PC') {
                $notified_users->orWhere('role', 'its');
            } else {
                $notified_users->orWhere('role', 'ppfo');
            }

            $notified_users = $notified_users->get();

            $notifications_to_insert = [];

            $activity_text = "<b>$requestor_name</b> has requested to purchase item for <b>$room_destination_name</b>";

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

            return $purchaseReq;
        });
    }
}
