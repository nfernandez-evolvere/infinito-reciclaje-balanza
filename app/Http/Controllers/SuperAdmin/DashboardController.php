<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdminDashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected SuperAdminDashboardService $dashboardService,
    ) {}

    public function index(): View
    {
        return view('modules.super_admin.dashboard', [
            'stats'     => $this->dashboardService->stats(),
            'recientes' => $this->dashboardService->recientes(),
        ]);
    }
}
