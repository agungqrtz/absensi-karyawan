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

// Rute untuk halaman "Pendaftaran Berhasil"
Route::get('/register/success', function () {
    return view('auth.registered');
})->name('register.success');

// Redirect dari "/" ke "/beranda"
Route::get('/', function () {
    return redirect('/beranda');
});

// Halaman utama aplikasi yang memerlukan login
Route::get('/beranda', [AttendanceController::class, 'index'])
    ->name('attendance.index')
    ->middleware('auth');

// ===================================================================
// GRUP RUTE API (DIKEMBALIKAN KE SINI DENGAN MIDDLEWARE 'auth')
// ===================================================================
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/attendance-status', [AttendanceController::class, 'getAttendanceStatus']);
    Route::post('/attendance', [AttendanceController::class, 'submitAttendance']);
    Route::get('/recap', [AttendanceController::class, 'getRecap']);
    
    // Rute ini sekarang akan ditemukan oleh helper route() di Blade
    Route::get('/export/recap', [AttendanceController::class, 'exportRecap'])->name('export.recap');

    Route::get('/statistics', [AttendanceController::class, 'getStatisticsData']);
    
    // Rute CRUD Absensi
    Route::post('/attendance-data', [AttendanceController::class, 'store']);
    Route::put('/attendance-data/{attendance}', [AttendanceController::class, 'update']);
    Route::delete('/attendance-data/{attendance}', [AttendanceController::class, 'destroy']);
    
    // RUTE BARU UNTUK HAPUS DATA PER BULAN
    Route::delete('/recap/delete-month', [AttendanceController::class, 'destroyCurrentMonth'])->name('recap.delete_month');
    
    // Rute CRUD Karyawan
    Route::get('/employees', [AttendanceController::class, 'getEmployees']);
    Route::post('/employees', [AttendanceController::class, 'storeEmployee']);
    Route::delete('/employees/{employee}', [AttendanceController::class, 'destroyEmployee']);
});
