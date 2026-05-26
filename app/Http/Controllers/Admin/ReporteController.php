<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function index(): View
    {
        return view('modules.admin.reportes.index');
    }
}
