<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\Category;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\GlobalSearchFilter;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;

class CategoryService extends BaseService
{
    use FileUploadTrait;
    /**
     * The model class name.
     *
     * @var string
     */
    protected string $modelClass = Category::class;
    protected bool $cachePerUser = true;

    public function __construct()
    {
        // Ensure BaseService initializes the model instance
        parent::__construct();
    }


    // Define allowed filters
     protected function getAllowedFilters(): array
    {
        return [
            AllowedFilter::custom('search', new GlobalSearchFilter, 'name'),
            'name',
            AllowedFilter::exact('status'),
        ];
    }

    // Define allowed includes relationships
     protected function getAllowedIncludes(): array
     {
        return ['foods'];
     }

     // Define allowed sorts
     protected function getAllowedSorts(): array
     {
        return ['id', 'name', 'created_at'];
     }

     /**
     * Handle category creation along with image upload
     */
    public function storeCategory(Request $request): Category
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = $this->handleFileUpload($request, 'image', 'categories', forceWebp: true);
        }
        return $this->create($data);
    }

    /**
     * Handle category update along with old image deletion
     */
    public function updateCategory(Request $request, $id): Category
    {
        $data = $request->validated();
        $category = $this->getById($id);

        if ($request->hasFile('image')) {
            $this->deleteFile($category->image);
            $data['image'] = $this->handleFileUpload($request, 'image', 'categories', forceWebp: true);
        }

        return $this->update($id, $data);
    }

    /**
     * Delete category and associated image
     */
    public function deleteCategory($id): bool
    {
        $category = $this->getById($id);

        $this->deleteFile($category->image);
        return $this->delete($id);
    }
}
