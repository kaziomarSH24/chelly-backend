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
    protected bool $cachePerUser = true;

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
        return ['category', 'variants'];
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
        
        $variants = $data['variants'] ?? [];
        unset($data['variants']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->handleFileUpload($request, 'image', 'foods', forceWebp: true);
        }

        $food = $this->create($data);

        // Sync Variants
        if (!empty($variants)) {
            $variants = array_map(function($variant) {
                if (empty($variant['title'])) {
                    $titleParts = array_filter([$variant['option1'] ?? null, $variant['option2'] ?? null, $variant['option3'] ?? null]);
                    $variant['title'] = !empty($titleParts) ? implode(' / ', $titleParts) : 'Default';
                }
                return $variant;
            }, $variants);

            $food->variants()->createMany($variants);
        }

        return $food->load('category', 'variants');
    }

    /**
     * Handle food update along with old image deletion
     */
    public function updateFood(Request $request, $id): Food
    {
        $data = $request->validated();
        $food = $this->getById($id);

        $variants = $data['variants'] ?? [];
        unset($data['variants']);

        if ($request->hasFile('image')) {
            $this->deleteFile($food->image);
            $data['image'] = $this->handleFileUpload($request, 'image', 'foods', forceWebp: true);
        }

        $this->update($id, $data);

        // Sync Variants
        if (!empty($variants)) {
            // Delete variants that are not in the payload
            $variantIds = collect($variants)->pluck('id')->filter()->toArray();
            $food->variants()->whereNotIn('id', $variantIds)->delete();

            // Update or create variants
            foreach ($variants as $variantData) {
                if (empty($variantData['title'])) {
                    $titleParts = array_filter([$variantData['option1'] ?? null, $variantData['option2'] ?? null, $variantData['option3'] ?? null]);
                    $variantData['title'] = !empty($titleParts) ? implode(' / ', $titleParts) : 'Default';
                }

                if (isset($variantData['id'])) {
                    $food->variants()->where('id', $variantData['id'])->update($variantData);
                } else {
                    $food->variants()->create($variantData);
                }
            }
        } else {
            // If variants array is empty or not provided, we could either do nothing or delete all.
            // Usually, an empty array means clear all variants. But let's only clear if explicitly passed as empty array.
            if ($request->has('variants')) {
                $food->variants()->delete();
            }
        }

        return $food->fresh('category', 'variants');
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
