<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogRequest;
use App\Models\Blog;
use App\Services\BlogService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(protected BlogService $blogService)
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);

        $this->authorizeResource(Blog::class, 'blog', [
            'except' => ['index', 'show']
        ]);
    }

    public function index()
    {
        $blogs = $this->blogService->getAll(function ($query) {
            $user = auth('sanctum')->user();

            if (!$user || !$user->hasRole('admin')) {
                $query->where('status', 'active');
            }
        });

        if($blogs->isEmpty()) {
            return response_error('No blogs found.', [], 404);
        }

        return response_success('Blogs retrieved successfully.', $blogs);
    }

    public function store(BlogRequest $request)
    {
        $blog = $this->blogService->storeBlog($request);
        return response_success('Blog created successfully.', $blog, 201);
    }

    public function show(string $id)
    {
        $blog = $this->blogService->getById($id);

        if ($blog->status === 'inactive') {
            $user = auth('sanctum')->user();
            if (!$user || !$user->hasRole('admin')) {
                return response_error('Blog not found.', [], 404);
            }
        }

        return response_success('Blog details retrieved successfully.', $blog);
    }

    public function update(BlogRequest $request, string $id)
    {
        $blog = $this->blogService->updateBlog($request, $id);
        return response_success('Blog updated successfully.', $blog);
    }

    public function destroy(string $id)
    {
        $this->blogService->deleteBlog($id);
        return response_success('Blog deleted successfully.');
    }
}
