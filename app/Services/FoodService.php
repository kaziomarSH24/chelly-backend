<?php

namespace App\Services;

use App\Models\Food;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\GlobalSearchFilter;
use Stripe\ApiOperations\All;

class FoodService extends BaseService
{
    use FileUploadTrait;

    protected string $modelClass = Food::class;

    /**
     * Define allowed filters for Spatie QueryBuilder
     */
    protected function getAllowedFilters(): array
    {
        return [
            'name',
            'category_id',
            AllowedFilter::exact('status'),
            // Allow searching across name and description columns
            AllowedFilter::custom('search', new GlobalSearchFilter(), 'name,description'),
        ];
    }

    /**
     * Define allowed includes for eager loading
     */
    protected function getAllowedIncludes(): array
    {
        return ['category'];
    }

    /**
     * Define allowed sorts
     */
    protected function getAllowedSorts(): array
    {
        return ['id', 'name', 'price', 'stock', 'created_at'];
    }

    /**
     * Handle food item creation along with image upload
     */
    public function storeFood(Request $request): Food
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {

            $data['image'] = $this->handleFileUpload($request, 'image', 'foods', forceWebp: true);
        }

        return $this->create($data);
    }

    /**
     * Handle food update along with old image deletion
     */
    public function updateFood(Request $request, $id): Food
    {
        $data = $request->validated();
        $food = $this->getById($id);

        if ($request->hasFile('image')) {
            $this->deleteFile($food->image);
            $data['image'] = $this->handleFileUpload($request, 'image', 'foods', forceWebp: true);
        }

        return $this->update($id, $data);
    }

    /**
     * Delete food item and its associated image
     */
    public function deleteFood($id): bool
    {
        $food = $this->getById($id);
        $this->deleteFile($food->image);

        return $this->delete($id);
    }
}
