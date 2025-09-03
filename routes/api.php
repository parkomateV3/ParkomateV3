<?php

use App\Http\Controllers\apiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('v1/occupancy', [apiController::class, 'getCompleteSiteData']);
Route::get('getDisplay', [apiController::class, 'getDisplayOld']);
Route::post('v1/get-display-data', [apiController::class, 'getDisplay']);
Route::post('v1/get-sensors-data', [apiController::class, 'getSensors']);
Route::post('v1/update-sensor-status', [apiController::class, 'getSensorsStatus']);

// eecs
Route::post('v1/update-eecs-count', [apiController::class, 'updateCount']);
Route::post('v1/get_device_data', [apiController::class, 'getDeviceData']);


// reservation
Route::post('v1/get_reservation_data', [apiController::class, 'getReservationData']);


Route::get('v1/update_vehicle_count', [apiController::class, 'updateVehicleCount']);
Route::get('v1/get_vehicle_count', [apiController::class, 'getVehicleCount']);


Route::post('v1/upload_image', [apiController::class, 'uploadImage']);
Route::post('v1/test_api', [apiController::class, 'testApi']);
// Route::get('getDisplaySymbol', [apiController::class, 'getDisplaySymbol']);

// image upload
Route::post('v1/images-upload', [apiController::class, 'imagesUpload']);