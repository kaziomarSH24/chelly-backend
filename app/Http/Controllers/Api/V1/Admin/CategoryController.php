<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use AuthorizesRequests;
    protected CategoryService $categoryService;
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->authorizeResource(Category::class, 'category', [
            'except' => ['index', 'show']
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $callback = function ($q) {
            $q->where('status', 'active');
        };
        $categories = $this->categoryService->getAll($callback);
        if ($categories->isEmpty()) {
            return response_error('No categories found.', [], 404);
        }

        return response_success('Categories retrieved successfully', $categories);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        $category = $this->categoryService->storeCategory($request);
        return response_success('Category created successfully.', $category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = $this->categoryService->getById($id);
        return response_success('Category details retrieved successfully.', $category);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, $id)
    {
        $category = $this->categoryService->updateCategory($request, $id);
        return response_success('Category updated successfully.', $category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->categoryService->deleteCategory($id);
        return response_success('Category deleted successfully.');
    }
}
