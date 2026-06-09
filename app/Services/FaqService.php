<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\Faq;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\GlobalSearchFilter;

class FaqService extends BaseService
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected string $modelClass = Faq::class;

    public function __construct()
    {
        // Ensure BaseService initializes the model instance
        parent::__construct();
    }


    /**
     * Define allowed filters
     */
    protected function getAllowedFilters(): array
    {
        return [
            // Search globally across question and answer fields
            AllowedFilter::custom('search', new GlobalSearchFilter, 'question', 'answer'),
            'question',
            AllowedFilter::exact('is_active'),
        ];
    }

    /**
     * Define allowed includes relationships
     */
    protected function getAllowedIncludes(): array
    {
        return [
            // No relationships needed for FAQ at the moment
        ];
    }

    /**
     * Define allowed sorts
     */
    protected function getAllowedSorts(): array
    {
        return [
            'id',
            'question',
            'created_at',
        ];
    }
}
