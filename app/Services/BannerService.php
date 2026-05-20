<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\Banner;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\GlobalSearchFilter;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Stripe\ApiOperations\All;

class BannerService extends BaseService
{
    use FileUploadTrait;

    protected string $modelClass = Banner::class;

    // Enable per-user caching to separate admin and guest data
    protected bool $cachePerUser = true;

    /**
     * Define allowed filters for Spatie QueryBuilder
     */
    protected function getAllowedFilters(): array
    {
        return [
            'title',
            AllowedFilter::exact('status'),
            AllowedFilter::custom('search', new GlobalSearchFilter(), 'title'),
        ];
    }

    /**
     * Define allowed includes for eager loading
     */
    protected function getAllowedIncludes(): array
    {
        return [];
    }

    /**
     * Define allowed sorts
     */
    protected function getAllowedSorts(): array
    {
        return ['id', 'title', 'created_at'];
    }

    /**
     * Handle banner creation along with image upload
     */
    public function storeBanner(Request $request): Banner
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $this->handleFileUpload($request, 'image', 'banners', forceWebp: true);
        }

        return $this->create($data);
    }

    /**
     * Handle banner update along with old image deletion
     */
    public function updateBanner(Request $request, string $id): Banner
    {
        $data = $request->validated();
        $banner = $this->getById($id);

        if ($request->hasFile('image')) {
            $this->deleteFile($banner->image);
            $data['image'] = $this->handleFileUpload($request, 'image', 'banners', forceWebp: true);
        }

        return $this->update($id, $data);
    }

    /**
     * Delete banner item and its associated image
     */
    public function deleteBanner(string $id): bool
    {
        $banner = $this->getById($id);
        $this->deleteFile($banner->image);

        return $this->delete($id);
    }
}
