<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class POController extends Controller
{
    public function index()
    {
        return PurchaseOrder::orderBy('created_at', 'desc')->get();
    }
}
