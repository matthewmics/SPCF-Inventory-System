<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FileStorage;

class NoteController extends Controller
{

    public function show(Request $request)
    {
        $name = $request->input('name');
        $id = $request->input('id');

        if ($name === 'transfer') {
            return Note::where('transfer_id', $id)->orderBy('created_at', 'desc')->get();
        } else if ($name === 'repair') {
            return Note::where('repair_id', $id)->orderBy('created_at', 'desc')->get();
        } else if ($name === 'borrow') {
            return Note::where('borrow_id', $id)->orderBy('created_at', 'desc')->get();
        }
    }

    public function create(Request $request)
    {
        $request->validate([
            'message' => 'required'
        ], [
            'message.required' => 'Note is required'
        ]);

        return DB::transaction(function () use ($request) {
            if ($request->hasFile('file')) {
                $base64file = base64_encode(file_get_contents($request->file('file')->path()));

                $file_storage = FileStorage::create([
                    'name' => uniqid() . "_" . $request->file('file')->getClientOriginalName(),
                    'base64' => $base64file
                ]);

                $request['file_storage_id'] = $file_storage->id;
            }
            return Note::create($request->all());
        });
    }
}
