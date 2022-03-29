<?php

namespace App\Http\Controllers;

use App\Models\FileStorage;
use App\Models\InventoryItem;
use App\Models\JobOrder;
use App\Models\Notification;
use App\Models\PurchaseOrder;
use App\Models\RepairRequest;
use Illuminate\Http\Request;

class JobOrderController extends Controller
{
    public function listPendingJobOrders(Request $request)
    {
        return JobOrder::with(['handler', 'repair_request', 'repair_request.item', 'repair_request.item.inventory_parent_item'])->where('status', 'pending')->get();
    }

    public function markAsRepaired(Request $request, $id)
    {
        $handler_id = auth()->user()->id;

        $jobOrder = JobOrder::find($id);
        $jobOrder->status = 'repaired';
        $jobOrder->processor_user_id = $handler_id;
        $jobOrder->save();

        $repairRequest = RepairRequest::with(['item', 'item.inventory_parent_item'])->find($jobOrder->repair_request_id);
        $repairRequest->status = 'repaired';
        $repairRequest->save();

        $item = $repairRequest->item;

        Notification::create([
            'user_id' => $repairRequest->requestor_user_id,
            'message' => "<b>" . $item->inventory_parent_item->name . "</b> has now been fixed"
        ]);

        return $jobOrder;
    }

    public function replaceItem(Request $request, $id)
    {

        $request->validate([
            'replacement_item_id' => 'required'
        ]);

        $handler_id = auth()->user()->id;

        $jobOrder = JobOrder::find($id);
        $jobOrder->status = 'replaced';
        $jobOrder->processor_user_id = $handler_id;
        $jobOrder->save();

        $repairRequest = RepairRequest::with(['item', 'item.inventory_parent_item'])->find($jobOrder->repair_request_id);
        $repairRequest->status = 'replaced';
        $repairRequest->save();


        $item = InventoryItem::find($repairRequest->item->id);

        $room_id = $item->room_id;

        $replacementItem = InventoryItem::find($request['replacement_item_id']);

        $replacementItem->room_id = $room_id;

        $item->room_id = null;
        $item->is_disposed = true;

        $item->save();

        $replacementItem->save();

        Notification::create([
            'user_id' => $repairRequest->requestor_user_id,
            'message' => "<b>" . $item->inventory_parent_item->name . "</b> has been replaced"
        ]);

        return $jobOrder;
    }

    public function createPO(Request $request, $id)
    {

        $request->validate([
            'file' => 'required'
        ], [
            'file.required' => 'File attachment is required'
        ]);

        $handler_id = auth()->user()->id;

        $jobOrder = JobOrder::find($id);
        $jobOrder->status = 'PO created';
        $jobOrder->processor_user_id = $handler_id;
        $jobOrder->save();

        $repairRequest = RepairRequest::with(['item', 'item.inventory_parent_item', 'item.room'])->find($jobOrder->repair_request_id);
        $repairRequest->status = 'PO created';
        $repairRequest->save();

        $item = InventoryItem::find($repairRequest->item->id);

        $item_name = $item->inventory_parent_item->name;
        $room_name = $item->room->name;

        $base64file = base64_encode(file_get_contents($request->file('file')->path()));

        $file_storage = FileStorage::create([
            'name' => uniqid() . "_" . $request->file('file')->getClientOriginalName(),
            'base64' => $base64file
        ]);

        PurchaseOrder::create([
            'file_storage_id' => $file_storage->id,
            'job_order_id' => $jobOrder->id,
            'room_name' => $room_name,
            'item_name' => $item_name
        ]);

        $item->room_id = null;
        $item->is_disposed = true;

        $item->save();



        Notification::create([
            'user_id' => $repairRequest->requestor_user_id,
            'message' => "Purchase Order created for <b>" . $item_name . "</b>"
        ]);


        return $jobOrder;
    }
}
