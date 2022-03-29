<?php

namespace App\Http\Controllers;

use App\Models\FileStorage;
use App\Models\InventoryItem;
use App\Models\Notification;
use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Http\Request;

class RepairRequestController extends Controller
{
    public function listRepairRequests(Request $request)
    {
        $role = auth()->user()->role;

        if ($role === 'department') {
            return RepairRequest::with(['item', 'requestor', 'item.inventory_parent_item', 'handler'])
                ->orderBy('created_at', 'desc')
                ->where('requestor_user_id', auth()->user()->id)
                ->get();
        } else if ($role === 'its') {
            return RepairRequest::with(['item', 'requestor', 'item.inventory_parent_item', 'handler'])
                ->where('item_type', 'PC')
                ->orderBy('created_at', 'desc')
                ->get();
        } else if ($role === 'ppfo') {
            return RepairRequest::with(['item', 'requestor', 'item.inventory_parent_item', 'handler'])
                ->where('item_type', '<>', 'PC')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return RepairRequest::with(['item', 'requestor', 'item.inventory_parent_item', 'handler'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function requestRepair(Request $request)
    {
        $userId = auth()->user()->id;

        $request->validate([
            'item_id' => 'required',
            'details' => 'required',
            'file' => 'required'
        ], [
            'file.required' => 'File attachment is required'
        ]);

        $base64file = base64_encode(file_get_contents($request->file('file')->path()));

        $file_storage = FileStorage::create([
            'name' => uniqid() . "_" . $request->file('file')->getClientOriginalName(),
            'base64' => $base64file
        ]);

        $item = InventoryItem::with('inventory_parent_item')->find($request->item_id);

        $request['requestor_user_id'] = $userId;
        $request['file_storage_id'] = $file_storage->id;
        $request['item_type'] = $item->inventory_parent_item->item_type;

        $repair_request = RepairRequest::create($request->all());


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
        $item_to_repair_name = $item->inventory_parent_item->name;

        foreach ($notified_users as $notified_user) {
            array_push($notifications_to_insert, [
                'user_id' => $notified_user->id,
                'message' => "<b>$requestor_name</b> has requested <b>$item_to_repair_name</b> to be repaired",
                "created_at" =>  \Carbon\Carbon::now('UTC'),
                "updated_at" => \Carbon\Carbon::now('UTC'),
            ]);
        }

        Notification::insert($notifications_to_insert);

        return $repair_request;
    }
}
