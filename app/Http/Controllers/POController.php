<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class POController extends Controller
{
    public function index()
    {
        return PurchaseOrder::with('file_storage')->get();
    }
}
