<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->dashboardService->getDashboard(),
        ]);
    }
}
