<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index()
    {
        $users = $this->userService->getAll();
        return response_success('Users retrieved successfully.', $users);
    }

    public function show(string $id)
    {
        $user = User::with(['orders' => function ($q) {
            $q->latest()->take(5); // Show user's recent orders in their profile
        }])->findOrFail($id);

        return response_success('User details retrieved successfully.', $user);
    }

    public function toggleStatus(string $id)
    {
        $user = $this->userService->toggleStatus($id);
        return response_success('User status updated successfully.', ['status' => $user->status]);
    }

    public function destroy(string $id)
    {
        $this->userService->deleteUser($id);
        return response_success('User deleted successfully.');
    }
}
