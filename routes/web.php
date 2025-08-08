<?php

use App\Http\Controllers\adminController;
use App\Http\Controllers\authController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\displayController;
use App\Http\Controllers\displaydataController;
use App\Http\Controllers\displaysymbolController;
use App\Http\Controllers\eecsController;
use App\Http\Controllers\eecsDeviceController;
use App\Http\Controllers\eecsSensorController;
use App\Http\Controllers\exportController;
use App\Http\Controllers\financialModelController;
use App\Http\Controllers\floorController;
use App\Http\Controllers\floorMapController;
use App\Http\Controllers\historyController;
use App\Http\Controllers\interconnectController;
use App\Http\Controllers\notificationController;
use App\Http\Controllers\OrMapController;
use App\Http\Controllers\QrMapController;
use App\Http\Controllers\reservationController;
use App\Http\Controllers\reservationDeviceController;
use App\Http\Controllers\sensorController;
use App\Http\Controllers\siteController;
use App\Http\Controllers\symbolController;
use App\Http\Controllers\tableController;
use App\Http\Controllers\tableEntryController;
use App\Http\Controllers\testController;
use App\Http\Controllers\zonalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

// Route::get('dashboard', function () {
//     return 'Welcome to your dashboard!';
// })->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard1', function () {
        return 'Welcome to your dashboard!';
    });

    Route::get('/profile', function () {
        return 'Your profile page';
    });

    // Other authenticated routes can go here...
    Route::resource('site', siteController::class);
    Route::resource('floor', floorController::class);
    Route::resource('interconnect', interconnectController::class);
    Route::resource('zonal', zonalController::class);
    Route::resource('sensor', sensorController::class);
    // Route::get('eecssensor/{id}', [eecsSensorController::class, 'getSensors']);
    Route::resource('eecs', eecsController::class);
    Route::resource('eecsdevice', eecsDeviceController::class);
    Route::resource('eecssensor', eecsSensorController::class);
    Route::resource('display', displayController::class);
    Route::resource('displaydata', displaydataController::class);
    Route::resource('symbol', symbolController::class);
    Route::resource('displaysymbol', displaysymbolController::class);
    Route::resource('reservation', reservationController::class);
    Route::resource('table', tableController::class);
    Route::resource('entries', tableEntryController::class);
    Route::resource('reservation_info', reservationDeviceController::class);
    Route::get('reservation-status-update/{id}', [reservationDeviceController::class, 'reservationStatusUpdate'])->name('reservation-status-update');

    Route::get('barrier-status-update/{id}', [reservationController::class, 'barrierStatusUpdate'])->name('barrier-status-update');
    Route::get('admins', [adminController::class, 'index'])->name('admins');
    Route::post('admin-store', [adminController::class, 'store'])->name('admin-store');
    Route::get('admins/edit/{id}', [adminController::class, 'show']);
    Route::post('admins/edit', [adminController::class, 'update'])->name('admins/edit');
    Route::get('admins/change/{id}', [adminController::class, 'changep']);
    Route::post('admins/changepassword', [adminController::class, 'changePassword'])->name('admins/changepassword');

    Route::post('check_reservation_site', [adminController::class, 'checkReservationSite'])->name('check_reservation_site');
    Route::post('get_slots_data', [adminController::class, 'getSlotsData'])->name('get_slots_data');
});

//dashboard
Route::middleware('isDashboard')->group(function () {
    // Route::get('dashboard/table', [dashboardController::class, 'dashboardTable'])->name('dashboard/table');
    Route::get('dashboard/table', [dashboardController::class, 'dashboardTable'])->name('dashboard/table');
    Route::get('dashboard/home', [dashboardController::class, 'index'])->name('dashboard/home');
    Route::get('dashboard/detailed-view', [dashboardController::class, 'detailedView'])->name('dashboard/detailed-view');
    Route::get('dashboard/test', [dashboardController::class, 'test3'])->name('dashboard/test');

    Route::get('dashboard/summary-report', [dashboardController::class, 'summaryReport'])->name('dashboard/summary-report');
    Route::post('dashboard/summary-report', [dashboardController::class, 'summaryReportPost'])->name('dashboard/summary-report');
    Route::get('dashboard/summary-report-stats/{name}', [dashboardController::class, 'summaryReportStats'])->name('dashboard/summary-report-stats');
    Route::post('/export-summary-report', [exportController::class, 'summaryReportExport'])->name('export-summary-report');

    Route::get('dashboard/chart', [dashboardController::class, 'chart'])->name('dashboard/chart');
    Route::get('dashboard/get-dashboard-data', [dashboardController::class, 'getDashboardData'])->name('get-dashboard-data');
    Route::get('dashboard/get-detailed-data', [dashboardController::class, 'getDetailedViewData'])->name('get-detailed-data');
    Route::get('dashboard/get-detailed-chart-data', [dashboardController::class, 'getDetailedChartData'])->name('get-detailed-chart-data');
    Route::get('dashboard/change-data', [dashboardController::class, 'changeData'])->name('change-data');
    Route::get('dashboard/table-view', [dashboardController::class, 'tableViewData'])->name('dashboard/table-view');
    Route::get('dashboard/table-view-data', [dashboardController::class, 'getTableViewData'])->name('table-view-data');
    Route::get('dashboard/history', [historyController::class, 'historicalData'])->name('dashboard/history');
    Route::post('dashboard/historical-data', [historyController::class, 'historicalData'])->name('dashboard/historical-data');
    Route::get('dashboard/findmycar', [QrMapController::class, 'findMyCar']);
    Route::get('dashboard/findmycarpost/{sensor}', [QrMapController::class, 'findMyCarPost']);
    Route::post('dashboard/findmycarselect', [QrMapController::class, 'findMyCarSelect']);

    // EECS
    Route::get('dashboard/eecs-dashboard-data', [dashboardController::class, 'getEECSData'])->name('eecs-dashboard-data');
    Route::get('dashboard/editeecs', [dashboardController::class, 'editEecsCount'])->name('dashboard/editeecs');
    Route::get('dashboard/gettypes/{id}', [dashboardController::class, 'getTypes'])->name('dashboard/gettypes');
    Route::get('dashboard/maxgettypes/{id}', [dashboardController::class, 'maxgetTypes'])->name('dashboard/maxgettypes');
    Route::post('dashboard/updatecount', [dashboardController::class, 'updateCount'])->name('dashboard/updatecount');

    Route::get('dashboard/reservations/{floor_id}', [dashboardController::class, 'reservations'])->name('dashboard/reservations');
    Route::get('dashboard/reservation-data/{floor_id}', [reservationDeviceController::class, 'getReservationFloorData'])->name('dashboard/reservation-data');
    Route::get('dashboard/get-reservation-data', [reservationDeviceController::class, 'getReservationData'])->name('dashboard/get-reservation-data');

    // floor map
    Route::get('dashboard/floormap/{floor_id}', [floorMapController::class, 'index']);
    Route::get('dashboard/overnightfloormap/{floorId}', [floorMapController::class, 'getOvernightFloorData']);
    // Route::get('dashboard/floorm/getcardata/{floorId}', [floorMapController::class, 'getCarData']);
    Route::get('dashboard/floorm/{floor_id}', [floorMapController::class, 'test']);

    // financial model
    Route::get('dashboard/financial', [financialModelController::class, 'financialmodel']);


    Route::fallback(function () {
        // You can return a view, redirect, or custom message
        return response()->view('dashboard.pagenotfound', [], 404);
    });
    Route::get('dashboard/404', function () {
        return view('dashboard.pagenotfound');
    });
});

Route::get('dashboard/floormap/getfloordata/{floorId}', [floorMapController::class, 'getFloorData']);

// Route::get('overtimedata', [dashboardController::class, 'test']);
// OR Map Routes
Route::get('get_map/{site_id}/{floor_id}/{location}', [QrMapController::class, 'getMapData']);
Route::post('get_map', [QrMapController::class, 'getMapDataPost'])->name('get_map');
Route::get('test_qr', [QrMapController::class, 'testQR']);
Route::get('get_category_data/{site_id}/{selectedCategory}', [QrMapController::class, 'getCategoryData']);
Route::get('get_piller_data/{floor_id}', [QrMapController::class, 'getPillerData']);
Route::get('/processed-image', [QrMapController::class, 'showProcessedImage']);
Route::get('interconnection', [QrMapController::class, 'getInterconnectionData'])->name('interconnection');
Route::get('/map', [QrMapController::class, 'showMap']);
Route::get('/download-qr/{site_id}/{floor_id}', [QrMapController::class, 'downloadQr']);

// find my car 
Route::get('dashboard/findmycarsearch/{site_id}/{number}', [QrMapController::class, 'findMyCarSearch']);
Route::get('get_map_result/{site_id}/{floor_id}/{location}/{sensor}', [QrMapController::class, 'getMapDataPost2']);


Route::get('/calculate-route/{start}/{end}', [QrMapController::class, 'calculateRoute']);


//auth
Route::get('login', [authController::class, 'showLoginForm'])->name('login');
Route::post('login', [authController::class, 'login']);
Route::post('logout', [authController::class, 'logout'])->name('logout');

// Route::get('register', [authController::class, 'showRegistrationForm'])->name('register');
// Route::post('register', [authController::class, 'register']);

Route::get('noaccess', [authController::class, 'noaccess']);

Route::get('test', [testController::class, 'test3']);
Route::post('toggleStatus', [testController::class, 'toggleStatus'])->name('toggleStatus');
Route::get('historydata/{zonal}/{slot}', [testController::class, 'historyData']);

Route::get('dashboard/testchart', [historyController::class, 'testChart']);


Route::get('dashboard/login', [dashboardController::class, 'dashboardLogin']);
Route::post('dashboard/login', [dashboardController::class, 'dashboardLoginPost']);
Route::get('dashboard/logout', [dashboardController::class, 'dashboardLogout'])->name('dashboard/logout');


// notification
Route::get('/send-notification', [notificationController::class, 'sendNotification']);
