<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public static function store($user, $activity)
    {
        ActivityLog::create([
            'user_name' => $user->name,
            'activity' => $activity,
            'user_id' => $user->id
        ]);
    }
}
