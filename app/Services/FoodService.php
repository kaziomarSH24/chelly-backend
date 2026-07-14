<?php

namespace App\Services;

use App\Models\Food;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\GlobalSearchFilter;
use Illuminate\Support\Facades\DB;
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
        return ['category', 'variants', 'images'];
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
        
        $images = $request->file('images') ?? [];
        unset($data['images']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->handleFileUpload($request, 'image', 'foods', forceWebp: true);
        }

        return DB::transaction(function () use ($data, $variants, $images) {
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

            // Sync Images
            if (!empty($images)) {
                $foodImages = [];
                foreach ($images as $index => $imageFile) {
                    $path = $this->handleUploadedFile($imageFile, 'foods', forceWebp: true);
                    if ($path) {
                        $foodImages[] = [
                            'image_path' => $path,
                            'sort_order' => $index
                        ];
                    }
                }
                if (!empty($foodImages)) {
                    $food->images()->createMany($foodImages);
                }
            }

            return $food->load('category', 'variants', 'images');
        });
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
        
        $images = $request->file('images') ?? [];
        unset($data['images']);
        
        $deletedImageIds = $data['deleted_image_ids'] ?? [];
        unset($data['deleted_image_ids']);

        if ($request->hasFile('image')) {
            $this->deleteFile($food->image);
            $data['image'] = $this->handleFileUpload($request, 'image', 'foods', forceWebp: true);
        }

        return DB::transaction(function () use ($id, $data, $variants, $food, $request, $images, $deletedImageIds) {
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

            // Delete specific old images
            if (!empty($deletedImageIds)) {
                $imagesToDelete = $food->images()->whereIn('id', $deletedImageIds)->get();
                foreach ($imagesToDelete as $img) {
                    $this->deleteFile($img->getRawOriginal('image_path'));
                    $img->delete();
                }
            }

            // Upload new images
            if (!empty($images)) {
                $foodImages = [];
                $currentMaxSort = $food->images()->max('sort_order') ?? 0;
                
                foreach ($images as $index => $imageFile) {
                    $path = $this->handleUploadedFile($imageFile, 'foods', forceWebp: true);
                    if ($path) {
                        $foodImages[] = [
                            'image_path' => $path,
                            'sort_order' => $currentMaxSort + $index + 1
                        ];
                    }
                }
                if (!empty($foodImages)) {
                    $food->images()->createMany($foodImages);
                }
            }

            return $food->fresh('category', 'variants', 'images');
        });
    }

    /**
     * Delete food item and its associated image
     */
    public function deleteFood($id): bool
    {
        $food = $this->getById($id);
        $this->deleteFile($food->image);
        
        foreach ($food->images as $img) {
            $this->deleteFile($img->getRawOriginal('image_path'));
        }

        return $this->delete($id);
    }
}
