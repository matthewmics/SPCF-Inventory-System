<?php

namespace App\Http\Controllers;

use App\Models\FileStorage;
use Illuminate\Http\Request;

class FileStorageController extends Controller
{
    public function show(Request $request, $id){
        return FileStorage::find($id);
    }
}
