<?php

namespace App\Http\Controllers;

use App\Models\floor_info;
use App\Models\overnight_occupancy;
use App\Models\sensor_data_logging;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class floorMapController extends Controller
{
    public function test($floor_id)
    {
        $access = Auth::user()->access;
        $accessArray = explode(',', $access);
        $checkAccess = "floor-map";
        $flag = 0;
        if (in_array($checkAccess, $accessArray)) {
            $flag = 1;
        }
        if ($flag == 0) {
            return redirect('noaccess');
        }

        $data = floor_info::where('floor_id', $floor_id)->first();
        if ($data == null || $data == '' || $data->floor_map_coordinate == null || $data->floor_map_coordinate == '') {
            return "Data not available";
        } else {

            $dimension = explode(',', $data->floor_image_sensor_mapping_dimenssion);
            $car_scale = explode(',', $data->car_scale);
            $floor_image = $data->floor_image;
            // dd($dimension);
            $coordinates = json_decode($data->floor_map_coordinate, true);
            $status = null;
            foreach ($coordinates as &$c) {
                $status = getSensorStatus($c['sensor_id']);
                $c['status'] = $status;
            }
            unset($c);
            return view('floormap.index3', compact('coordinates', 'car_scale', 'dimension', 'floor_image'));
        }
    }

    public function getFloorData($floorId)
    {
        $floor_name = getFloorname($floorId);
        $floorData = sensor_data_logging::where('floor_id', $floorId)->get();
        // dd($floorData);
        $total = 0;
        $available = 0;
        $occupied = 0;
        $data = null;
        if ($floorData->isNotEmpty()) {
            foreach ($floorData as $f) {
                if ($f->status == 1 || $f->status == 2 || $f->status == 4) {
                    $occupied++;
                }
                if ($f->status == 0) {
                    $available++;
                }
            }
            $total = $available + $occupied;
            // $percentage = ($occupied / $total) * 100;
            // $percentage = $total > 0 ? ($occupied / $total) * 100 : 0;
            // $percentage = number_format($percentage, 2);
        } else {
            $data = [
                'data' => 0,
            ];
        }
        $datafloor = floor_info::where('floor_id', $floorId)->first();
        if ($datafloor == null || $datafloor == '' || $datafloor->floor_map_coordinate == null || $datafloor->floor_map_coordinate == '') {
            $data = [
                'data' => 0,
            ];
        } else {
            $coordinates = json_decode($datafloor->floor_map_coordinate, true);
            $status = null;
            foreach ($coordinates as &$c) {
                $status = getSensorStatus($c['sensor_id']);
                $number = getSensorNumber($c['sensor_id']);
                if ($number != null && $number != '' && $number != 'NA') {
                    $c['label'] = $c['label'] . " - " . $number;
                }
                $c['status'] = $status;
            }
            unset($c);
        }

        $data = [
            'floor_name' => $floor_name,
            'total' => $total,
            'available' => $available,
            'occupied' => $occupied,
            'coordinates' => $coordinates,
            'data' => 1,
        ];

        return $data;
    }

    public function index($floor_id)
    {
        $site_id = Auth::user()->site_id;
        $siteType = getSiteType($site_id);
        if ($siteType == "eecs" || $siteType == "slot_reservation") {
            return redirect('dashboard/404');
        }
        $active = "floor-map";
        $access = Auth::user()->access;
        $accessArray = explode(',', $access);
        $checkAccess = "floor-map";
        $flag = 0;
        if (in_array($checkAccess, $accessArray)) {
            $flag = 1;
        }
        if ($flag == 0) {
            return redirect('noaccess');
        }

        $data = floor_info::where('floor_id', $floor_id)->first();
        if ($data == null || $data == '' || $data->floor_map_coordinate == null || $data->floor_map_coordinate == '') {
            return "Data not available";
        } else {

            $dimension = explode(',', $data->floor_image_sensor_mapping_dimenssion);
            $car_scale = explode(',', $data->car_scale);
            $floor_image = $data->floor_image;
            $label_size = $data->label_properties;
            // dd($dimension);
            $coordinates = json_decode($data->floor_map_coordinate, true);
            $status = null;
            foreach ($coordinates as &$c) {
                $status = getSensorStatus($c['sensor_id']);
                $number = getSensorNumber($c['sensor_id']);
                if ($number != null && $number != '' && $number != 'NA') {
                    $c['label'] = $c['label'] . " - " . $number;
                }
                // $c['number'] = $number;
                $c['status'] = $status;
            }
            unset($c);
            return view('floormap.floormap', compact('coordinates', 'car_scale', 'dimension', 'floor_image', 'active', 'floor_id', 'label_size'));
        }
        // return view('floormap.floormap', compact('active'));
    }

    public function getOvernightFloorData($floor_id)
    {
        $site_id = Auth::user()->site_id;
        $siteType = getSiteType($site_id);
        if ($siteType == "eecs" || $siteType == "slot_reservation") {
            return redirect('dashboard/404');
        }
        $active = "floor-map";
        $access = Auth::user()->access;
        $accessArray = explode(',', $access);
        $checkAccess = "floor-map";
        $flag = 0;
        if (in_array($checkAccess, $accessArray)) {
            $flag = 1;
        }
        if ($flag == 0) {
            return redirect('noaccess');
        }

        if (now()->greaterThan(now()->setTime(8, 0))) {
            $overnightData = overnight_occupancy::whereDate('created_at', Carbon::today())->where('floor_id', $floor_id)->first();
        } else {
            $overnightData = overnight_occupancy::whereDate('created_at', Carbon::yesterday())->where('floor_id', $floor_id)->first();
        }
        // dd($overnightData);
        // $overnightData = overnight_occupancy::where('floor_id', $floor_id)->first();
        $data = floor_info::where('floor_id', $floor_id)->first();
        if ($data == null || $data == '' || $data->floor_map_coordinate == null || $data->floor_map_coordinate == '') {
            return "Data not available";
        } else {
            $overDaysSensors = [];
            $overnightSensors = empty($overnightData) ? [] : json_decode($overnightData->data, true);
            $dimension = explode(',', $data->floor_image_sensor_mapping_dimenssion);
            $car_scale = explode(',', $data->car_scale);
            $floor_image = $data->floor_image;
            $label_size = $data->label_properties;
            // dd($dimension);
            $coordinates = json_decode($data->floor_map_coordinate, true);
            $status = null;
            foreach ($coordinates as &$c) {
                $status = getSensorStatus($c['sensor_id']);
                if ($status == 1) {
                    $sensorNumber = getSensorNoWithId($c['sensor_id']);
                    $prevZero = DB::table('floor_data_by_minutes')
                        ->where('floor_id', $floor_id)
                        ->where('created_at', '<', Carbon::now())
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.\"{$sensorNumber}\"')) = '0'")
                        // ->whereDate('created_at', '<', $date)
                        // ->whereBetween('created_at', '<', [$startOfToday, $currentDateTime])
                        ->orderBy('created_at', 'desc')
                        ->first();
                    if ($prevZero) {
                        $minutesDifference = Carbon::now()
                            ->diffInMinutes(Carbon::parse($prevZero->created_at));
                        if ($minutesDifference > 1440) {
                            $overDaysSensors[] = $c['sensor_id'];
                        }
                    }
                    // dd($prevZero);
                }
                $c['status'] = $status;
            }
            unset($c);
            // dd($overDaysSensors);
            return view('floormap.overnightfloormap', compact('coordinates', 'car_scale', 'dimension', 'floor_image', 'active', 'floor_id', 'overnightSensors', 'overDaysSensors', 'label_size'));
        }
    }
}
