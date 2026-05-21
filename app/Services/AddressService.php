<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\Address;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\GlobalSearchFilter;
use Illuminate\Support\Facades\DB;

class AddressService extends BaseService
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected string $modelClass = Address::class;
    protected bool $cachePerUser = true;

    public function __construct()
    {
        // Ensure BaseService initializes the model instance
        parent::__construct();
    }


    // Define allowed filters
    protected function getAllowedFilters(): array
    {
        return [];
    }

    // Define allowed includes relationships
    protected function getAllowedIncludes(): array
    {
        return [];
    }

    // Define allowed sorts
    protected function getAllowedSorts(): array
    {
        return [];
    }



    public function storeAddress(int $userId, array $data): Address
    {
        return DB::transaction(function () use ($userId, $data) {
            $data['user_id'] = $userId;
            $isFirstAddress = Address::where('user_id', $userId)->count() === 0;
            if ($isFirstAddress) {
                $data['is_default'] = true;
            }
            if (isset($data['is_default']) && $data['is_default']) {
                Address::where('user_id', $userId)->update(['is_default' => false]);
            }

            return $this->create($data);
        });
    }

    public function updateAddress(int $userId, int $addressId, array $data): Address
    {
        return DB::transaction(function () use ($userId, $addressId, $data) {
            $address = $this->getById($addressId);

            if ($address->user_id !== $userId) {
                abort(403, 'Unauthorized action.');
            }

            if (isset($data['is_default']) && $data['is_default']) {
                Address::where('user_id', $userId)
                    ->where('id', '!=', $addressId)
                    ->update(['is_default' => false]);
            }

            return $this->update($addressId, $data);
        });
    }

    public function getUserAddresses(int $userId)
    {
        return Address::where('user_id', $userId)
            ->orderByDesc('is_default') 
            ->latest()
            ->get();
    }
}
