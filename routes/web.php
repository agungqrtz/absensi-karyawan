<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Auth::routes();

// Tambahkan route ini untuk redirect dari "/" ke "/beranda"
Route::get('/', function () {
    return redirect('/beranda');
});

// Ubah URL halaman utama menjadi /beranda
Route::get('/beranda', [AttendanceController::class, 'index'])->name('attendance.index')->middleware('auth');

// Grup rute API tetap sama, tapi sekarang dilindungi oleh middleware 'auth'
Route::prefix('api')->middleware('auth')->group(function () {
    Route::post('/attendance', [AttendanceController::class, 'submitAttendance']);
    Route::get('/recap', [AttendanceController::class, 'getRecap']);
    Route::post('/attendance-data', [AttendanceController::class, 'store']);
    Route::put('/attendance-data/{attendance}', [AttendanceController::class, 'update']);
    Route::delete('/attendance-data/{attendance}', [AttendanceController::class, 'destroy']);
    Route::get('/employees', [AttendanceController::class, 'getEmployees']);
    Route::post('/employees', [AttendanceController::class, 'storeEmployee']);
    Route::delete('/employees/{employee}', [AttendanceController::class, 'destroyEmployee']);
});
