<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = \App\Models\Collection::where('status', 'active')->get();
        return response_success('Collections retrieved successfully.', $collections);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:collections,name',
            'status' => 'nullable|in:active,inactive'
        ]);

        $collection = \App\Models\Collection::create($request->all());
        return response_success('Collection created successfully.', $collection, 201);
    }

    public function show($id)
    {
        $perPage = request()->query('per_page', 10);
        $collection = \App\Models\Collection::findOrFail($id);
        $foods = $collection->foods()->paginate($perPage);
        
        $data = [
            'collection' => $collection,
            'foods' => $foods
        ];

        return response_success('Collection details retrieved successfully.', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:collections,name,'.$id,
            'status' => 'nullable|in:active,inactive'
        ]);

        $collection = \App\Models\Collection::findOrFail($id);
        $collection->update($request->all());
        return response_success('Collection updated successfully.', $collection);
    }

    public function destroy($id)
    {
        $collection = \App\Models\Collection::findOrFail($id);
        $collection->delete();
        return response_success('Collection deleted successfully.');
    }

    public function attachFoods(Request $request, $id)
    {
        $request->validate([
            'food_ids' => 'required|array',
            'food_ids.*' => 'exists:foods,id'
        ]);

        $collection = \App\Models\Collection::findOrFail($id);
        $collection->foods()->syncWithoutDetaching($request->food_ids);

        return response_success('Foods added to collection successfully.');
    }

    public function detachFoods(Request $request, $id)
    {
        $request->validate([
            'food_ids' => 'required|array',
            'food_ids.*' => 'exists:foods,id'
        ]);

        $collection = \App\Models\Collection::findOrFail($id);
        $collection->foods()->detach($request->food_ids);

        return response_success('Foods removed from collection successfully.');
    }
}
