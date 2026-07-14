<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FoodRequest;
use App\Services\FoodService;
use App\Models\Food;

class FoodController extends Controller
{
    public function __construct(protected FoodService $foodService)
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);

        // Protect resource routes, leaving index and show public
        $this->authorizeResource(Food::class, 'food', [
            'except' => ['index', 'show']
        ]);
    }

    public function index()
    {
        $foods = $this->foodService->getAll();
        return response_success('Foods retrieved successfully.', $foods);
    }

    public function store(FoodRequest $request)
    {
        $food = $this->foodService->storeFood($request);
        return response_success('Food created successfully.', $food, 201);
    }

    public function show($id)
    {
        // Eager load the category and variants relationships automatically via the service
        $food = $this->foodService->getById($id, ['category', 'variants']);
        return response_success('Food details retrieved successfully.', $food);
    }

    public function update(FoodRequest $request, $id)
    {
        $food = $this->foodService->updateFood($request, $id);
        return response_success('Food updated successfully.', $food);
    }

    public function destroy($id)
    {
        $this->foodService->deleteFood($id);
        return response_success('Food deleted successfully.');
    }
}
