<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KehadiranController;
use App\Http\Controllers\Admin\KaryawanController;

use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\HistoryController;
use App\Http\Controllers\Client\KalenderController;
use App\Http\Controllers\Client\ProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
    return response()->json([
        'error' => '401',
        'message' => 'authentication required'
    ], 401);
})->name('login');

// Route Login
Route::post('/login', [AuthController::class, 'login']);

// logout
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Route Home
Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'home'], function () {
    Route::get('/', [HomeController::class, 'kehadiran']);
    Route::post('/absen-masuk', [HomeController::class, 'absenMasuk']);
    Route::post('/absen-pulang', [HomeController::class, 'absenPulang']);
});

// Route History
Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'history'], function () {
    Route::get('/', [HistoryController::class, 'default']);
    Route::get('/otherdate', [HistoryController::class, 'index']);
});

// Route Calender
Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'calendar'], function () {
    Route::get('/', [KalenderController::class, 'index']);
    Route::get('/otherdate', [KalenderController::class, 'otherDate']);
    Route::get('/notifevent', [KalenderController::class, 'notifEvent']);
});

// Route Profile
Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'profile'], function () {
    Route::get('/', [ProfileController::class, 'index']);
    Route::get('/detail', [ProfileController::class, 'show']);
    Route::post('/reset-password', [ProfileController::class, 'resetPassword']);
});


// Route Admin Dashboard
Route::get('/dashboard/donload', [KehadiranController::class, 'donloadKehadiran']);
Route::group(['middleware' => ['auth:sanctum', 'is_admin'], 'prefix' => 'dashboard'], function () {
    Route::get('/', [DashboardController::class, 'dashboard']);
    Route::get('/statistik', [DashboardController::class, 'statistik']);
    Route::get('/jadwal', [DashboardController::class, 'jadwal']);
});

// Route Admin Kehadiran
Route::group(['middleware' => ['auth:sanctum', 'is_admin'], 'prefix' => 'kehadiran'], function () {
    Route::get('/', [KehadiranController::class, 'kehadiran']);
    Route::get('/detail/{id}', [KehadiranController::class, 'detailAbsen']);
    Route::get('/kehadiran-terbaru', [KehadiranController::class, 'kehadiranTerbaru']);
    Route::post('/search', [KehadiranController::class, 'search']);
});

// Route Admin Karyawan
Route::group(['middleware' => ['auth:sanctum', 'is_admin'], 'prefix' => 'karyawan'], function () {
    Route::get('/', [KaryawanController::class, 'index']);
    Route::post('/store-user', [KaryawanController::class, 'storeUser']);

    Route::get('/edit/{id}', [KaryawanController::class, 'edit']);
    Route::post('/update-user', [KaryawanController::class, 'updateUser']);

    Route::get('/delete-user/{id}', [KaryawanController::class, 'deleteUser']);
});

// Route Admin Kalender
Route::group(['middleware' => ['auth:sanctum', 'is_admin'], 'prefix' => 'kalender'], function () {
    Route::get('/', [\App\Http\Controllers\Admin\KalenderController::class, 'index']);
    Route::post('/create', [\App\Http\Controllers\Admin\KalenderController::class, 'store']);
    Route::get('/destroy/{id}', [\App\Http\Controllers\Admin\KalenderController::class, 'destroy']);
    Route::get('/update/{id}', [\App\Http\Controllers\Admin\KalenderController::class, 'update']);
    Route::post('/edit', [\App\Http\Controllers\Admin\KalenderController::class, 'edit']);
});
