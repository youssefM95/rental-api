<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;

class PropertyController extends Controller
{
    public function index()
    {
        return Property::all();
    }
    public function exposedProperties($id)  {
        return Property::where('owner_id' ,'!=' ,$id)->get();
    }
    public function store(Request $request)
    {
        $property = Property::create($request->all());
        return response()->json($property, 201);
    }

    public function show($id)
    {
        return Property::find($id);
    }

    public function update(Request $request, $id)
    {
        $property = Property::find($id);
        $property->update($request->all());
        return response()->json($property, 200);
    }

    public function destroy($id)
    {
        $property = Property::find($id);
        $property->delete();
        return response()->json(null, 204);
    }
}
