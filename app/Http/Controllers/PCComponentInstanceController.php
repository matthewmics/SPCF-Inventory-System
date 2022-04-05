<?php

namespace App\Http\Controllers;

use App\Models\PCComponentInstance;
use Illuminate\Http\Request;

class PCComponentInstanceController extends Controller
{

    public function index()
    {
        return PCComponentInstance::get();
    }

    public function availableItems()
    {
        return PCComponentInstance::with('component')->whereNull('inventory_item_id')->where('is_disposed', false)->get();
    }

    public function setItem(Request $request, $id)
    {
        $instance = PCComponentInstance::find($id);

        $instance->inventory_item_id = $request["inventory_item_id"];

        $instance->save();

        $instance->load(['component']);

        return $instance;
    }

    public function store(Request $request)
    {
        $request->validate([
            'brand' => 'required',
            'serial_number' => 'required',
            'pc_component_id' => 'required',
        ], [
            'required.pc_component_id' => "PC Component is required"
        ]);

        return PCComponentInstance::create($request->all());
    }


    public function show($id)
    {
        return PCComponentInstance::find($id);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'brand' => 'required',
            'serial_number' => 'required',
            'pc_component_id' => 'required',
        ]);

        $item = PCComponentInstance::find($id);

        $item->update($request->all());

        return $item;
    }

    public function destroy($id)
    {
        $item = PCComponentInstance::find($id);

        $item->delete();

        return response()->noContent();
    }
}
