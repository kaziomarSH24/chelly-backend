<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BannerRequest;
use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function __construct(protected BannerService $bannerService)
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);

        $this->authorizeResource(Banner::class, 'banner', [
            'except' => ['index', 'show']
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = $this->bannerService->getAll(function ($query) {
            $user = auth('sanctum')->user();

            // Show only active banners if the user is a guest or not an admin
            if (!$user || !$user->hasRole('admin')) {
                $query->where('status', 'active');
            }
        });

        if($banners->isEmpty()) {
            return response_error('No banners found.', [], 404);
        }

        return response_success('Banners retrieved successfully.', $banners);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(BannerRequest $request)
    {
        $banner = $this->bannerService->storeBanner($request);
        return response_success('Banner created successfully.', $banner, 201);
    }

    public function show(string $id)
    {
        $banner = $this->bannerService->getById($id);

        // Prevent non-admins from viewing an inactive banner
        if ($banner->status === 'inactive') {
            $user = auth('sanctum')->user();
            if (!$user || !$user->hasRole('admin')) {
                return response_error('Banner not found.', [], 404);
            }
        }

        return response_success('Banner details retrieved successfully.', $banner);
    }

    public function update(BannerRequest $request, string $id)
    {
        $banner = $this->bannerService->updateBanner($request, $id);
        return response_success('Banner updated successfully.', $banner);
    }

    public function destroy(string $id)
    {
        $this->bannerService->deleteBanner($id);
        return response_success('Banner deleted successfully.');
    }
}
