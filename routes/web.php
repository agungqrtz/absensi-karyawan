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

// Rute BARU untuk halaman "Pendaftaran Berhasil"
Route::get('/register/success', function () {
    return view('auth.registered');
})->name('register.success');

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
    
    // Rute untuk Ekspor Excel
    Route::get('/export/recap', [AttendanceController::class, 'exportRecap'])->name('export.recap');

    // Rute BARU untuk Statistik & Grafik
    Route::get('/statistics', [AttendanceController::class, 'getStatisticsData'])->name('statistics.data');

    Route::post('/attendance-data', [AttendanceController::class, 'store']);
    Route::put('/attendance-data/{attendance}', [AttendanceController::class, 'update']);
    Route::delete('/attendance-data/{attendance}', [AttendanceController::class, 'destroy']);
    Route::get('/employees', [AttendanceController::class, 'getEmployees']);
    Route::post('/employees', [AttendanceController::class, 'storeEmployee']);
    Route::delete('/employees/{employee}', [AttendanceController::class, 'destroyEmployee']);
});
