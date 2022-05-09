<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Building;
use Illuminate\Support\Facades\Log;
use App\Models\TransferRequest;
use App\Models\RepairRequest;
use App\Models\BorrowRequest2;
use App\Models\PurchaseItemRequest;
use Carbon\Carbon;

class ReportController extends Controller
{

    public function generateReport($room_items, $header_data)
    {
        $table_headers = ['EQUIPMENT', 'BRAND', 'QTY', 'STATUS', '', '', ''];
        $table_sub_headers = ['', '', '', 'W', 'N', 'R', 'B', ''];
        $legends = ['W: Working', 'N: Not Working', 'R: Repairing', 'B: Borrowed'];

        $item_map = [];

        $result = [];

        foreach ($room_items as $item) {
            $key = $item->inventory_parent_item->name;
            $key2 = $item->brand;

            if (array_key_exists($key, $item_map)) {

                if (array_key_exists($key2, $item_map[$key])) {

                    array_push($item_map[$key][$key2], $item);
                } else {
                    $item_map[$key][$key2] = [$item];
                }
            } else {
                $item_map[$key] = [$key2 => [$item]];
            }
        }

        ksort($item_map);

        // $fileName = uniqid();
        // $fileUrl = "../storage/app/$fileName.csv";

        // $file = fopen($fileUrl, 'w');

        // fputcsv($file, $header_data);
        // fputcsv($file, ['']);
        // fputcsv($file, $table_headers);
        // fputcsv($file, $table_sub_headers);

        foreach ($item_map as $key => $brands) {

            foreach ($brands as $brand => $items) {
                $total = 0;
                $working = 0;
                $notWorking = 0;
                $repairing = 0;
                $borrowing = 0;

                foreach ($items as $item) {
                    $total = $total + 1;

                    $b_status = $item->borrow_status;
                    $r_status = $item->repair_status;

                    if ($b_status === "borrowed") {
                        $borrowing = $borrowing + 1;
                    }

                    if (in_array($r_status, ['pending', 'job order created'])) {

                        $notWorking = $notWorking + 1;


                        if ($r_status === "job order created") {
                            $repairing = $repairing + 1;
                        }
                    } else {
                        $working = $working + 1;
                    }
                }

                // fputcsv($file, [$key, $brand, $total, $working, $notWorking, $repairing, $borrowing]);
                array_push($result, [$key, $brand, $total, $working, $notWorking, $repairing, $borrowing]);
            }
        }


        // fputcsv($file, ['']);
        // fputcsv($file, $legends);

        // fclose($file);

        return $result;
        // return response()->download($fileUrl)->deleteFileAfterSend(true);
    }

    public function inventoryReport(Request $request)
    {
        $items_to_generate = $request['items_to_generate'];

        $items_query = InventoryItem::with(['inventory_parent_item'])
            ->where('is_disposed', false);

        if (count($items_to_generate)) {
            $items_query->whereIn('inventory_parent_item_id', $items_to_generate);
        }

        $items = $items_query->get();

        return $this->generateReport($items, []);
    }

    public function roomReport(Request $request)
    {
        $room_id = $request['room_id'];
        $date = $request['date'];
        $items_to_generate = $request['items_to_generate'];

        $room = Room::find($room_id);

        $room_items_query = InventoryItem::with(['inventory_parent_item'])
            ->where('room_id', $room_id)
            ->where('is_disposed', false);

        if (count($items_to_generate)) {
            $room_items_query->whereIn('inventory_parent_item_id', $items_to_generate);
        }

        $room_items = $room_items_query->get();

        return $this->generateReport($room_items, ["Room: $room->name", "", "As Of: $date"]);
    }

    public function buildingReport(Request $request)
    {
        $building_id = $request['building_id'];
        $date = $request['date'];
        $items_to_generate = $request['items_to_generate'];

        $building = Building::find($building_id);

        $items_query = InventoryItem::with(['inventory_parent_item'])
            ->where('is_disposed', false)
            ->whereIn('room_id', Room::select('id')
                ->where('building_id', $building_id));

        if (count($items_to_generate)) {
            $items_query->whereIn('inventory_parent_item_id', $items_to_generate);
        }

        $items = $items_query->get();

        return $this->generateReport($items, ["Building: $building->name", "", "As Of: $date"]);
    }

    public function borrowReport(Request $request)
    {
        $date = $request['date'];
        $status_to_generate = $request['status_to_generate'];

        $result = [];

        // $fileName = uniqid();
        // $fileUrl = "../storage/app/$fileName.csv";

        // $file = fopen($fileUrl, 'w');

        // fputcsv($file, ['Borrow Requests', "", "As Of: $date"]);
        // fputcsv($file, ['']);
        // fputcsv($file, ['Status', 'Date', 'Borrower', 'Department', 'Requested Date', 'To Borrow', 'purpose', 'Worked On By', 'Action Date']);

        $borrows = BorrowRequest2::with(['items', 'items.inventory_parent_item', 'destination', 'worker2']);

        if (count($status_to_generate)) {
            $borrows->whereIn('status', $status_to_generate);
        } else {
            $borrows->whereIn('status', ['rejected', 'returned']);
        }

        $borrows = $borrows->get();

        foreach ($borrows as $borrow) {

            $dateLocal = Carbon::createFromFormat('Y-m-d H:i:s', $borrow['created_at'])->addHours(8)->toDateTimeString();

            if ($borrow['date_processed'])
                $actionDate = Carbon::createFromFormat('Y-m-d H:i:s', $borrow['date_processed'])->addHours(8)->toDateTimeString();
            else $actionDate = '-';

            // fputcsv($file, [
            //     $borrow['status'],
            //     $dateLocal,
            //     $borrow['borrower'],
            //     $borrow['department'],
            //     $borrow['from'] . ' to ' . $borrow['to'],
            //     $borrow['borrow_details'],
            //     $borrow['purpose'],
            //     $borrow['worker2']['name'],
            //     $actionDate
            // ]);

            array_push($result, [
                $borrow['status'],
                $dateLocal,
                $borrow['borrower'],
                $borrow['department'],
                $borrow['from'] . ' to ' . $borrow['to'],
                $borrow['borrow_details'],
                $borrow['purpose'],
                $borrow['worker2']['name'],
                $actionDate
            ]);
        }

        // fclose($file);

        // return response()->download($fileUrl)->deleteFileAfterSend(true);
        return $result;
    }

    public function repairReport(Request $request)
    {
        $date = $request['date'];
        $status_to_generate = $request['status_to_generate'];

        $result = [];

        // $fileName = uniqid();
        // $fileUrl = "../storage/app/$fileName.csv";

        // $file = fopen($fileUrl, 'w');

        // fputcsv($file, ['Repair Requests', "", "As Of: $date"]);
        // fputcsv($file, ['']);
        // fputcsv($file, ['Status', 'Date', 'Requestor', 'Item', 'Serial/Asset #', 'Details']);

        $repairs = RepairRequest::with(['item', 'requestor', 'item.inventory_parent_item', 'handler']);

        if (count($status_to_generate)) {
            $repairs->whereIn('status', $status_to_generate);
        } else {
            $repairs->whereIn('status', ['rejected', 'repaired', 'PO created', 'disposed']);
        }

        $repairs = $repairs->get();

        foreach ($repairs as $repair) {

            $dateLocal = Carbon::createFromFormat('Y-m-d H:i:s', $repair['created_at'])->addHours(8)->toDateTimeString();

            // fputcsv($file, [
            //     $repair['status'],
            //     $dateLocal,
            //     $repair['requestor']['name'],
            //     $repair['item']['inventory_parent_item']['name'],
            //     $repair['item']['serial_number'],
            //     $repair['details']
            // ]);

            array_push($result, [
                $repair['status'],
                $dateLocal,
                $repair['requestor']['name'],
                $repair['item']['inventory_parent_item']['name'],
                $repair['item']['serial_number'],
                $repair['details']
            ]);
        }

        // fclose($file);

        // return response()->download($fileUrl)->deleteFileAfterSend(true);
        return $result;
    }

    public function transferReport(Request $request)
    {
        $date = $request['date'];
        $status_to_generate = $request['status_to_generate'];

        $result = [];

        $transfers = TransferRequest::with(['item', 'current_room', 'destination_room', 'requestor', 'item.inventory_parent_item', 'handler']);

        if (count($status_to_generate)) {
            $transfers->whereIn('status', $status_to_generate);
        } else {
            $transfers->whereIn('status', ['rejected', 'completed']);
        }

        $transfers = $transfers->get();

        foreach ($transfers as $transfer) {

            $dateLocal = Carbon::createFromFormat('Y-m-d H:i:s', $transfer['created_at'])->addHours(8)->toDateTimeString();

            array_push($result, [
                $transfer['status'],
                $dateLocal,
                $transfer['requestor']['name'],
                $transfer['item']['inventory_parent_item']['name'],
                $transfer['item']['serial_number'],
                $transfer['current_room'] ? $transfer['current_room']['name'] : 'Inventory',
                $transfer['destination_room']['name']
            ]);
        }

        return $result;
    }

    public function purchaseReport(Request $request)
    {

        $status_to_generate = $request['status_to_generate'];

        $result = [];

        $pirs = PurchaseItemRequest::with(['worker_object', 'requestor_object', 'destination']);

        if (count($status_to_generate)) {
            $pirs->whereIn('status', $status_to_generate);
        } else {
            $pirs->whereIn('status', ['rejected', 'completed']);
        }

        $pirs = $pirs->get();

        foreach ($pirs as $pir) {

            $dateLocal = Carbon::createFromFormat('Y-m-d H:i:s', $pir['created_at'])->addHours(8)->toDateTimeString();

            array_push($result, [
                $pir['status'],
                $dateLocal,
                $pir['requestor'],
                $pir['to_purchase'],
                $pir['destination']['name'],
                $pir['worker_object']['name']
            ]);
        }

        return $result;
    }
}
