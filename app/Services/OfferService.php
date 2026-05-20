<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\Offer;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\GlobalSearchFilter;

class OfferService extends BaseService
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected string $modelClass = Offer::class;

    protected bool $cachePerUser = true;
    // protected bool $cachingEnabled = false;

    public function __construct()
    {
        // Ensure BaseService initializes the model instance
        parent::__construct();
    }


    // Define allowed filters
     protected function getAllowedFilters(): array
    {
        return [
           'title',
            'status',
            AllowedFilter::custom('search', new GlobalSearchFilter(), 'title'),
        ];
    }

    // Define allowed includes relationships
     protected function getAllowedIncludes(): array
     {
        return [
            //
        ];
     }

     // Define allowed sorts
     protected function getAllowedSorts(): array
     {
        return [
            'id',
            'name',
            'created_at',
        ];
     }
}
