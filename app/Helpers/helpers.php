<?php

use App\Models\detection_type;
use App\Models\display_info;
use App\Models\eecs_device_info;
use App\Models\eecs_sensor_info;
use App\Models\floor_info;
use App\Models\reservation_device_info;
use App\Models\role_master;
use App\Models\sensor_data_logging;
use App\Models\sensor_info;
use App\Models\site_info;
use App\Models\symbol_info;
use App\Models\table_info;
use App\Models\zonal_info;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

function getSitename($id)
{
    $data = site_info::where('site_id', $id)->first();
    return $data->site_name;
}

function getSiteNameWithFloorId($id)
{
    $data = floor_info::where('floor_id', $id)->first();
    return $data->site_id;
}

function getSiteAdImage($id)
{
    $data = site_info::where('site_id', $id)->first();
    return $data->ad_image;
}

function getFloorname($id)
{
    $data = floor_info::where('floor_id', $id)->first();
    if ($data) {
        return $data->floor_name;
    }
    return "";
}

function getZonalname($id)
{
    $data = zonal_info::where('zonal_id', $id)->first();
    return $data->zonal_name;
}

function getRoleName($id)
{
    $data = role_master::find($id);
    return $data->role_name;
}

function getDisplayLocation($id)
{
    $data = display_info::where('display_id', $id)->first();
    return $data->location_of_the_display_on_site . " (" . $data->display_unique_no . ")";
}

function symbolBinaryData($id)
{
    $data = symbol_info::where('symbol_id', $id)->first();
    return $data->binary_data;
}

function symbolSize($id)
{
    $data = symbol_info::where('symbol_id', $id)->first();
    return $data->symbol_size;
}

function symbolImage($id)
{
    $data = symbol_info::where('symbol_id', $id)->first();
    return $data->symbol_img;
}

function symbolName($id)
{
    $data = symbol_info::where('symbol_id', $id)->first();
    return $data->symbol_name;
}

function getSensorId($id)
{
    $data = sensor_info::where('sensor_unique_no', $id)->first();
    return $data->sensor_id;
}

function getTodayHoursData()
{
    // Get the current time
    $currentTime = Carbon::now()->endOfDay();
    // $currentTime = Carbon::now();

    // Calculate the range of hours
    $currentHour = (int)$currentTime->format('H');

    $hoursArray24H = array_map(function ($hour) {
        return str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
    }, range(0, $currentHour));

    $hoursArray12H = array_map(function ($hour) {
        return Carbon::createFromTime($hour)->format('g:i A');
    }, range(0, $currentHour));

    return [
        'hoursArray24H' => $hoursArray24H,
        'hoursArray12H' => $hoursArray12H,
    ];
}

function getZonalIdWithNo($no)
{
    // $data = zonal_info::where('zonal_unique_no', $no)->first();
    // return $data->zonal_id;

    $data = zonal_info::get();
    $matchedData = null;
    if (empty($data)) {
        return 0;
    }
    foreach ($data as $d) {
        $zonalArr = explode(',', $d->zonal_unique_no);
        if (in_array($no, $zonalArr)) {
            // $array = $zonalArr;
            $matchedData = $d->zonal_id;
        }
    }
    if ($matchedData == null) {
        return 0;
    }
    return $matchedData;
}

function getSensorNoWithId($id)
{
    $data = sensor_info::where('sensor_id', $id)->first();
    return $data->sensor_unique_no;
}

function getSensorNameNo($id)
{
    $data = sensor_info::where('sensor_id', $id)->first();
    return $data->sensor_name . " (" . $data->sensor_unique_no . ")";
}

function getEECSSensorNameNo($id)
{
    $data = eecs_sensor_info::where('id', $id)->first();
    return $data->sensor_name . " (" . $data->sensor_number . ")";
}

function getZonalNameNo($id)
{
    $data = zonal_info::where('zonal_id', $id)->first();
    return $data->zonal_name . " (" . $data->zonal_unique_no . ")";
}

function getTableName($id)
{
    $data = table_info::where('table_id', $id)->first();
    return $data->table_name;
}

function getSitePillerNames($site_id)
{
    $data = floor_info::where('site_id', $site_id)->get();
    $pillerNames = [];

    foreach ($data as $d) {
        $pillers = $d->piller_name;
        if ($pillers != null) {
            $pillers = json_decode($pillers, true);
            foreach ($pillers as $key => $value) {
                $items = explode(',', $value); // Split by comma
                // dd($items);
                $pillerNames = array_merge($pillerNames, $items);
            }
        }
    }
    $pillerNames = array_unique($pillerNames);
    sort($pillerNames);
    return $pillerNames;
}

function getSiteAllCategories($site_id)
{
    $data = floor_info::where('site_id', $site_id)->get();
    $allCategories = [];
    foreach ($data as $d) {
        $pillers = $d->piller_name;
        if ($pillers != null) {
            $pillers = json_decode($pillers, true);
            $categories = array_keys($pillers);
            $allCategories = array_merge($allCategories, $categories);
        }
    }
    $allCategories = array_unique($allCategories);
    return $allCategories;
}

function getSitePillerNamesFloor($floor_id)
{
    $data = floor_info::where('floor_id', $floor_id)->first();
    $filters = json_decode($data->piller_name, true);

    $allLocations = collect($filters)
        ->flatMap(function ($category) {
            return collect($category)->flatMap(function ($item) {
                return explode(',', reset($item));
            });
        })
        ->values()
        ->toArray();

    sort($allLocations);
    return $allLocations;

    // foreach ($data as $d) {
    //     $pillers = $d->piller_name;
    //     if ($pillers != null) {
    //         $pillers = json_decode($pillers, true);
    //         foreach ($pillers as $key => $value) {
    //             $items = explode(',', $value); // Split by comma
    //             // dd($items);
    //             $pillerNames = array_merge($pillerNames, $items);
    //         }
    //     }
    // }
    // $pillerNames = array_unique($pillerNames);
    // sort($pillerNames);
    // return $pillerNames;
}

function getFloorData($site_id, $destinationLocation, $interCoordinate)
{
    $floorData = floor_info::where('site_id', $site_id)->get();
    $piller = null;
    $destinationKey = null; // Variable to store matched key
    $finalData = null; // Variable to store matched key
    foreach ($floorData as $floor) {
        if ($floor->piller_name != null) {

            $piller = $floor->piller_name;
            $piller = json_decode($piller, true);
            $piller = collect($piller)
                ->flatMap(function ($category) {
                    return collect($category)->mapWithKeys(function ($item) {
                        $key = key($item);
                        $value = explode(',', $item[$key]);
                        return [$key => $value];
                    });
                })
                ->toArray();

            foreach ($piller as $key => $value) {
                if (in_array($destinationLocation, $value)) {
                    $destinationKey = $key;
                    $coordinates = $floor->piller_coordinates;
                    $coordinates = json_decode($coordinates, true);
                    $symbol_size = explode(',', $floor->symbol_size);
                    $width = $symbol_size[0];
                    $height = $symbol_size[1];
                    $radius = $symbol_size[2];
                    $spacing = $symbol_size[3];
                    $max_distance = $symbol_size[4];
                    foreach ($coordinates as $key => $value) {
                        if ($key == $destinationKey) {
                            $coordinate = explode(',', $value);
                            break;
                        }
                    }
                    foreach ($coordinates as $key => $value) {
                        if ($key == $interCoordinate) {
                            $icoordinate = explode(',', $value);
                            break;
                        }
                    }
                    $finalData[] = [
                        'floor_id' => $floor->floor_id,
                        'destinationKey' => $destinationKey,
                        'coordinate' => $coordinate,
                        'icoordinate' => $icoordinate,
                        'width' => $width,
                        'height' => $height,
                        'radius' => $radius,
                        'spacing' => $spacing,
                        'max_distance' => $max_distance,
                        'interconnect_location_symbol' => $floor->interconnect_location_symbol,
                        'destination_location_symbol' => $floor->destination_location_symbol,
                        'floor_image' => $floor->floor_image,
                        'piller_coordinates' => $floor->piller_coordinates
                    ];
                    break;
                }
                // if (in_array($location, $value)) {
                //     $currentKey = $key;
                //     // break;
                // }
            }
        }
    }

    return $finalData;
    // return width hright 
}

function getSensorStatus($id)
{
    $sensorData = sensor_info::where('sensor_id', $id)->first();
    $data = sensor_data_logging::where('sensor', $sensorData->sensor_unique_no)->first();
    return $data->status;
}

function getSensorNumber($id)
{
    $sensorData = sensor_info::where('sensor_id', $id)->first();
    $data = sensor_data_logging::where('sensor', $sensorData->sensor_unique_no)->first();
    return $data->number;
}

function getPrice($value)
{
    $flag = 1;
    $lesser = null;
    $site_id = Auth::user()->site_id;
    $siteData = site_info::where('site_id', $site_id)->first();
    $timeslots = json_decode($siteData->financial_model);
    if (empty($timeslots)) {
        // It's empty
        return 0;
    }
    foreach ($timeslots as $item) {
        foreach ($item as $min => $price) {
            if ($flag) {
                $lesser = $price;
                $flag = 0;
            }
            if ($min <= $value) {
                $lesser = $price;
            } else {
                break; // Since array is sorted, we can stop here
            }
        }
    }

    return $lesser;
    // return $lesser;
}

function getPriceWithSite($value, $site_id)
{
    // $flag = 1;
    // $lesser = null;
    // // $site_id = Auth::user()->site_id;
    // $siteData = site_info::where('site_id', $site_id)->first();
    // $timeslots = json_decode($siteData->financial_model, true);
    // dd($timeslots);
    // if (empty($timeslots)) {
    //     // It's empty
    //     return 0;
    // }
    // foreach ($timeslots as $item) {
    //     foreach ($item as $min => $price) {
    //         if ($flag) {
    //             $lesser = $price;
    //             $flag = 0;
    //         }
    //         if ($min <= $value) {
    //             $lesser = $price;
    //         } else {
    //             break; // Since array is sorted, we can stop here
    //         }
    //     }
    // }

    // return $lesser;


    $siteData = site_info::where('site_id', $site_id)->first();

    if (!$siteData || !$siteData->financial_model) {
        return 0;
    }

    $timeslots = json_decode($siteData->financial_model, true); // decode as array
    $day = now()->format('l'); // e.g., "Monday"

    if (!isset($timeslots[$day])) {
        return 0;
    }

    $daySlots = $timeslots[$day];

    // Make sure slots are sorted by minute key (just in case)
    ksort($daySlots);

    $lesser = 0;
    $flag = true;

    foreach ($daySlots as $min => $price) {
        if ($flag) {
            $lesser = $price;
            $flag = false;
        }
        if ($min <= $value) {
            $lesser = $price;
        } else {
            break; // since the array is sorted, we can break early
        }
    }

    return $lesser;
}

function getZonalId($id)
{
    // $data = zonal_info::get();
    // $matchedData = null;
    // if (empty($data)) {
    //     return 0;
    // }
    // foreach ($data as $d) {
    //     $zonalArr = json_decode($d->zonal_unique_no);
    //     $matchedData = $zonalArr;
    //     // if(in_array($id, $zonalArr)){
    //     //     $matchedData[] = $d;
    //     // }
    // }
    // // $getzonals = json_decode($data->zonal_unique_no);
    // // dd($getzonals);
    // return $matchedData;
    // return $data->zonal_id;


    $data = zonal_info::get();
    $matchedData = null;
    if (empty($data)) {
        return 0;
    }
    foreach ($data as $d) {
        $zonalArr = explode(',', $d->zonal_unique_no);
        if (in_array($id, $zonalArr)) {
            // $array = $zonalArr;
            $matchedData = $d->zonal_id;
        }
    }
    if ($matchedData == null) {
        return 0;
    }
    return $matchedData;
}

function getPillerData2($floor_id)
{
    // return $selectedCategory;
    $data = floor_info::where('floor_id', $floor_id)->first();
    $allLocations = [];
    if ($data) {
        $pillers = $data->piller_name;
        if ($pillers != null) {
            $pillers = json_decode($pillers, true);
            // ✅ Check if selected category exists
            if (isset($pillers['Parking Pillers'])) {
                $locations = collect($pillers['Parking Pillers'])
                    ->flatMap(function ($group) {
                        return explode(',', reset($group));
                    })
                    ->values()
                    ->toArray();

                $allLocations = array_merge($allLocations, $locations);
            }
        }
        $allLocations = array_unique($allLocations);
        sort($allLocations);
    }
    return $allLocations;
}

function getTypeName($id)
{
    $data = detection_type::find($id);
    if ($data) {
        return $data->type;
    }
    return null; // or handle the case where the type is not found
}

function getDeviceId($id)
{
    $data = eecs_device_info::find($id);
    if ($data) {
        return $data->device_id;
    }
    return null; // or handle the case where the type is not found
}

function getDeviceName($id)
{
    $data = eecs_device_info::find($id);
    if ($data) {
        return $data->device_name;
    }
    return null; // or handle the case where the type is not found
}

function getSlotStatus($id)
{
    $data = reservation_device_info::find($id);
    if ($data) {
        return $data->status;
    }
    return null; // or handle the case where the type is not found
}

function getSlotName($id)
{
    $data = reservation_device_info::find($id);
    if ($data) {
        return $data->reservation_name;
    }
    return null; // or handle the case where the type is not found
}

function getSiteType($id)
{
    $data = site_info::where('site_id', $id)->first();
    if ($data) {
        return $data->site_type_of_product;
    }
    return null; // or handle the case where the type is not found
}

function getSiteFloorData($id)
{
    $data = floor_info::where('site_id', $id)->get();
    return $data;
}

function getFloorsData()
{
    $site_id = Auth::user()->site_id;
    $data = floor_info::where('site_id', $site_id)->get();
    if ($data->isEmpty()) {
        return []; // Return an empty array if no data is found
    }
    return $data;
}
