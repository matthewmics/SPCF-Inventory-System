<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\JobOrder;
use App\Models\Notification;
use App\Models\RepairRequest;
use App\Models\TransferRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ActivityLogController;

class ITSPPFOController extends Controller
{
    public function listTransferRequests(Request $request)
    {
        $role = auth()->user()->role;

        $statuses = ['completed', 'rejected', 'disposed'];

        if ($role === 'ppfo') {

            return TransferRequest::with(['item', 'current_room', 'destination_room', 'requestor', 'item.inventory_parent_item'])
                ->whereNotIn('item_type', ['PC'])
                ->whereNotIn('status', $statuses)
                ->orderBy('id')
                ->get();
        } else if ($role === 'its') {

            return TransferRequest::with(['item', 'current_room', 'destination_room', 'requestor', 'item.inventory_parent_item'])
                ->where('item_type', 'PC')
                ->whereNotIn('status', $statuses)
                ->orderBy('id')
                ->get();
        }

        return TransferRequest::with(['item', 'current_room', 'destination_room', 'requestor', 'item.inventory_parent_item'])
            ->whereNotIn('status', $statuses)
            ->orderBy('id')
            ->get();
    }

    public function listRepairRequests(Request $request)
    {
        $role = auth()->user()->role;

        $statuses = ['completed', 'rejected', 'disposed', 'replaced', 'PO created', 'job order created', 'repaired'];

        if ($role === 'ppfo') {

            return RepairRequest::with(['item', 'requestor', 'item.inventory_parent_item'])
                ->whereNotIn('item_type', ['PC'])
                ->whereNotIn('status', $statuses)
                ->orderBy('id')
                ->get();
        } else if ($role === 'its') {

            return RepairRequest::with(['item', 'requestor', 'item.inventory_parent_item'])
                ->where('item_type', 'PC')
                ->whereNotIn('status', $statuses)
                ->orderBy('id')
                ->get();
        }

        return RepairRequest::with(['item', 'requestor', 'item.inventory_parent_item'])
            ->whereNotIn('status', $statuses)
            ->orderBy('id')
            ->get();
    }

    public function rejectRequest(Request $request)
    {
        $handler_id = auth()->user()->id;

        $request->validate([
            "rejection_details" => "required"
        ]);

        $request_id = $request['transfer_request_id'];

        $requestTransfer = TransferRequest::with(['item', 'item.inventory_parent_item', 'requestor'])->find($request_id);

        $requestTransfer->status = 'rejected';
        $requestTransfer->handler_user_id = $handler_id;
        $requestTransfer->rejection_details = $request['rejection_details'];

        $requestTransfer->save();

        $item_name = $requestTransfer->item->inventory_parent_item->name;
        $requestor_name = $requestTransfer->requestor->name;
        $current_user_name = auth()->user()->name;
        $activity_text = "<b>$current_user_name</b> set <b>$requestor_name's</b> request to transfer <b>$item_name</b> to rejected";
        ActivityLogController::store(auth()->user(), $activity_text);

        Notification::create([
            'user_id' => $requestTransfer->requestor_user_id,
            'message' => "Your request to transfer a <b>" . $requestTransfer->item->inventory_parent_item->name . "</b> has been rejected"
        ]);

        return $request;
    }

    public function workOnRequest(Request $request)
    {
        $handler_id = auth()->user()->id;

        $request_id = $request['transfer_request_id'];

        $request = TransferRequest::with(['item', 'item.inventory_parent_item', 'requestor'])->find($request_id);

        $request->status = 'in progress';
        $request->handler_user_id = $handler_id;

        $request->save();

        $item_name = $request->item->inventory_parent_item->name;
        $requestor_name = $request->requestor->name;
        $current_user_name = auth()->user()->name;
        $activity_text = "<b>$current_user_name</b> set <b>$requestor_name's</b> request to transfer <b>$item_name</b> to in progress";
        ActivityLogController::store(auth()->user(), $activity_text);

        Notification::create([
            'user_id' => $request->requestor_user_id,
            'message' => "Your request to transfer a <b>" . $request->item->inventory_parent_item->name . "</b> is now in progress"
        ]);

        return $request;
    }

    // -=----=----=----=----=----=----=----=----=----=----=----=----=----=----=----=----=----=---

    public function rejectRepairRequest(Request $request)
    {
        $handler_id = auth()->user()->id;

        $request->validate([
            "rejection_details" => "required",
            "repair_request_id" => "required"
        ]);

        $request_id = $request['repair_request_id'];

        $requestTransfer = RepairRequest::with(['item', 'item.inventory_parent_item', 'requestor'])->find($request_id);

        $requestTransfer->status = 'rejected';
        $requestTransfer->handler_user_id = $handler_id;
        $requestTransfer->rejection_details = $request['rejection_details'];

        $requestTransfer->save();

        $item_name = $requestTransfer->item->inventory_parent_item->name;
        $requestor_name = $requestTransfer->requestor->name;
        $current_user_name = auth()->user()->name;
        $activity_text = "<b>$current_user_name</b> set <b>$requestor_name's</b> request to repair <b>$item_name</b> to rejected";
        ActivityLogController::store(auth()->user(), $activity_text);

        Notification::create([
            'user_id' => $requestTransfer->requestor_user_id,
            'message' => "Your request to have a <b>" . $requestTransfer->item->inventory_parent_item->name . "</b> repaired has been rejected"
        ]);

        return $request;
    }

    public function disposeRepairRequest(Request $request)
    {
        $handler_id = auth()->user()->id;

        $request->validate([
            "item_id" => "required",
            "repair_request_id" => "required"
        ]);

        // dispose item
        $item = InventoryItem::find($request['item_id']);

        $item_name = $item->inventory_parent_item->name;

        $item->is_disposed = true;
        // $item->deleted_at = Carbon::now('UTC');
        $item->room_id = null;
        $item->save();

        // set repair request status        
        $repairRequest = RepairRequest::with(['item', 'item.inventory_parent_item', 'requestor'])->find($request['repair_request_id']);
        $repairRequest->status = 'disposed';
        $repairRequest->handler_user_id = $handler_id;
        $repairRequest->save();

        
        $item_name = $repairRequest->item->inventory_parent_item->name;
        $requestor_name = $repairRequest->requestor->name;
        $current_user_name = auth()->user()->name;
        $activity_text = "<b>$current_user_name</b> set <b>$requestor_name's</b> request to repair <b>$item_name</b> to disposed";
        ActivityLogController::store(auth()->user(), $activity_text);


        // notify 
        Notification::create([
            'user_id' => $repairRequest->requestor_user_id,
            'message' => "Your request to have a <b>" . $item_name . "</b> repaired has been marked as diposed due to the item being unfixable"
        ]);

        return $repairRequest;
    }

    public function createJobOrder(Request $request)
    {

        $handler_id = auth()->user()->id;

        $request->validate([
            "repair_request_id" => "required"
        ]);

        $jobOrder = JobOrder::create([
            'handler_user_id' => $handler_id,
            'repair_request_id' => $request['repair_request_id']
        ]);

        $repairRequest = RepairRequest::with(['item', 'item.inventory_parent_item', 'requestor'])->find($request['repair_request_id']);

        // set status
        $repairRequest->status = 'job order created';
        $repairRequest->handler_user_id = $handler_id;
        $repairRequest->save();

        $item_name = $repairRequest->item->inventory_parent_item->name;
        $requestor_name = $repairRequest->requestor->name;
        $current_user_name = auth()->user()->name;
        $activity_text = "<b>$current_user_name</b> set <b>$requestor_name's</b> request to repair <b>$item_name</b> to job order created";
        ActivityLogController::store(auth()->user(), $activity_text);

        // notify 
        Notification::create([
            'user_id' => $repairRequest->requestor_user_id,
            'message' => "Job Order has been created for your request to have a <b>" . $repairRequest->item->inventory_parent_item->name .
                "</b> repaired"
        ]);


        return $jobOrder;
    }



    public function finishRequest(Request $request)
    {
        $handler_id = auth()->user()->id;

        $request_id = $request['transfer_request_id'];

        $request = TransferRequest::with(['item', 'item.inventory_parent_item', 'requestor'])->find($request_id);

        $item = InventoryItem::find($request->item_id);

        $item->room_id = $request->destination_room_id;

        $item->save();

        $request->status = 'completed';
        $request->handler_user_id = $handler_id;

        $request->save();

        $item_name = $request->item->inventory_parent_item->name;
        $requestor_name = $request->requestor->name;
        $current_user_name = auth()->user()->name;
        $activity_text = "<b>$current_user_name</b> set <b>$requestor_name's</b> request to transfer <b>$item_name</b> to completed";
        ActivityLogController::store(auth()->user(), $activity_text);

        Notification::create([
            'user_id' => $request->requestor_user_id,
            'message' => "Your request to transfer a <b>" . $request->item->inventory_parent_item->name . "</b> is now completed"
        ]);

        return $request;
    }
}
