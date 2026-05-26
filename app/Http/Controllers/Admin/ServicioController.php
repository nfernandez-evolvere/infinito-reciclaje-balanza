<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ServicioController extends Controller
{
    public function index(): View
    {
        return view('modules.admin.servicios.index');
    }
}
