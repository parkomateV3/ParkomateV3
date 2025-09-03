<?php

namespace App\Http\Controllers;

use App\Models\AccessPoint;
use App\Models\display_info;
use App\Models\displaydata;
use App\Models\floor_data_by_day;
use App\Models\floor_data_by_hour;
use App\Models\floor_data_by_minute;
use App\Models\floor_info;
use App\Models\sensor_data_logging;
use App\Models\sensor_info;
use App\Models\sensor_reservation;
use App\Models\site_info;
use App\Models\user_site_mapping;
use App\Models\zonal_info;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class TestParkingController extends Controller
{
    //testFloorMapDetails

    public function testFloorMapDetails($floor_id)
    {
        $active = "floor-map";

        $data = floor_info::where("floor_id", $floor_id)->first();
        if (
            $data == null ||
            $data == "" ||
            $data->floor_map_coordinate == null ||
            $data->floor_map_coordinate == ""
        ) {
        } else {
            $dimension = explode(
                ",",
                $data->floor_image_sensor_mapping_dimenssion
            );
            $car_scale = explode(",", $data->car_scale);
            $floor_image = $data->floor_image_sensor_mapping;
            $label_size = $data->label_properties;
            $coordinates = json_decode($data->floor_map_coordinate, true);
            $status = null;
            foreach ($coordinates as &$c) {
                $status = 1;
                $number = 1;
                if ($number != null || $number != "") {
                    $c["label"] = $c["label"] . " - " . $number;
                }
                $c["status"] = $status;
            }
            unset($c);
            return view(
                "floormap.testFloorMapDetails",
                compact(
                    "coordinates",
                    "car_scale",
                    "dimension",
                    "floor_image",
                    "active",
                    "floor_id",
                    "label_size"
                )
            );
        }
        // return view('floormap.floormap', compact('active'));
    }

}
