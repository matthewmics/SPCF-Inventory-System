<?php

namespace App\Http\Controllers;

use App\Models\PCComponent;
use Illuminate\Http\Request;

class PCComponentController extends Controller
{

    public function index()
    {
        return PCComponent::with(['instances_id_only'])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        return PCComponent::create($request->all());
    }

    public function show($id)
    {
        return PCComponent::with(['instances'])->find($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $item = PCComponent::find($id);

        $item->update($request->all());

        return $item;
    }

    public function destroy($id)
    {
        $item = PCComponent::find($id);

        $item->delete();

        return response()->noContent();
    }
}
