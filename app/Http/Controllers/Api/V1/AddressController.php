<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Services\AddressService;
use Exception;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(protected AddressService $addressService)
    {
        // $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $userId = auth('sanctum')->id();
        $addresses = $this->addressService->getUserAddresses($userId);

        if ($addresses->isEmpty()) {
            return response_error('No addresses found.', [], 404);
        }

        return response_success('Addresses retrieved successfully.', $addresses);
    }

    public function store(AddressRequest $request)
    {

        $userId = auth('sanctum')->id();
        $address = $this->addressService->storeAddress($userId, $request->validated());

        return response_success('Address created successfully.', $address, 201);
    }

    public function update(AddressRequest $request, string $id)
    {
        $userId = auth('sanctum')->id();
        $address = $this->addressService->updateAddress($userId, $id, $request->validated());

        return response_success('Address updated successfully.', $address);
    }

    public function destroy(string $id)
    {
        $userId = auth('sanctum')->id();
        $address = clone $this->addressService->getById($id);

        if ($address->user_id !== $userId) {
            return response_error('Unauthorized action.', [], 403);
        }

        $this->addressService->delete($id);
        return response_success('Address deleted successfully.');
    }
}
