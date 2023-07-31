<?php


use App\Http\Controllers\Admin\KategoriKaryawanController;
use App\Http\Controllers\Admin\RoleAdminController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\TestController;
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
        'message' => 'authentication required'
    ], 401);
})->name('login');

Route::get('/testnotif', [TestController::class, 'testNotifEvent']);
Route::get('/test-time', [TestController::class, 'testTimeNow']);
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
    Route::post('/izin', [HomeController::class, 'izin']);
    Route::post('/absen-pulang/{id}', [HomeController::class, 'absenPulang']);
    Route::get('/jadwal', [HomeController::class, 'jadwalAbsen']);
    Route::get('/history-notif', [HomeController::class, 'historyNotif']);
//    Route::post('/read-notif', [HomeController::class, 'readNotif']);
});

// Route History
Route::get('/history/donload-rekapan', [HistoryController::class, 'donloadKehadiran'])->middleware('auth:sanctum');
Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'history'], function () {
    Route::get('/absen', [HistoryController::class, 'absen']);
    Route::get('/izin', [HistoryController::class, 'izin']);
    Route::get('/rekapan', [HistoryController::class, 'recap']);
});

// Route Calender
Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'calendar'], function () {
    Route::get('/', [KalenderController::class, 'index']);
    Route::get('/show/{id}', [KalenderController::class, 'show']);
    Route::get('/notifevent', [KalenderController::class, 'notifEventToday']);
});

// Route Profile
Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'profile'], function () {
    Route::get('/', [ProfileController::class, 'index']);
    Route::get('/detail', [ProfileController::class, 'show']);
    Route::post('/reset-pw', [ProfileController::class, 'resetPassword']);
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
    Route::get('/jml-kehadiran', [KehadiranController::class, 'jmlKehadiran']);
    Route::get('/detail/{id}', [KehadiranController::class, 'detailAbsen']);
    Route::get('/detail-izin/{id}', [KehadiranController::class, 'detailIzin']);
    Route::get('/kehadiran-terbaru', [KehadiranController::class, 'kehadiranTerbaru']);
    Route::post('/search', [KehadiranController::class, 'search']);
    Route::get('/test', [KehadiranController::class, 'testKehadiran']);
});

// Route Admin Karyawan
Route::group(['middleware' => ['auth:sanctum', 'is_admin'], 'prefix' => 'karyawan'], function () {
    Route::get('/kategori', [KaryawanController::class, 'getKategori']);
    Route::get('/', [KaryawanController::class, 'index']);
    Route::post('/store', [KaryawanController::class, 'store']);
    Route::post('/import', [KaryawanController::class, 'import']);
    Route::get('/detail/{id}', [KaryawanController::class, 'show']);
    Route::post('/update/{id}', [KaryawanController::class, 'update']);

    Route::get('/delete/{id}', [KaryawanController::class, 'delete']);
    Route::post('/testjadwal', [KaryawanController::class, 'testUpdate']);
    Route::post('/testkategori/{id}', [KaryawanController::class, 'testKategori']);
});

// Route Admin Event
Route::group(['middleware' => ['auth:sanctum', 'is_admin'], 'prefix' => 'kalender'], function () {
    Route::get('/', [\App\Http\Controllers\Admin\KalenderController::class, 'index']);
    Route::post('/create', [\App\Http\Controllers\Admin\KalenderController::class, 'store']);
    Route::get('/destroy/{id}', [\App\Http\Controllers\Admin\KalenderController::class, 'destroy']);
    Route::post('/update/{id}', [\App\Http\Controllers\Admin\KalenderController::class, 'update']);
    Route::get('/detail/{id}', [\App\Http\Controllers\Admin\KalenderController::class, 'show']);

    Route::get('/get-kategori', [\App\Http\Controllers\Admin\KalenderController::class, 'getKategori']);
    Route::get('/get-karyawan', [\App\Http\Controllers\Admin\KalenderController::class, 'getKaryawan']);
});

// Route Admin Setting
Route::group(['middleware' => ['auth:sanctum', 'is_admin'], 'prefix' => 'setting'], function () {
    // Kategori
    Route::group(['prefix' => 'kategori'], function () {
        Route::get('/', [KategoriKaryawanController::class, 'index']);
        Route::post('/store', [KategoriKaryawanController::class, 'store']);
        Route::post('/update/{id}', [KategoriKaryawanController::class, 'update']);
        Route::get('/delete/{id}', [KategoriKaryawanController::class, 'delete']);
        Route::get('/get-karyawan', [KategoriKaryawanController::class, 'getAllKaryawan']);
        Route::get('/detail/{id}', [KategoriKaryawanController::class, 'show']);
        Route::post('/assign', [KategoriKaryawanController::class, 'assignKategori']);
        Route::post('/unassign', [KategoriKaryawanController::class, 'unAssignKategori']);
    });
    // Kordinat
    Route::group(['prefix' => 'kordinat'], function () {
        Route::post('/update', [SettingController::class, 'updateDataKordinat']);
    });
    // Role Admin
    Route::group(['prefix' => 'role-admin'], function () {
        Route::get('/', [RoleAdminController::class, 'index']);
        Route::post('/store', [RoleAdminController::class, 'store']);
        Route::get('/destroy', [RoleAdminController::class, 'destroy']);
    });
    // Batas Waktu Absen
    Route::group(['prefix' => 'batas-waktu'], function () {
        Route::get('/', [SettingController::class, 'indexBatasWaktu']);
        Route::post('/update', [SettingController::class, 'updateBatasWaktu']);
    });
});
// get data kordinat
Route::get('/setting/kordinat', [SettingController::class, 'getDataKordinat']);

// push notif
Route::post('save-token', [App\Http\Controllers\NotifController::class, 'saveToken'])->middleware('auth:sanctum');
Route::post('send-notification', [App\Http\Controllers\NotifController::class, 'sendNotification'])->middleware('auth:sanctum');

//test
Route::get('/get-user-blom-absen', [TestController::class, 'testYgBlomAbsen']);
Route::post('/parsedate', [HistoryController::class, 'parseDate']);
Route::get('/absen-libur', [TestController::class, 'testAbsenLibur']);
Route::get('/test-absen', function () {
    dispatch(new \App\Jobs\AbsenJob());
});
