<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Building;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{

    public function generateReport($room_items, $header_data)
    {
        $item_map = [];

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

        $fileName = uniqid();
        $fileUrl = "../storage/app/$fileName.csv";

        $file = fopen($fileUrl, 'w');

        fputcsv($file, $header_data);
        fputcsv($file, ['']);
        fputcsv($file, ['EQUIPMENT', 'BRAND', 'QTY', 'STATUS', '', '', '']);
        fputcsv($file, ['', '', '', 'W', 'N', 'R', 'B', '']);

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

                fputcsv($file, [$key, $brand, $total, $working, $notWorking, $repairing, $borrowing]);
            }
        }


        fputcsv($file, ['']);
        fputcsv($file, ['W: Working', 'N: Not Working', 'R: Repairing', 'B: Borrowed']);

        fclose($file);

        return response()->download($fileUrl)->deleteFileAfterSend(true);
    }

    public function roomReport(Request $request)
    {
        $room_id = $request['room_id'];
        $date = $request['date'];

        $room = Room::find($room_id);

        $room_items = InventoryItem::with(['inventory_parent_item'])->where('room_id', $room_id)
            ->where('is_disposed', false)
            ->get();

        return $this->generateReport($room_items, ["Room: $room->name", "", "As Of: $date"]);
    }

    public function buildingReport(Request $request)
    {
        $building_id = $request['building_id'];
        $date = $request['date'];

        $building = Building::find($building_id);

        $items = InventoryItem::with(['inventory_parent_item'])
            ->whereIn('room_id', Room::select('id')->where('building_id', $building_id))
            ->get();

        return $this->generateReport($items, ["Building: $building->name", "", "As Of: $date"]);
    }
}
