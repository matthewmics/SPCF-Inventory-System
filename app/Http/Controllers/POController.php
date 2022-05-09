<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\PurchaseItemRequest;
use Illuminate\Support\Facades\DB;

class POController extends Controller
{
    public function index()
    {
        return PurchaseOrder::orderBy('created_at', 'desc')->get();
    }

    public function complete(Request $request, $id)
    {

        return DB::transaction(function () use ($request, $id) {


            $po = PurchaseOrder::find($id);

            if ($po->job_order_id) {

                $jo = JobOrder::with(['repair_request', 'repair_request.requestor'])->find($po->job_order_id);

                Notification::create([
                    'user_id' => $jo->repair_request->requestor_user_id,
                    'message' => '<b>' . $jo->repair_request->requestor->name . '</b>' . '\'s' . 'request to purchase '
                        . '<b>' . $po->item_name . '</b>'
                        . ' has been successfully bought'
                ]);
            }

            if ($po->purchase_item_request_id) {

                $pir = PurchaseItemRequest::with(['requestor_object'])
                    ->find($po->purchase_item_request_id);

                Notification::create([
                    'user_id' => $pir->requested_by,
                    'message' => '<b>' . $pir->requestor . '</b>' . '\'s' . 'request to purchase '
                        . '<b>' . $po->item_name . '</b>'
                        . ' has been successfully bought'
                ]);
            }

            $po->is_completed = true;
            $po->save();

            return $po;
        });
    }
}
