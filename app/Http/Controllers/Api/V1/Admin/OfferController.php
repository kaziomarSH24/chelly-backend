<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfferRequest;
use App\Models\Offer;
use App\Services\OfferService;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function __construct(protected OfferService $offerService)
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->authorizeResource(Offer::class, 'offer', [
            'except' => ['index', 'show']
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $offers = $this->offerService->getAll(function ($query) {
            $user = auth('sanctum')->user();
            // If there is no authenticated user OR the user is not an admin, show only active offers
            // dd($user);
            if (!$user || !$user->hasRole('admin')) {
                $query->where('status', 'active');
            }
        });
        if($offers->isEmpty()) {
            return response_error('No offers found.', [], 404);
        }

        return response_success('Offers retrieved successfully.', $offers);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(OfferRequest $request)
    {
        $data = $request->validated();
        $offer = $this->offerService->create($data);
        return response_success('Offer created successfully.', $offer, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Use service to fetch data so it gets CACHED
        $offer = $this->offerService->getById($id);

        // If the offer is inactive and the user is not an admin, return 404
        if ($offer->status === 'inactive') {
            $user = auth('sanctum')->user();

            if (!$user || !$user->hasRole('admin')) {
                return response_error('Offer not found.', [], 404);
            }
        }
        return response_success('Offer details retrieved successfully.', $offer);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(OfferRequest $request, string $id)
    {
        $offer = $this->offerService->update($id, $request->validated());
        return response_success('Offer updated successfully.', $offer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->offerService->delete($id);
        return response_success('Offer deleted successfully.');
    }
}
