<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService)
    {
    }

    /**
     * Retrieve admin dashboard overview data
     */
    public function index()
    {
        $data = $this->dashboardService->getDashboardData();

        return response_success('Dashboard data retrieved successfully.', $data);
    }
}
