<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PesajeController extends Controller
{
    public function index(): View
    {
        return view('modules.admin.pesajes.index');
    }
}
