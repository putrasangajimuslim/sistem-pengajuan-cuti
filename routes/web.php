<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\CutiTahunanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisiController;
use App\Http\Controllers\IzinController;
use App\Http\Controllers\LaporanLeaveRequestController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');

Route::prefix('divisi')
    ->as('divisi.')
    ->middleware(['auth', 'role:HRD'])
    ->group(function () {
        Route::get('/', [DivisiController::class, 'index'])->name('index');
        Route::get('/getdatajson', [DivisiController::class, 'getDataJSON'])->name('getdatajson');
        Route::get('edit/{id}', [DivisiController::class, 'edit'])->name('edit');
        Route::post('store', [DivisiController::class, 'store'])->name('store');
        Route::put('update/{id}', [DivisiController::class, 'update'])->name('update');
        Route::delete('destroy/{id}', [DivisiController::class, 'destroy'])->name('destroy');
    });

Route::prefix('users')
    ->as('users.')
    ->middleware(['auth', 'role:HRD'])
    ->group(function () {
        Route::get('/', [UsersController::class, 'index'])->name('index');
        Route::get('/getdatajson', [UsersController::class, 'getDataJSON'])->name('getdatajson');
        Route::get('edit/{id}', [UsersController::class, 'edit'])->name('edit');
        Route::post('store', [UsersController::class, 'store'])->name('store');
        Route::put('update/{id}', [UsersController::class, 'update'])->name('update');
        Route::delete('destroy/{id}', [UsersController::class, 'destroy'])->name('destroy');
    });

Route::prefix('cutitahunan')
    ->as('cutitahunan.')
    ->middleware(['auth', 'role:HRD'])
    ->group(function () {
        Route::get('/', [CutiTahunanController::class, 'index'])->name('index');
        Route::get('/getdatajson', [CutiTahunanController::class, 'getDataJSON'])->name('getdatajson');
        Route::get('edit/{id}', [CutiTahunanController::class, 'edit'])->name('edit');
        Route::post('store', [CutiTahunanController::class, 'store'])->name('store');
        Route::put('update/{id}', [CutiTahunanController::class, 'update'])->name('update');
        Route::delete('destroy/{id}', [CutiTahunanController::class, 'destroy'])->name('destroy');
    });

Route::prefix('laporan')
    ->as('laporan.')
    ->middleware(['auth', 'role:HRD'])
    ->group(function () {
        Route::get('/', [LaporanLeaveRequestController::class, 'index'])->name('index');
        Route::get('/getdatajson', [LaporanLeaveRequestController::class, 'getDataJSON'])->name('getdatajson');
        Route::post('export-laporan', [LaporanLeaveRequestController::class, 'exportExcel'])->name('export-laporan');
    });

Route::prefix('pengajuan')
    ->as('pengajuan.')
    ->middleware(['auth', 'role:HRD|Manager|Staff|Supervisor'])
    ->group(function () {
        Route::prefix('cuti')->as('cuti.')->group(function () {
            Route::get('/', [CutiController::class, 'index'])->name('index');
            Route::get('/getdatajson', [CutiController::class, 'getDataJSON'])->name('getdatajson');
            Route::get('/getdataleaveticketjson/{id}', [CutiController::class, 'getDataLeaveTicketJSON'])->name('getdataleaveticketjson');
            Route::post('getdataprogramcutijson', [CutiController::class, 'getDataProgramCutiJSON'])->name('getdataprogramcutijson');
            Route::post('store', [CutiController::class, 'store'])->name('store');
            Route::post('updatestatusleave', [CutiController::class, 'updateStatusLeave'])->name('updatestatusleave');
        });

        Route::prefix('izin')->as('izin.')->group(function () {
            Route::get('/', [IzinController::class, 'index'])->name('index');
            Route::get('/getdatajson', [IzinController::class, 'getDataJSON'])->name('getdatajson');
            Route::get('/getdataleaveticketjson/{id}', [IzinController::class, 'getDataLeaveTicketJSON'])->name('getdataleaveticketjson');
            Route::post('getdataprogramcutijson', [IzinController::class, 'getDataProgramCutiJSON'])->name('getdataprogramcutijson');
            Route::post('store', [IzinController::class, 'store'])->name('store');
            Route::post('updatestatusleave', [IzinController::class, 'updateStatusLeave'])->name('updatestatusleave');
        });
    });
