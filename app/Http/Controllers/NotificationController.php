<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{

    public function index(Request $request)
    {
        return Notification::where('user_id', auth()->user()->id)
            ->where('read', 0)
            ->get();
    }

    public function read(Request $request, $id)
    {
        $notification = Notification::find($id);
        $notification->read = true;
        $notification->save();

        return $notification;
    }

    public function readAll(Request $request)
    {
        return Notification::where('user_id', auth()->user()->id)->update(['read' => true]);
    }
}
