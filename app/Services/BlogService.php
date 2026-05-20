<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\Blog;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\GlobalSearchFilter;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;

class BlogService extends BaseService
{
    use FileUploadTrait;

    protected string $modelClass = Blog::class;

    // Enable per-user caching
    protected bool $cachePerUser = true;

    protected function getAllowedFilters(): array
    {
        return [
            'title',
            'category_id',
            AllowedFilter::exact('status'),
            // Allow searching across both title and content
            AllowedFilter::custom('search', new GlobalSearchFilter(), 'title,content'),
        ];
    }

    protected function getAllowedIncludes(): array
    {
        return ['category'];
    }

    protected function getAllowedSorts(): array
    {
        return ['id', 'title', 'created_at'];
    }

    public function storeBlog(Request $request): Blog
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $this->handleFileUpload($request, 'image', 'blogs', forceWebp: true);
        }

        return $this->create($data);
    }

    public function updateBlog(Request $request, string $id): Blog
    {
        $data = $request->validated();
        $blog = $this->getById($id);

        if ($request->hasFile('image')) {
            $this->deleteFile($blog->image);
            $data['image'] = $this->handleFileUpload($request, 'image', 'blogs', forceWebp: true);
        }

        return $this->update($id, $data);
    }

    public function deleteBlog(string $id): bool
    {
        $blog = $this->getById($id);
        $this->deleteFile($blog->image);

        return $this->delete($id);
    }
}
