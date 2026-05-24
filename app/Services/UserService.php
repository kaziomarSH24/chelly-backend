<?php

namespace App\Services;

use App\Filters\GlobalSearchFilter;
use App\Services\BaseService;
use App\Models\User;
use App\Traits\FileUploadTrait;
use Spatie\QueryBuilder\AllowedFilter;

class UserService extends BaseService
{
    use FileUploadTrait;
    /**
     * The model class name.
     *
     * @var string
     */
    protected string $modelClass = User::class;

    protected bool $cachingEnabled = true;

    public function __construct()
    {
        // Ensure BaseService initializes the model instance
        parent::__construct();
    }

    /**
     * Which fields are allowed to be filtered by.
     * @var array
     */
    protected function getAllowedFilters(): array
    {
        return [
            AllowedFilter::custom('search', new GlobalSearchFilter, 'name,email'),
            'name',
            'email',
            AllowedFilter::exact('status'),
        ];
    }

    /**
     * Which fields are allowed to be sorted by.
     * @var array
     */
    protected function getAllowedSorts(): array
    {
        return [
            'id',
            'name',
            'created_at',
        ];
    }

    /**
     * Which relationships are allowed to be loaded.
     * @var array
     */
    protected function getAllowedIncludes(): array
    {
        return [
            'roles',
        ];
    }

    public function toggleStatus(int $id): User
    {
        $user = User::findOrFail($id);

        // Prevent admin from deactivating themselves
        if ($user->id === auth('sanctum')->id()) {
            abort(403, 'You cannot deactivate your own account.');
        }

        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active'
        ]);

        return $user;
    }

    public function deleteUser(int $id): bool
    {
        $user = User::findOrFail($id);

        if ($user->id === auth('sanctum')->id()) {
            abort(403, 'You cannot delete your own account.');
        }
        //avatar remove logic
        if ($user->avatar) {
            $this->deleteFile($user->avatar);
        }

        return $user->delete();
    }
}
