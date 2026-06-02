<?php

use App\Http\Controllers\SuperAdmin\DashboardController as SuperDashboardController;
use App\Http\Controllers\SuperAdmin\OrganizacionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:super_admin'])->name('super.')->group(function () {

    Route::get('/dashboard', [SuperDashboardController::class, 'index'])->name('dashboard');

    // Organizaciones ——————————————————————————————————————————————
    Route::resource('organizaciones', OrganizacionController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['organizaciones' => 'organizacion']);
    Route::patch('organizaciones/{organizacion}/toggle', [OrganizacionController::class, 'toggle'])
        ->name('organizaciones.toggle');
    Route::post('organizaciones/{organizacion}/usuarios', [OrganizacionController::class, 'addUser'])
        ->name('organizaciones.addUser');
    Route::delete('organizaciones/{organizacion}/usuarios/{user}', [OrganizacionController::class, 'removeUser'])
        ->name('organizaciones.removeUser');
    Route::post('organizaciones/{organizacion}/usuarios/{user}/reset-password', [OrganizacionController::class, 'resetUserPassword'])
        ->name('organizaciones.resetUserPassword');

    // Usuarios ————————————————————————————————————————————————————
    Route::get('usuarios/search', [OrganizacionController::class, 'searchUsers'])
        ->name('usuarios.search');
});
