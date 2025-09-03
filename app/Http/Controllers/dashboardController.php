<?php

namespace App\Http\Controllers;

use App\Jobs\SendSensorMail;
use App\Models\display_info;
use App\Models\eece_data_logging_floor;
use App\Models\eece_data_logging_site;
use App\Models\eecs_device_info;
use App\Models\email_log;
use App\Models\floor_data_by_hour;
use App\Models\floor_info;
use App\Models\overnight_occupancy;
use App\Models\sensor_data_logging;
use App\Models\site_data_by_hour;
use App\Models\site_data_by_minute;
use App\Models\site_info;
use App\Models\table_entry;
use App\Models\table_info;
use App\Models\User;
use App\Models\zonal_info;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class dashboardController extends Controller
{
    public function test()
    {

        $siteData = site_info::get();
        $floorData = floor_info::get();
        $datetime = Carbon::now()->format('Y-m-d H:i:s');

        foreach ($siteData as $site) {
            $flag = 0;
            $checkemptydata = sensor_data_logging::where('site_id', $site['site_id'])->latest('updated_at')->first();
            if ($checkemptydata) {
                $flag = 1;
                if (Carbon::now()->diffInMinutes($checkemptydata->updated_at) >= 15) {
                    $flag = 0;
                }
            }

            if ($flag) {
                $sensorData = [];
                $dataAnalysis = [];
                $occupied = 0;
                $available = 0;
                $red = 0;
                $green = 0;
                $blue = 0;
                $magenta = 0;
                $yellow = 0;
                $cyan = 0;
                $white = 0;
                $sensorinfo = sensor_data_logging::where('site_id', $site['site_id'])->get();
                foreach ($sensorinfo as $sensor) {
                    $sensorData[$sensor->sensor] = $sensor->status;
                    if ($sensor->status == 1 || $sensor->status == 2) {
                        $occupied++;
                    }
                    if ($sensor->status == 0) {
                        $available++;
                    }
                    if ($sensor->color != null) {
                        // switch ($sensor->color) {
                        if ($sensor->color == 'red')
                            $red++;
                        if ($sensor->color == 'green')
                            $green++;
                        if ($sensor->color == 'blue')
                            $blue++;
                        if ($sensor->color == 'magenta')
                            $magenta++;
                        if ($sensor->color == 'yellow')
                            $yellow++;
                        if ($sensor->color == 'cyan')
                            $cyan++;
                        if ($sensor->color == 'white')
                            $white++;
                        // }
                    }
                }
                $dataAnalysis = [
                    "Available" => $available,
                    "Occupied" => $occupied,
                    "Green" => $green,
                    "Red" => $red,
                    "Blue" => $blue,
                    "Magenta" => $magenta,
                    "Yellow" => $yellow,
                    "Cyan" => $cyan,
                    "White" => $white,
                ];
                // Convert to JSON format if needed
                $jsonDataAnalysis = json_encode($dataAnalysis);

                $data = json_encode($sensorData);
                $dataStore = [
                    'site_id' => $site['site_id'],
                    'data' => $data,
                    'date_time' => $datetime,
                    // 'total_occupied' => $occupied,
                    // 'total_available' => $available,
                    'data_analysis' => $jsonDataAnalysis,
                ];
                site_data_by_minute::create($dataStore);
            }
        }
    }
    public function test4()
    {
        // $return = getPriceWithSite(366, 6);
        // dd($return);

        // 1. Get today’s date and all floor IDs with data today
        // $today = Carbon::today();
        // $floorIds = DB::table('floor_data_by_minutes')
        //     ->whereDate('created_at', $today)
        //     ->distinct()
        //     ->pluck('floor_id');
        // $allResults = [];

        // foreach ($floorIds as $floorId) {
        //     // 2. Retrieve today’s entries for this floor in time order
        //     $floorData = DB::table('floor_data_by_minutes')
        //         ->where('floor_id', $floorId)
        //         ->whereDate('created_at', $today)
        //         ->orderBy('created_at')
        //         ->get(['data', 'created_at']);

        //     if ($floorData->isEmpty()) {
        //         continue;
        //     }


        //     // 4. For each sensor on this floor, compute occupancy blocks
        //     foreach (array_keys(json_decode($floorData->first()->data, true)) as $sensorId) {
        //         // Determine initial status (0 or 1)
        //         $firstStatus = json_decode($floorData->first()->data, true)[$sensorId] ?? 0;
        //         $initialCount = 0;
        //         if ($firstStatus == 1) {
        //             // Find last time status was 0
        //             $prevZero = DB::table('floor_data_by_minutes')
        //                 ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.\"{$sensorId}\"')) = '0'")
        //                 ->whereDate('created_at', '<', \Carbon\Carbon::today())
        //                 ->orderBy('created_at', 'desc')
        //                 ->first();
        //             // dd($prevZero);
        //             if ($prevZero) {
        //                 $initialCount = Carbon::parse($floorData->first()->created_at)
        //                     ->diffInMinutes(Carbon::parse($prevZero->created_at));
        //             }
        //         }
        //         // dd($floorData);
        //         // Count continuous 1-blocks
        //         $durations = [];
        //         $current = $initialCount;
        //         foreach ($floorData as $entry) {
        //             $status = json_decode($entry->data, true)[$sensorId] ?? 0;
        //             if ($status == 1) {
        //                 $current++;
        //             } else {
        //                 if ($current > 0) {
        //                     $durations[] = $current;
        //                     $current = 0;
        //                 }
        //             }
        //         }
        //         // dd($durations);

        //         // if ($current > 0) {
        //         //     $durations[] = $current;
        //         // }
        //         $allResults[] = $durations;
        //         // 5. Convert durations to prices
        //         $siteId = getSiteNameWithFloorId($floorId);
        //         // dd($siteId);
        //         $prices = array_map(fn($min) => getPriceWithSite($min, $siteId), $durations);
        //         // dd($prices);
        //         $allResults[] = $prices;

        //         // $allResults[$sensorId] = [
        //         //     'durations' => $durations,
        //         //     'prices'    => $prices,
        //         // ];
        //     }

        //     // 6. Save results in overnight_occupancy (as JSON data)
        //     // DB::table('overnight_occupancy')->insert([
        //     //     'floor_id'   => $floorId,
        //     //     'date'       => $today,
        //     //     'data'       => json_encode($allResults),
        //     //     'created_at' => now(),
        //     //     'updated_at' => now(),
        //     // ]);
        // }
        // dd($allResults);

        $date = Carbon::today()->toDateString();
        $start = Carbon::today()->format('Y-m-d 00:00:00');
        $end = Carbon::today()->format('Y-m-d 07:59:59');
        $floorData = DB::table('floor_data_by_minutes')
            // ->select('site_id', DB::raw('MAX(id) as id'))
            // ->where('floor_id', 15)
            // ->whereBetween('created_at', [$start, $end])
            ->whereDate('created_at', $date)
            ->get()
            ->groupBy('floor_id');
        // dd($floorData);
        $allData = [];
        foreach ($floorData as $floorId => $entries) {
            $sensorTime = []; // Store time occupied per sensor
            $siteId = 0;
            foreach ($entries as $entry) {
                $siteId = $entry->site_id;
                if ($entry->data != "[]") {

                    $currentStatus = json_decode($entry->data, true);

                    foreach ($currentStatus as $sensorId => $status) {
                        $sensorTime[] = [$sensorId, $status];
                    }
                }
            }

            $groupedData = [];

            foreach ($sensorTime as $entry) {
                $sensorId = (string) $entry[0]; // Convert to string to handle numeric IDs consistently
                $status = $entry[1];

                // Group by sensor ID
                $groupedData[$sensorId][] = $status;
            }

            $sensors = [];
            if (!empty($groupedData)) {
                $result = [];
                foreach ($groupedData as $sensorId => $statuses) {
                    $initialCount = 0;
                    $firstStatus = reset($statuses);
                    if ($firstStatus == 1) {
                        $prevZero = DB::table('floor_data_by_minutes')
                            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.\"{$sensorId}\"')) = '0'")
                            ->whereDate('created_at', '<', $date)
                            ->orderBy('created_at', 'desc')
                            ->first();

                        $floorFirstData = DB::table('floor_data_by_minutes')
                            ->where('floor_id', $floorId)
                            ->whereDate('created_at', $date)
                            // ->orderBy('created_at', 'desc')
                            ->first();
                        if ($prevZero) {
                            $initialCount = Carbon::parse($floorFirstData->created_at)
                                ->diffInMinutes(Carbon::parse($prevZero->created_at)) - 1;
                        }
                        dd($initialCount);
                    }
                    // dd($firstStatus);
                    $count = $initialCount;
                    foreach ($statuses as $status) {
                        // amount count
                        if ($status == 1) {
                            $count++;
                        } else {
                            if ($count > 0) {
                                $result[] = $count;
                                $count = 0;
                            }
                        }
                    }
                }
            }
            $prices = [];
            foreach ($result as $value) {
                $prices[] = getPriceWithSite($value, $siteId);
            }
            // dd($prices);
            // $dataStore = [
            //     'floor_id' => (int) $floorId,
            //     'site_id' => (int) $siteId,
            //     'data' => json_encode($sensors),
            // ];
            $allData[] = $prices;
            // overnight_occupancy::create($dataStore);
        }
        dd($allData);
    }

    public function test3()
    {
        // $displayno = 167984924398472;
        $displayData = display_info::all();
        if (!empty($displayData)) {
            foreach ($displayData as $display) {
                $difference = Carbon::now()->diffInMinutes($display->updated_at);
                if ($difference > 15) {
                    $emailLogZ = email_log::whereDate('created_at', Carbon::today())->where('sensor_id', $display->display_unique_no)->latest('created_at')->first();

                    if ($emailLogZ) {
                        $diffInMinutesZ = Carbon::now()->diffInMinutes($emailLogZ->created_at);
                        // Now you can check like:
                        if ($diffInMinutesZ > 120) {
                            // $email = "ajay.ladkat@parkomate.com";
                            $email = "maheshyangandul@gmail.com";
                            $mailData = [
                                'from_email' => $email,
                                'from_name' => "Display Error",
                                'subject' => "Display Error",
                                'site_name' => 1,
                                'floor_name' => 1,
                                'zonal_name' => 1,
                                'sensor_unique_no' => 1,
                                'sensor_name' => 1,
                                'sensor_id' => $display->display_unique_no,
                                'status' => 1,
                                'date' => Carbon::now(),
                                'device' => 'displayapi'
                            ];
                            SendSensorMail::dispatch($email, $mailData);
                        }
                    } else {
                        // $email = "ajay.ladkat@parkomate.com";
                        $email = "maheshyangandul@gmail.com";
                        $mailData = [
                            'from_email' => $email,
                            'from_name' => "Display Error",
                            'subject' => "Display Error",
                            'site_name' => 1,
                            'floor_name' => 1,
                            'zonal_name' => 1,
                            'sensor_unique_no' => 1,
                            'sensor_name' => 1,
                            'sensor_id' => $display->display_unique_no,
                            'status' => 1,
                            'date' => Carbon::now(),
                            'device' => 'displayapi'
                        ];
                        SendSensorMail::dispatch($email, $mailData);
                    }
                }
            }
        }

        $siteData = site_info::all();
        if (!empty($siteData)) {
            foreach ($siteData as $site) {
                $sensorLogData = sensor_data_logging::where('site_id', $site->site_id)->latest('updated_at')->first();
                if ($sensorLogData) {
                    $difference = Carbon::now()->diffInMinutes($sensorLogData->updated_at);
                    if ($difference > 15) {
                        $emailLogZ = email_log::whereDate('created_at', Carbon::today())->where('sensor_id', $sensorLogData->sensor)->latest('created_at')->first();
                        if ($emailLogZ) {
                            $diffInMinutesZ = Carbon::now()->diffInMinutes($emailLogZ->created_at);
                            // Now you can check like:
                            if ($diffInMinutesZ > 120) {
                                // $email = "ajay.ladkat@parkomate.com";
                                $email = "maheshyangandul@gmail.com";
                                $mailData = [
                                    'from_email' => $email,
                                    'from_name' => "Site Error",
                                    'subject' => "Site Error",
                                    'site_name' => getSitename($sensorLogData->site_id),
                                    'floor_name' => 1,
                                    'zonal_name' => 1,
                                    'sensor_unique_no' => 1,
                                    'sensor_name' => 1,
                                    'sensor_id' => $sensorLogData->sensor,
                                    'status' => 1,
                                    'date' => Carbon::now(),
                                    'device' => 'site_error'
                                ];
                                SendSensorMail::dispatch($email, $mailData);
                            }
                        } else {
                            // $email = "ajay.ladkat@parkomate.com";
                            $email = "maheshyangandul@gmail.com";
                            $mailData = [
                                'from_email' => $email,
                                'from_name' => "Site Error",
                                'subject' => "Site Error",
                                'site_name' => getSitename($sensorLogData->site_id),
                                'floor_name' => 1,
                                'zonal_name' => 1,
                                'sensor_unique_no' => 1,
                                'sensor_name' => 1,
                                'sensor_id' => $sensorLogData->sensor,
                                'status' => 1,
                                'date' => Carbon::now(),
                                'device' => 'site_error'
                            ];
                            SendSensorMail::dispatch($email, $mailData);
                        }
                    }
                }
            }
        }

        // overnight_occupancy::truncate();
        // $start = Carbon::yesterday()->format('Y-m-d 00:00:00');
        // $end = Carbon::yesterday()->format('Y-m-d 07:59:59');
        // $floorData = DB::table('floor_data_by_minutes')
        //     // ->select('site_id', DB::raw('MAX(id) as id'))
        //     // ->where('floor_id', $floor_id)
        //     // ->whereBetween('created_at', [$start, $end])
        //     ->whereDate('created_at', Carbon::yesterday())
        //     ->get()
        //     ->groupBy('floor_id');
        // // dd($floorData);

        // foreach ($floorData as $floorId => $entries) {
        //     $sensorTime = []; // Store time occupied per sensor
        //     $siteId = 0;
        //     foreach ($entries as $entry) {
        //         $siteId = $entry->site_id;
        //         if ($entry->data != "[]") {

        //             $currentStatus = json_decode($entry->data, true);

        //             foreach ($currentStatus as $sensorId => $status) {
        //                 $sensorTime[] = [$sensorId, $status];
        //             }
        //         }
        //     }

        //     $groupedData = [];

        //     foreach ($sensorTime as $entry) {
        //         $sensorId = (string) $entry[0]; // Convert to string to handle numeric IDs consistently
        //         $status = $entry[1];

        //         // Group by sensor ID
        //         $groupedData[$sensorId][] = $status;
        //     }
        //     // dd($groupedData);
        //     $sensors = [];
        //     if (!empty($groupedData)) {
        //         foreach ($groupedData as $sensorId => $statuses) {
        //             // dd($statuses);
        //             $checkValues = [0, 2, 4]; // the values you want to check

        //             if (!empty(array_intersect($checkValues, $statuses))) {
        //             } else {
        //                 $sensorID = getSensorId($sensorId);
        //                 $sensors[] = $sensorID;
        //                 // echo $sensorId . "<br>";
        //             }
        //             // $count = 0;

        //             // foreach ($statuses as $status) {
        //             //     if ($status == 1) {
        //             //         $count++;
        //             //     } else {
        //             //         if ($count > 0) {
        //             //             $count = 0;
        //             //         }
        //             //     }
        //             // }

        //             // // Capture any final trailing sequence of 1s
        //             // if ($count > 0) {
        //             //     $sensorID = getSensorId($sensorId);
        //             //     $sensors[] = $sensorID;
        //             // }
        //         }
        //     }
        //     $dataStore = [
        //         'floor_id' => (int) $floorId,
        //         'site_id' => (int) $siteId,
        //         'data' => json_encode($sensors),
        //     ];
        //     overnight_occupancy::create($dataStore);
        // }


    }

    public function test2()
    {
        $id = '20780923';
        $data = zonal_info::get();
        // $data = zonal_info::where('zonal_id', 6)->first();
        $array = null;
        $matchedData = null;
        // $matchedData = explode(',', $data->zonal_unique_no);
        // dd($matchedData);
        if (empty($data)) {
            return 0;
        }
        foreach ($data as $d) {
            $zonalArr = explode(',', $d->zonal_unique_no);
            // $matchedData = $zonalArr;
            if (in_array($id, $zonalArr)) {
                $array = $zonalArr;
                $matchedData = $d->zonal_id;
            }
        }
        // $getzonals = json_decode($data->zonal_unique_no);
        // dd($getzonals);
        // return $matchedData;
        return response()->json(['zonal_id' => $matchedData, 'zonal_no' => $array]);
        // return $data->zonal_id;
    }

    public function dashboardTable()
    {
        $site_id = Auth::user()->site_id;
        $siteType = getSiteType($site_id);
        if ($siteType == "eecs" || $siteType == "slot_reservation") {
            return redirect('dashboard/404');
        }

        $HourData = getTodayHoursData();
        $hoursArray24H = $HourData['hoursArray24H'];
        $hoursArray12H = $HourData['hoursArray12H'];

        // $dataArray = site_data_by_minute::where('site_id', $site_id)
        //     ->whereDate('created_at', Carbon::today())
        //     ->get();

        // Initialize an array to hold hour-wise data
        $hourlyData = [];

        // foreach ($dataArray as $data) {
        //     echo $data->created_at . "<br>";
        // }
        // exit;

        // Get the current hour (up to now)
        $currentHour = Carbon::now()->format('H');
        $SiteData = [];
        $InData = [];
        $OutData = [];

        // Loop to initialize data for every hour from 00:00:00 to the current hour
        for ($hour = 1; $hour <= $currentHour; $hour++) {
            $startHour = Carbon::today()->setHour($hour)->format('Y-m-d H:i:s'); // Start of the hour
            $endHour = Carbon::today()->setHour($hour)->addHour()->format('Y-m-d H:i:s'); // End of the hour

            // Initialize counters
            $inCount = 0;
            $outCount = 0;

            // Track the previous status
            $previousStatus = null;

            $getData = DB::table('site_data_by_minutes')
                ->where('site_id', $site_id)
                ->whereDate('created_at', Carbon::today())
                ->whereBetween('created_at', [$startHour, $endHour])
                ->get();

            foreach ($getData as $entry) {
                if ($entry->data != "[]") {

                    $currentStatus = json_decode($entry->data, true);

                    if ($previousStatus) {
                        foreach ($currentStatus as $sensorId => $status) {
                            if (isset($previousStatus[$sensorId])) {
                                // Check for IN (0 → 1)
                                if ($status == "1" && $previousStatus[$sensorId] == "0") {
                                    $inCount++;
                                }

                                // Check for OUT (1 → 0)
                                if ($status == "0" && $previousStatus[$sensorId] == "1") {
                                    $outCount++;
                                }
                            }
                        }
                    }

                    // Update previous status
                    $previousStatus = $currentStatus;
                }
            }

            $InData[] = $inCount;
            $OutData[] = $outCount;

            // $timeRange = "{$startHour} to {$endHour}";

            // // Initialize the hourly data with empty arrays if no data
            // $hourlyData[$timeRange] = [
            //     'time_range' => $timeRange,
            //     'data' => [],
            // ];
        }



        // dd($InData);

        // Fetch hour-wise aggregated data
        // $dataArray = site_data_by_minute::select(
        //     DB::raw('HOUR(created_at) as hour'), // Extract the hour from created_at
        //     DB::raw('COUNT(*) as count'),       // Count the records for the hour
        // )
        //     ->where('site_id', $site_id)
        //     ->whereDate('created_at', Carbon::today()) // Ensure data is only for today
        //     ->groupBy(DB::raw('HOUR(created_at)')) // Group by the hour
        //     ->orderBy('hour', 'ASC') // Sort hours in ascending order
        //     ->get();


        // // Format the result for better readability
        // $hourlyData = $dataArray->map(function ($item) {
        //     return [
        //         'hour' => str_pad($item->hour, 2, '0', STR_PAD_LEFT) . ':00', // Format hour as HH:00
        //         'count' => $item->count,
        //     ];
        // });

        // // Output the result
        // return $hourlyData;

        $getData = DB::table('site_data_by_minutes')
            ->where('site_id', $site_id)
            ->whereDate('created_at', Carbon::today())
            ->get();

        $data = [];
        foreach ($getData as $entry) {
            $dataAnalysis = json_decode($entry->data_analysis, true);
            $Occupied = $dataAnalysis['Occupied'];

            $epochTime = (int)str_pad(strtotime($entry->created_at), 13, 0, STR_PAD_RIGHT);

            $data[] = [
                'date' => $epochTime,
                'value' => $Occupied,
            ];
        }


        // return view('dashboard.table', compact('hoursArray12H', 'InData', 'OutData'));
        return view('dashboard.table', compact('data'));
    }

    // public function index()
    // {
    //     $site_id = Auth::user()->site_id;

    //     $HourData = getTodayHoursData();
    //     $hoursArray24H = $HourData['hoursArray24H'];
    //     $hoursArray12H = $HourData['hoursArray12H'];

    //     // $dataArray = site_data_by_minute::where('site_id', $site_id)
    //     //     ->whereDate('created_at', Carbon::today())
    //     //     ->get();

    //     // Initialize an array to hold hour-wise data
    //     $hourlyData = [];

    //     // foreach ($dataArray as $data) {
    //     //     echo $data->created_at . "<br>";
    //     // }
    //     // exit;

    //     // Get the current hour (up to now)
    //     $currentHour = Carbon::now()->format('H');
    //     $SiteData = [];
    //     $InData = [];
    //     $OutData = [];

    //     // Loop to initialize data for every hour from 00:00:00 to the current hour
    //     for ($hour = 0; $hour <= $currentHour; $hour++) {
    //         $startHour = Carbon::today()->setHour($hour)->format('Y-m-d H:i:s'); // Start of the hour
    //         $endHour = Carbon::today()->setHour($hour)->addHour()->format('Y-m-d H:i:s'); // End of the hour
    //         $d1 = $startHour;
    //         $d2 = $endHour;

    //         // Initialize counters
    //         $inCount = 0;
    //         $outCount = 0;

    //         // Track the previous status
    //         $previousStatus = null;

    //         $getData = DB::table('site_data_by_minutes')
    //             ->where('site_id', $site_id)
    //             ->whereDate('created_at', Carbon::today())
    //             ->whereBetween('created_at', [$startHour, $endHour])
    //             ->get();

    //         foreach ($getData as $entry) {
    //             if ($entry->data != "[]") {

    //                 $currentStatus = json_decode($entry->data, true);

    //                 if ($previousStatus) {
    //                     foreach ($currentStatus as $sensorId => $status) {
    //                         if (isset($previousStatus[$sensorId])) {
    //                             // Check for IN (0 → 1)
    //                             if ($status == "1" && $previousStatus[$sensorId] == "0") {
    //                                 $inCount++;
    //                             }

    //                             // Check for OUT (1 → 0)
    //                             if ($status == "0" && $previousStatus[$sensorId] == "1") {
    //                                 $outCount++;
    //                             }
    //                         }
    //                     }
    //                 }

    //                 // Update previous status
    //                 $previousStatus = $currentStatus;
    //             }
    //         }

    //         $InData[] = $inCount;
    //         $OutData[] = $outCount;

    //         // $timeRange = "{$startHour} to {$endHour}";

    //         // // Initialize the hourly data with empty arrays if no data
    //         // $hourlyData[$timeRange] = [
    //         //     'time_range' => $timeRange,
    //         //     'data' => [],
    //         // ];
    //     }



    //     // dd($InData);

    //     // Fetch hour-wise aggregated data
    //     // $dataArray = site_data_by_minute::select(
    //     //     DB::raw('HOUR(created_at) as hour'), // Extract the hour from created_at
    //     //     DB::raw('COUNT(*) as count'),       // Count the records for the hour
    //     // )
    //     //     ->where('site_id', $site_id)
    //     //     ->whereDate('created_at', Carbon::today()) // Ensure data is only for today
    //     //     ->groupBy(DB::raw('HOUR(created_at)')) // Group by the hour
    //     //     ->orderBy('hour', 'ASC') // Sort hours in ascending order
    //     //     ->get();


    //     // // Format the result for better readability
    //     // $hourlyData = $dataArray->map(function ($item) {
    //     //     return [
    //     //         'hour' => str_pad($item->hour, 2, '0', STR_PAD_LEFT) . ':00', // Format hour as HH:00
    //     //         'count' => $item->count,
    //     //     ];
    //     // });

    //     // // Output the result
    //     // return $hourlyData;


    //     return view('dashboard.index', compact('hoursArray12H', 'InData', 'OutData'));
    // }

    public function index()
    {
        $access = Auth::user()->access;
        $accessArray = explode(',', $access);
        $checkAccess = "financial-model";
        $flag = 0;
        if (in_array($checkAccess, $accessArray)) {
            $flag = 1;
        }
        $site_id = Auth::user()->site_id;
        $floorData = floor_info::where('site_id', $site_id)->get();

        $checkFinancialModel = site_info::where('site_id', $site_id)->first();
        if (empty($checkFinancialModel->financial_model)) {
            $flag = 0;
        }
        // $latestDateTime = sensor_data_logging::where('site_id', $site_id)->latest('updated_at')->first();
        // $timeAgo = "";
        // if ($latestDateTime) {
        //     $updatedTime = $latestDateTime->updated_at;
        //     $currentTime = now();
        //     $diffInSeconds = $currentTime->diffInSeconds($updatedTime);

        //     // Convert to a Carbon instance for human-readable output
        //     $timeAgo = Carbon::now()->subSeconds($diffInSeconds)->diffForHumans([
        //         'parts' => 1, // Show only the most significant unit
        //     ]);

        //     echo "Time in words: $timeAgo"; // Output: "14 minutes ago"
        // }
        if (now()->greaterThan(now()->setTime(8, 0))) {
            $overnightData = overnight_occupancy::whereDate('created_at', Carbon::today())->where('site_id', $site_id)->get();
        } else {
            $overnightData = overnight_occupancy::whereDate('created_at', Carbon::yesterday())->where('site_id', $site_id)->get();
        }

        $max = [];
        foreach ($overnightData as $overnight) {
            $sensordata = json_decode($overnight->data);
            $max = array_merge($max, $sensordata);
        }
        $maxcount = count($max);

        $active = "home";

        if ($checkFinancialModel->site_type_of_product == "eecs") {
            return view('dashboard.eecsindex', compact('active', 'maxcount', 'flag', 'floorData'));
        }
        if ($checkFinancialModel->site_type_of_product == "slot_reservation") {
            return view('dashboard.slotreservationindex', compact('active', 'maxcount', 'flag', 'floorData'));
        }
        return view('dashboard.index', compact('active', 'maxcount', 'flag', 'floorData'));
    }

    public function tableViewData()
    {
        $site_id = Auth::user()->site_id;
        $siteType = getSiteType($site_id);
        if ($siteType == "eecs" || $siteType == "slot_reservation") {
            return redirect('dashboard/404');
        }
        $access = Auth::user()->access;
        $accessArray = explode(',', $access);
        $checkAccess = "table-view";
        $flag = 0;
        if (in_array($checkAccess, $accessArray)) {
            $flag = 1;
        }

        if ($flag) {

            $tableData = table_info::where('site_id', $site_id)->first();
            $active = "table-view";
            $title = "";
            if (empty($tableData)) {
                $title = "No Data";
                return view('dashboard.table-view', compact('title', 'active'));
            }
            $title = $tableData->table_name;
            return view('dashboard.table-view', compact('title', 'active'));
        } else {
            return redirect('noaccess');
        }
    }

    public function getTableViewData()
    {
        $site_id = Auth::user()->site_id;

        $latestDateTime = sensor_data_logging::where('site_id', $site_id)->latest('updated_at')->first();
        $timeAgo = "Last Update: ";
        $seconds = 0;
        if ($latestDateTime) {
            $updatedTime = $latestDateTime->updated_at;
            $currentTime = now();
            $diffInSeconds = $currentTime->diffInSeconds($updatedTime);
            $seconds = $diffInSeconds;

            if ($diffInSeconds >= 5) {
                // Convert to a Carbon instance for human-readable output
                $timeAgo = Carbon::now()->subSeconds($diffInSeconds)->diffForHumans([
                    'parts' => 1, // Show only the most significant unit
                ]);

                $timeAgo = "Last Update: " . $timeAgo;
            } else {
                $timeAgo = "Last Update: Just now";
            }
        }

        $minutes = CarbonInterval::seconds($seconds)->totalMinutes;

        $tableData = table_info::where('site_id', $site_id)->first();

        if (empty($tableData)) {
            return response()->json(['data' => 'No Data', 'lastUpdated' => $timeAgo, 'minutes' => $minutes]);
        }

        $entryData = table_entry::where('table_id', $tableData->table_id)->get();
        // dd($entryData);
        $formattedSensors = [];
        foreach ($entryData as $entry) {
            $SensorData = [];
            $array = json_decode($entry->floor_zonal_sensor_ids, true);

            // Separate the values for Floor, Zonals, and Sensors
            $floors = explode(',', $array['Floor']);  // ['2', '4']
            $zonals = explode(',', $array['Zonals']); // ['4', '5']
            $sensors = explode(',', $array['Sensors']); // ['4']
            // "floors" => $floors[0] == "" ? 'empty' : 'no empty',
            $logic_to_calculate_color = explode(',', $entry->logic_to_calculate_numbers);

            $sensorDataLog = sensor_data_logging::where('site_id', $entry->site_id)->get();

            if ($floors[0] == "" && $zonals[0] == "" && $sensors[0] == "") {
                // foreach ($sensorDataLog as $log) {
                //     if (in_array($log->color, $logic_to_calculate_color)) {
                //         $SensorData[] = 
                //     }
                // }
                // $finalOutput = $entry->display_format;
            } else {
                foreach ($sensorDataLog as $log) {
                    $sensor_id = getSensorId($log->sensor);
                    if (in_array($log->floor_id, $floors)) {
                        if (in_array($log->color, $logic_to_calculate_color)) {
                            $SensorData[] = $log->sensor;
                        }
                    }
                    if (in_array($log->zonal_id, $zonals)) {
                        if (in_array($log->color, $logic_to_calculate_color)) {
                            $SensorData[] = $log->sensor;
                        }
                    }
                    if (in_array($sensor_id, $sensors)) {
                        if (in_array($log->color, $logic_to_calculate_color)) {
                            $SensorData[] = $log->sensor;
                        }
                    }
                }
            }
            $uniqueCount = count(array_unique($SensorData));
            // $color = $colorMap[strtolower($entry["color"])] ?? 'U';
            // $formattedSensors[$index + 1] = [
            $formattedSensors[] = [
                "entry_name" => $entry->entry_name,
                "count" => $uniqueCount,
            ];
        }

        $siteData = $this->getSiteData();

        $count = count($siteData);
        // $count = 2;

        return response()->json(['data' => $formattedSensors, 'occupancy' => $siteData, 'lastUpdated' => $timeAgo, 'count' => $count, 'minutes' => $minutes]);
    }

    public function getSiteData()
    {
        $site_id = Auth::user()->site_id;

        $floorsData = floor_info::where('site_id', $site_id)->get();

        $siteTotal = ['name', 0, 0, 0, 0];

        $available = 0;
        $occupied = 0;
        $floorWiseData = [];
        foreach ($floorsData as $floor) {
            $floorData = sensor_data_logging::where('floor_id', $floor->floor_id)->get();
            $total = 0;
            $available = 0;
            $occupied = 0;
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
                $percentage = ($occupied / $total) * 100;
                $percentage = number_format($percentage, 2);
                $floorWiseData[] = [
                    'name' => $floor->floor_name,
                    'total' => $total,
                    'available' => $available,
                    'occupied' => $occupied,
                    'percentage' => $percentage,
                ];
                $floorWiseData = array_reverse($floorWiseData);
            }
            $siteTotal[1] = $siteTotal[1] + $total;
            $siteTotal[2] = $siteTotal[2] + $available;
            $siteTotal[3] = $siteTotal[3] + $occupied;
            $sitePercentage = ($siteTotal[3] / $siteTotal[1]) * 100;
            $sitePercentage = number_format($sitePercentage, 2);
            $siteTotal[4] = $sitePercentage;
        }

        $siteTotal[0] = getSitename($site_id);


        $floorWiseData[] = [
            'name' => $siteTotal[0],
            'total' => $siteTotal[1],
            'available' => $siteTotal[2],
            'occupied' => $siteTotal[3],
            'percentage' => $siteTotal[4],
        ];

        $floorWiseData = array_reverse($floorWiseData);

        return $floorWiseData;
    }

    public function chart()
    {
        $site_id = Auth::user()->site_id;
        // $getData = DB::table('site_data_by_minutes')
        //     ->where('site_id', $site_id)
        //     ->whereDate('created_at', Carbon::today())
        //     ->get();
        $getData = DB::table('site_data_by_days')
            ->where('site_id', $site_id)
            // ->whereDate('created_at', Carbon::today())
            ->get();

        $data = [];
        foreach ($getData as $entry) {

            $epochTime = (int)str_pad(strtotime($entry->date), 13, 0, STR_PAD_RIGHT);

            $data[] = [
                'date' => $epochTime,
                'checkIn' => $entry->check_in_count,
                'checkOut' => $entry->check_out_count,
            ];
        }

        // dd($data);
        // exit;

        return view('dashboard.testchart', compact('data'));
    }

    public function editEecsCount()
    {
        $active = 'editeecs';
        $site_id = Auth::user()->site_id;
        $floorData = floor_info::where('site_id', $site_id)->get();
        return view('dashboard.editeecs', compact('active', 'floorData'));
    }

    public function getTypes($id)
    {
        $site_id = Auth::user()->site_id;
        $floorData = floor_info::where('floor_id', $id)->first();
        // $typedata = eecs_device_info::where('site_id', $site_id)->first();
        $measuredCount = json_decode($floorData->measured_count, true);
        // $measuredCount = explode(',', $typedata->detection_list);

        $typeIds = array_keys($measuredCount);
        // $typeIds = array_values($measuredCount);
        // dd($typeIds);

        // Fetch type names from DB (assuming you have a detection_types table)
        $types = DB::table('detection_types')
            ->whereIn('id', $typeIds)
            ->pluck('type', 'id'); // [1 => 'TwoWheeler', 2 => 'FourWheeler']

        $data = [];

        foreach ($measuredCount as $typeId => $count) {
            $data[] = [
                'type_id' => $typeId,
                'type_name' => $types[$typeId] ?? 'Unknown',
                'count' => $count
            ];
        }

        // Optional: return as JSON
        return response()->json($data);
    }

    public function maxgetTypes($id)
    {
        $site_id = Auth::user()->site_id;
        $floorData = floor_info::where('floor_id', $id)->first();
        // $typedata = eecs_device_info::where('site_id', $site_id)->first();
        $measuredCount = json_decode($floorData->max_count, true);
        // $measuredCount = explode(',', $typedata->detection_list);

        $typeIds = array_keys($measuredCount);
        // $typeIds = array_values($measuredCount);
        // dd($typeIds);

        // Fetch type names from DB (assuming you have a detection_types table)
        $types = DB::table('detection_types')
            ->whereIn('id', $typeIds)
            ->pluck('type', 'id'); // [1 => 'TwoWheeler', 2 => 'FourWheeler']

        $data = [];

        foreach ($measuredCount as $typeId => $count) {
            $data[] = [
                'type_id' => $typeId,
                'type_name' => $types[$typeId] ?? 'Unknown',
                'count' => $count
            ];
        }

        // Optional: return as JSON
        return response()->json($data);
    }

    public function updateCount(Request $request)
    {
        // dd($request->all());
        $floorData = floor_info::where('floor_id', $request->floor_id)->first();

        if ($floorData) {
            if ($request->counttype == "measured") {
                // Decode existing measured_count
                $measuredCount = json_decode($floorData->measured_count, true);

                // Update the specific type with the new count
                $measuredCount[$request->type] = (int) $request->count;

                // Save the updated JSON back to DB
                $floorData->measured_count = json_encode($measuredCount);
                $floorData->save();
            } else {
                // Decode existing measured_count
                $maxCount = json_decode($floorData->max_count, true);

                // Update the specific type with the new count
                $maxCount[$request->type] = (int) $request->count;

                // Save the updated JSON back to DB
                $floorData->max_count = json_encode($maxCount);
                $floorData->save();
            }


            return redirect()->route('dashboard/editeecs')->with('message', 'Count Updated');
        }
        return response()->json(['status' => 'error', 'message' => 'Floor not found'], 404);
    }

    public function getEECSData()
    {

        $site_id = Auth::user()->site_id;

        $latestDateTime = floor_info::where('site_id', $site_id)->latest('updated_at')->first();
        $timeAgo = "Last Update: ";
        $seconds = 0;
        if ($latestDateTime) {
            $updatedTime = $latestDateTime->updated_at;
            $currentTime = now();
            $diffInSeconds = $currentTime->diffInSeconds($updatedTime);
            $seconds = $diffInSeconds;

            if ($diffInSeconds >= 5) {
                // Convert to a Carbon instance for human-readable output
                $timeAgo = Carbon::now()->subSeconds($diffInSeconds)->diffForHumans([
                    'parts' => 1, // Show only the most significant unit
                ]);

                $timeAgo = "Last Update: " . $timeAgo;
            } else {
                $timeAgo = "Last Update: Just now";
            }
        }

        $minutes = CarbonInterval::seconds($seconds)->totalMinutes;

        $site_name = site_info::where('site_id', $site_id)->value('site_name'); // Adjust table/column as needed
        $deviceData = eecs_device_info::where('site_id', $site_id)->first();

        if ($deviceData) {
            $detectionList = explode(',', $deviceData->detection_list);
            $floorData = floor_info::where('site_id', $site_id)->get();

            $floorWiseData = [];
            $inOutData = [];
            $overall = [
                'total' => 0,
                'occupied' => 0,
                'available' => 0
            ];

            // Used to aggregate detection details across all floors
            $siteDetectionAggregates = [];

            foreach ($floorData as $floor) {
                $maxcount = json_decode($floor->max_count, true);
                $measuredcount = json_decode($floor->measured_count, true);

                $detectionDetails = [];
                $floorTotal = 0;
                $floorOccupied = 0;
                $floorAvailable = 0;

                $dataLoggingFloors = eece_data_logging_floor::where('floor_id', $floor->floor_id)->whereDate('updated_at', Carbon::today())->get()->groupBy('type');

                foreach ($detectionList as $typeId) {
                    $typeName = getTypeNameForSite($typeId);

                    $total = isset($maxcount[$typeId]) ? (int) $maxcount[$typeId] : 0;
                    $occupied = isset($measuredcount[$typeId]) ? (int) $measuredcount[$typeId] : 0;
                    $available = $total - $occupied;
                    $percentage11 = $total > 0 ? number_format(($occupied / $total) * 100, 2) : 0.00;
                    $percentage12 = $total > 0 ? number_format(($available / $total) * 100, 2) : 0.00;

                    $detectionDetails[] = [
                        'id' => $typeId,
                        'name' => $typeName,
                        'total' => $total,
                        'occupied' => $occupied,
                        'available' => $available,
                        'percentage' => (float) $percentage11,
                        'apercentage' => (float) $percentage12
                    ];

                    $floorTotal += $total;
                    $floorOccupied += $occupied;
                    $floorAvailable += $available;

                    $overall['total'] += $total;
                    $overall['occupied'] += $occupied;
                    $overall['available'] += $available;

                    // Grouped detection type aggregation
                    if (!isset($siteDetectionAggregates[$typeId])) {
                        $siteDetectionAggregates[$typeId] = [
                            'id' => $typeId,
                            'name' => $typeName,
                            'total' => 0,
                            'occupied' => 0,
                            'available' => 0,
                            'percentage' => (float) 0.00,
                            'apercentage' => (float) 0.00,
                        ];
                    }
                    $siteDetectionAggregates[$typeId]['total'] += $total;
                    $siteDetectionAggregates[$typeId]['occupied'] += $occupied;
                    $siteDetectionAggregates[$typeId]['available'] += $available;

                    $siteTotal = (int)$siteDetectionAggregates[$typeId]['total'];
                    $siteOccupied = (int)$siteDetectionAggregates[$typeId]['occupied'];
                    $siteAvailable = (int)$siteDetectionAggregates[$typeId]['available'];

                    $siteDetectionAggregates[$typeId]['percentage'] = $siteTotal > 0
                        ? (float) number_format(($siteOccupied / $siteTotal) * 100, 2)
                        : 0.00;

                    $siteDetectionAggregates[$typeId]['apercentage'] = $siteTotal > 0
                        ? (float) number_format(($siteAvailable / $siteTotal) * 100, 2)
                        : 0.00;

                    // in-out
                    $in = 0;
                    $out = 0;


                    if (isset($dataLoggingFloors[$typeId])) {
                        foreach ($dataLoggingFloors[$typeId] as $floortype) {
                            if ($floortype->count < 0) {
                                $out += abs($floortype->count);
                            } elseif ($floortype->count > 0) {
                                $in += $floortype->count;
                            }
                        }
                    }

                    $inOutData[] = [
                        'type' => getTypeNameForSite($typeId),
                        'in' => $in,
                        'out' => $out,
                    ];
                }

                $percentage = $floorTotal > 0 ? number_format(($floorOccupied / $floorTotal) * 100, 2) : 0.00;
                $apercentage = $floorTotal > 0 ? number_format(($floorAvailable / $floorTotal) * 100, 2) : 0.00;


                $floorWiseData[] = [
                    'floor_name' => $floor->floor_name,
                    'total' => $floorTotal,
                    'occupied' => $floorOccupied,
                    'available' => $floorAvailable,
                    'percentage' => $percentage,
                    'apercentage' => $apercentage,
                    'detection_details' => $detectionDetails,
                    'inOutData' => $inOutData
                ];
                $inOutData = [];
            }

            $dataLoggingSites = eece_data_logging_site::where('site_id', $site_id)->whereDate('updated_at', Carbon::today())->get()->groupBy('type');
            // dd($dataLoggingSites);
            foreach ($detectionList as $typeId) {
                $in = 0;
                $out = 0;

                if (isset($dataLoggingSites[$typeId])) {
                    foreach ($dataLoggingSites[$typeId] as $floortype) {
                        if ($floortype->count < 0) {
                            $out += abs($floortype->count);
                        } elseif ($floortype->count > 0) {
                            $in += $floortype->count;
                        }
                    }
                }

                $inOutData[] = [
                    'type' => getTypeNameForSite($typeId),
                    'in' => $in,
                    'out' => $out,
                ];
            }

            // Create overall site-level summary
            $sitePercentage = $overall['total'] > 0 ? number_format(($overall['occupied'] / $overall['total']) * 100, 2) : 0.00;
            $asitePercentage = $overall['total'] > 0 ? number_format(($overall['available'] / $overall['total']) * 100, 2) : 0.00;

            array_unshift($floorWiseData, [
                'floor_name' => $site_name ?? 'Site Total',
                'total' => $overall['total'],
                'occupied' => $overall['occupied'],
                'available' => $overall['available'],
                'percentage' => $sitePercentage,
                'apercentage' => $asitePercentage,
                'detection_details' => array_values($siteDetectionAggregates),
                'inOutData' => $inOutData
            ]);

            // $floorWiseData = array_reverse($floorWiseData);

            return response()->json([
                'floorWiseData' => $floorWiseData,
                'overall' => $overall,
                'lastUpdated' => $timeAgo,
                'count' => count($floorWiseData),
                // 'count' => 2,
                'minutes' => $minutes
            ]);
        }
    }

    public function getDashboardData()
    {
        $access = Auth::user()->access;
        $accessArray = explode(',', $access);
        $floorAccess = "floor-map";
        $summaryAccess = "summary-report";
        $floorFlag = 0;
        if (in_array($floorAccess, $accessArray)) {
            $floorFlag = 1;
        }

        $checkAccess = "financial-model";
        $flag = 0;
        if (in_array($checkAccess, $accessArray)) {
            $flag = 1;
        }

        $site_id = Auth::user()->site_id;
        $checkFinancialModel = site_info::where('site_id', $site_id)->first();
        if (empty($checkFinancialModel->financial_model)) {
            $flag = 0;
        }

        $latestDateTime = sensor_data_logging::where('site_id', $site_id)->latest('updated_at')->first();
        $timeAgo = "Last Update: ";
        $seconds = 0;
        if ($latestDateTime) {
            $updatedTime = $latestDateTime->updated_at;
            $currentTime = now();
            $diffInSeconds = $currentTime->diffInSeconds($updatedTime);
            $seconds = $diffInSeconds;

            if ($diffInSeconds >= 5) {
                // Convert to a Carbon instance for human-readable output
                $timeAgo = Carbon::now()->subSeconds($diffInSeconds)->diffForHumans([
                    'parts' => 1, // Show only the most significant unit
                ]);

                $timeAgo = "Last Update: " . $timeAgo;
            } else {
                $timeAgo = "Last Update: Just now";
            }
        }

        $minutes = CarbonInterval::seconds($seconds)->totalMinutes;

        $startOfToday = Carbon::now()->startOfDay();
        $currentDateTime = Carbon::now();
        $floorData = DB::table('floor_data_by_minutes')
            ->where('site_id', $site_id)
            ->whereBetween('created_at', [$startOfToday, $currentDateTime])
            ->get()
            ->groupBy('floor_id');

        $inOutData = [];
        $siteTotal = ['name', 0, 0, 0];
        foreach ($floorData as $floorId => $entries) {
            // Initialize counters
            $inCount = 0;
            $outCount = 0;

            // Track the previous status
            $previousStatus = null;
            $sensorTime = [];

            foreach ($entries as $entry) {
                if ($entry->data != "[]") {

                    $currentStatus = json_decode($entry->data, true);

                    if ($previousStatus) {
                        foreach ($currentStatus as $sensorId => $status) {
                            if (isset($previousStatus[$sensorId])) {
                                // Check for IN (0 → 1)
                                if ($status == "1" && $previousStatus[$sensorId] == "0") {
                                    $inCount++;
                                }

                                // Check for OUT (1 → 0)
                                if ($status == "0" && $previousStatus[$sensorId] == "1") {
                                    $outCount++;
                                }
                            }
                        }
                    }

                    foreach ($currentStatus as $sensorId => $status) {
                        $sensorTime[] = [$sensorId, $status];
                    }

                    // Update previous status
                    $previousStatus = $currentStatus;
                }
            }

            $groupedData = [];
            $prices = [];
            if ($flag == 1) {
                foreach ($sensorTime as $entry) {
                    $sensorId = (string) $entry[0]; // Convert to string to handle numeric IDs consistently
                    $status = $entry[1];

                    // Group by sensor ID
                    $groupedData[$sensorId][] = $status;
                }

                if (!empty($groupedData)) {
                    $result = [];
                    foreach ($groupedData as $sensorId => $statuses) {
                        $count = 0;
                        $initialCount = 0;
                        $firstStatus = reset($statuses);
                        if ($firstStatus == 1) {
                            $prevZero = DB::table('floor_data_by_minutes')
                                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.\"{$sensorId}\"')) = '0'")
                                ->where('created_at', '<', $startOfToday)
                                // ->whereDate('created_at', '<', $date)
                                // ->whereBetween('created_at', '<', [$startOfToday, $currentDateTime])
                                ->orderBy('created_at', 'desc')
                                ->first();

                            $floorFirstData = DB::table('floor_data_by_minutes')
                                ->where('floor_id', $floorId)
                                ->whereDate('created_at', $startOfToday)
                                // ->whereBetween('created_at', [$startOfToday, $currentDateTime])
                                // ->orderBy('created_at', 'desc')
                                ->first();
                            if ($prevZero) {
                                $initialCount = Carbon::parse($floorFirstData->created_at)
                                    ->diffInMinutes(Carbon::parse($prevZero->created_at)) - 1;
                            }
                        }
                        $count = $initialCount;
                        foreach ($statuses as $status) {
                            if ($status == 1) {
                                $count++;
                            } else {
                                if ($count > 0) {
                                    $result[] = $count;
                                    $count = 0;
                                }
                            }
                        }
                    }
                }


                foreach ($result as $value) {
                    $prices[] = getPrice($value);
                }
            }

            $inOutData[] = [
                'name' => getFloorname($floorId),
                'id' => $floorId,
                'in' => $inCount,
                'out' => $outCount,
                'price' => array_sum($prices),
                'btn' => 1
            ];
            $siteTotal[1] = $siteTotal[1] + $inCount;
            $siteTotal[2] = $siteTotal[2] + $outCount;
            $siteTotal[3] = $siteTotal[3] + array_sum($prices);
        }
        $inOutData = array_reverse($inOutData);
        $inOutData[] = [
            'name' => getSitename($site_id),
            'id' => $site_id,
            'in' => $siteTotal[1],
            'out' => $siteTotal[2],
            'price' => $siteTotal[3],
            'btn' => 0
        ];
        $inOutData = array_reverse($inOutData);

        // dd($inOutData);

        $floorsData = floor_info::where('site_id', $site_id)->get();

        // $available = 0;
        // $occupied = 0;
        $floorWiseData = [];
        $siteTotal = ['name', 0, 0, 0, 0];

        foreach ($floorsData as $floor) {
            $floorData = sensor_data_logging::where('floor_id', $floor->floor_id)->get();
            // dd($floorData);
            if ($floorData->isNotEmpty()) {
                $total = 0;
                $available = 0;
                $occupied = 0;
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
                $percentage = $total > 0 ? ($occupied / $total) * 100 : 0;
                $percentage = number_format($percentage, 2);
                $floorWiseData[] = [
                    'name' => $floor->floor_name,
                    'total' => $total,
                    'available' => $available,
                    'occupied' => $occupied,
                    'percentage' => $percentage,
                ];
                $siteTotal[1] = $siteTotal[1] + $total;
                $siteTotal[2] = $siteTotal[2] + $available;
                $siteTotal[3] = $siteTotal[3] + $occupied;
                // $sitePercentage = ($siteTotal[3] / $siteTotal[1]) * 100;
                $sitePercentage = $siteTotal[1] > 0 ? ($siteTotal[3] / $siteTotal[1]) * 100 : 0;
                $sitePercentage = number_format($sitePercentage, 2);
                $siteTotal[4] = $sitePercentage;
            }
        }
        // dd($floorWiseData);

        // $count = 2;
        $floorWiseData = array_reverse($floorWiseData);

        // dd($floorWiseData);

        $siteTotal[0] = getSitename($site_id);

        $floorWiseData[] = [
            'name' => $siteTotal[0],
            'total' => $siteTotal[1],
            'available' => $siteTotal[2],
            'occupied' => $siteTotal[3],
            'percentage' => $siteTotal[4],
        ];
        $count = count($floorWiseData);
        // $count = 1;
        $floorWiseData = array_reverse($floorWiseData);

        // $name = "Floor";
        // $total = 20;
        // $available = 10;
        // $occupied = 10;
        // $current = 33;
        // $floorHtml = "<tr><td class='table-td'><a class=\"btn btn-primary btn-lg disabled\" role=\"button\" aria-disabled=\"true\">" . $name . "</a></td><td class='table-td'>" . $total . "</td><td class='table-td'>" . $available . "</td><td class='table-td'>" . $occupied . "</td><td class='table-td'><a class=\"btn btn-secondary btn-lg disabled\" role=\"button\" aria-disabled=\"true\">" . $name . "</a></td></tr>";

        return response()->json(['data' => $floorWiseData, 'lastUpdated' => $timeAgo, 'count' => $count, 'minutes' => $minutes, 'inOutData' => $inOutData, 'floorFlag' => $floorFlag, 'flag' => $flag]);
    }

    public function detailedView()
    {
        $site_id = Auth::user()->site_id;
        $siteType = getSiteType($site_id);
        if ($siteType == "slot_reservation") {
            return redirect('dashboard/404');
        }
        $access = Auth::user()->access;
        $accessArray = explode(',', $access);
        $checkAccess = "detailed-view";
        $flag = 0;
        if (in_array($checkAccess, $accessArray)) {
            $flag = 1;
        }
        if ($flag) {
            $active = "detailed-view";
            if ($siteType == "eecs") {
                return view('dashboard.eecsdetailedview', compact('active'));
            }
            return view('dashboard.detailedview', compact('active'));
        } else {
            return redirect('noaccess');
        }
    }

    public function getSiteAllData()
    {
        $site_id = Auth::user()->site_id;

        //data start
        $siteData = sensor_data_logging::where('site_id', $site_id)->get();
        $total = 0;
        $available = 0;
        $occupied = 0;
        foreach ($siteData as $s) {
            if ($s->status == 1 || $s->status == 2 || $s->status == 4) {
                $occupied++;
            }
            if ($s->status == 0) {
                $available++;
            }
        }
        $total = $available + $occupied;
        $percentage = ($occupied / $total) * 100;
        $percentage = number_format($percentage, 2);
        //data end



        $floorWiseData[] = [
            'name' => getSitename($site_id),
            'id' => $site_id,
            'total' => $total,
            'available' => $available,
            'occupied' => $occupied,
            'percentage' => $percentage,
            'btn' => 0
        ];

        return $floorWiseData;
    }

    public function getSiteChartData()
    {
        $site_id = Auth::user()->site_id;
        $perminutedata = DB::table('site_data_by_minutes')
            ->where('site_id', $site_id)
            ->whereDate('created_at', Carbon::today())
            ->get();


        // column data start
        $HourData = getTodayHoursData();
        $hoursArray12H = $HourData['hoursArray12H'];

        // Get the current hour (up to now)
        $currentHour = Carbon::now()->format('H');
        $InData = [];
        $OutData = [];
        $columnData = [];

        if (Auth::user()->chart_view == 3) {
            // Loop to initialize data for every hour from 00:00:00 to the current hour
            for ($hour = 0; $hour <= $currentHour; $hour++) {
                $startHour = Carbon::today()->setHour($hour)->format('Y-m-d H:i:s'); // Start of the hour
                $endHour = Carbon::today()->setHour($hour)->addHour()->format('Y-m-d H:i:s'); // End of the hour

                // Initialize counters
                $inCount = 0;
                $outCount = 0;

                // Track the previous status
                $previousStatus = null;

                $getData = DB::table('site_data_by_minutes')
                    ->where('site_id', $site_id)
                    ->whereDate('created_at', Carbon::today())
                    ->whereBetween('created_at', [$startHour, $endHour])
                    ->get();

                foreach ($getData as $entry) {
                    if ($entry->data != "[]") {

                        $currentStatus = json_decode($entry->data, true);

                        if ($previousStatus) {
                            foreach ($currentStatus as $sensorId => $status) {
                                if (isset($previousStatus[$sensorId])) {
                                    // Check for IN (0 → 1)
                                    if ($status == "1" && $previousStatus[$sensorId] == "0") {
                                        $inCount++;
                                    }

                                    // Check for OUT (1 → 0)
                                    if ($status == "0" && $previousStatus[$sensorId] == "1") {
                                        $outCount++;
                                    }
                                }
                            }
                        }

                        // Update previous status
                        $previousStatus = $currentStatus;
                    }
                }

                $InData[] = $inCount;
                $OutData[] = $outCount;
            }
            $columnData = [
                'hoursArray12H' => $hoursArray12H,
                'InData' => $InData,
                'OutData' => $OutData,
            ];
        }
        // column data end


        // per minute data start
        $chartData = [];
        if (Auth::user()->chart_view == 1) {
            foreach ($perminutedata as $entry) {
                $dataAnalysis = json_decode($entry->data_analysis, true);
                $Occupied = $dataAnalysis['Occupied'];

                $epochTime = (int)str_pad(strtotime($entry->created_at), 13, 0, STR_PAD_RIGHT);

                $chartData[] = [
                    'date' => $epochTime,
                    'value' => $Occupied,
                ];
            }
        }
        if (Auth::user()->chart_view == 2) {
            foreach ($perminutedata as $entry) {
                $dataAnalysis = json_decode($entry->data_analysis, true);
                $Available = $dataAnalysis['Available'];

                $epochTime = (int)str_pad(strtotime($entry->created_at), 13, 0, STR_PAD_RIGHT);

                $chartData[] = [
                    'date' => $epochTime,
                    'value' => $Available,
                ];
            }
        }
        // per minute data end


        $floorWiseData[] = [
            'name' => getSitename($site_id),
            'chart' => $chartData,
            'columndata' => $columnData,
        ];

        return $floorWiseData;
    }

    public function getDetailedChartData()
    {
        $site_id = Auth::user()->site_id;


        $floorData = DB::table('floor_data_by_minutes')
            ->where('site_id', $site_id)
            ->whereDate('created_at', Carbon::today())
            ->get()
            ->groupBy('floor_id');

        $siteChartData = $this->getSiteChartData();


        $floorWiseData = [];

        $floorWiseData = $siteChartData;
        foreach ($floorData as $floorId => $entries) {

            // column data start
            $HourData = getTodayHoursData();
            $hoursArray12H = $HourData['hoursArray12H'];

            // Get the current hour (up to now)
            $currentHour = Carbon::now()->format('H');
            $InData = [];
            $OutData = [];
            $columnData = [];

            if (Auth::user()->chart_view == 3) {
                // Loop to initialize data for every hour from 00:00:00 to the current hour
                for ($hour = 0; $hour <= $currentHour; $hour++) {
                    $startHour = Carbon::today()->setHour($hour)->format('Y-m-d H:i:s'); // Start of the hour
                    $endHour = Carbon::today()->setHour($hour)->addHour()->format('Y-m-d H:i:s'); // End of the hour

                    // Initialize counters
                    $inCount = 0;
                    $outCount = 0;

                    // Track the previous status
                    $previousStatus = null;

                    $getData = DB::table('floor_data_by_minutes')
                        ->where('floor_id', $floorId)
                        ->whereDate('created_at', Carbon::today())
                        ->whereBetween('created_at', [$startHour, $endHour])
                        ->get();

                    foreach ($getData as $entry) {
                        if ($entry->data != "[]") {

                            $currentStatus = json_decode($entry->data, true);

                            if ($previousStatus) {
                                foreach ($currentStatus as $sensorId => $status) {
                                    if (isset($previousStatus[$sensorId])) {
                                        // Check for IN (0 → 1)
                                        if ($status == "1" && $previousStatus[$sensorId] == "0") {
                                            $inCount++;
                                        }

                                        // Check for OUT (1 → 0)
                                        if ($status == "0" && $previousStatus[$sensorId] == "1") {
                                            $outCount++;
                                        }
                                    }
                                }
                            }

                            // Update previous status
                            $previousStatus = $currentStatus;
                        }
                    }

                    $InData[] = $inCount;
                    $OutData[] = $outCount;
                }
                $columnData = [
                    'hoursArray12H' => $hoursArray12H,
                    'InData' => $InData,
                    'OutData' => $OutData,
                ];
            }
            // column data end


            // per minute data start
            $chartData = [];
            if (Auth::user()->chart_view == 1) {
                foreach ($entries as $entry) {
                    $dataAnalysis = json_decode($entry->data_analysis, true);
                    $Occupied = $dataAnalysis['Occupied'];

                    $epochTime = (int)str_pad(strtotime($entry->created_at), 13, 0, STR_PAD_RIGHT);

                    $chartData[] = [
                        'date' => $epochTime,
                        'value' => $Occupied,
                    ];
                }
            }

            if (Auth::user()->chart_view == 2) {
                foreach ($entries as $entry) {
                    $dataAnalysis = json_decode($entry->data_analysis, true);
                    $Available = $dataAnalysis['Available'];

                    $epochTime = (int)str_pad(strtotime($entry->created_at), 13, 0, STR_PAD_RIGHT);

                    $chartData[] = [
                        'date' => $epochTime,
                        'value' => $Available,
                    ];
                }
            }
            // per minute data end


            $floorWiseData[] = [
                'name' => getFloorname($floorId),
                'chart' => $chartData,
                'columndata' => $columnData,
            ];
        }
        $count = count($floorWiseData);
        // $count = 1;
        // dd($floorWiseData);

        return response()->json(['data' => $floorWiseData, 'count' => $count]);
    }

    public function getDetailedViewData()
    {
        $access = Auth::user()->access;
        $accessArray = explode(',', $access);
        $floorAccess = "floor-map";
        $summaryAccess = "summary-report";
        $floorFlag = 0;
        $summaryFlag = 0;
        if (in_array($floorAccess, $accessArray)) {
            $floorFlag = 1;
        }
        if (in_array($summaryAccess, $accessArray)) {
            $summaryFlag = 1;
        }

        $site_id = Auth::user()->site_id;

        $latestDateTime = sensor_data_logging::where('site_id', $site_id)->latest('updated_at')->first();
        $timeAgo = "Last Update: ";
        $seconds = 0;
        if ($latestDateTime) {
            $updatedTime = $latestDateTime->updated_at;
            $currentTime = now();
            $diffInSeconds = $currentTime->diffInSeconds($updatedTime);
            $seconds = $diffInSeconds;

            if ($diffInSeconds >= 5) {
                // Convert to a Carbon instance for human-readable output
                $timeAgo = Carbon::now()->subSeconds($diffInSeconds)->diffForHumans([
                    'parts' => 1, // Show only the most significant unit
                ]);

                $timeAgo = "Last Update: " . $timeAgo;
            } else {
                $timeAgo = "Last Update: Just now";
            }
        }

        $minutes = CarbonInterval::seconds($seconds)->totalMinutes;

        $floorData = DB::table('floor_data_by_minutes')
            ->where('site_id', $site_id)
            ->whereDate('created_at', Carbon::today())
            ->get()
            ->groupBy('floor_id');

        $siteAllData = $this->getSiteAllData();


        $floorWiseData = [];

        $floorWiseData = $siteAllData;
        foreach ($floorData as $floorId => $entries) {

            //data start
            $floorData = sensor_data_logging::where('floor_id', $floorId)->get();
            $total = 0;
            $available = 0;
            $occupied = 0;
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
                $percentage = ($occupied / $total) * 100;
                $percentage = number_format($percentage, 2);
            }
            //data end

            $floorWiseData[] = [
                'name' => getFloorname($floorId),
                'id' => $floorId,
                'total' => $total,
                'available' => $available,
                'occupied' => $occupied,
                'percentage' => $percentage,
                'btn' => 1
            ];
        }

        // dd($floorWiseData);
        $count = count($floorWiseData);
        // $count = 1;

        return response()->json(['data' => $floorWiseData, 'lastUpdated' => $timeAgo, 'count' => $count, 'minutes' => $minutes, 'summaryFlag' => $summaryFlag, 'floorFlag' => $floorFlag]);
    }

    public function perHourData()
    {
        // $site_id = Auth::user()->site_id;

        // $data = DB::table('site_data_by_minutes')
        //     ->select('*')
        //     ->where('site_id', $site_id)
        //     ->orderBy('date_time', 'asc')
        //     ->get();

        // // Group by hour
        // $groupedByHour = $data->groupBy(function ($item) {
        //     return Carbon::parse($item->date_time)->format('Y-m-d H:00:00');
        // });

        // Calculate hourly statistics
        // $hourlyData = $groupedByHour->map(function ($items, $hour) {
        //     // return [
        //     //     'hour_start' => $hour,
        //     //     'site_id' => $items->site_id, // Assuming one site_id per group
        //     //     // 'data_analysis' => $items->data_analysis,
        //     //     'count' => $items->count(),
        //     // ];
        //     print_r($hour);
        //     echo "<br>";
        // });

        // Return response
        // return response()->json($hourlyData->values());

        // dd($groupedByHour);

        $previousHourStart = Carbon::now()->subHour()->startOfHour(); // Start of the previous hour
        $previousHourEnd = Carbon::now()->subHour()->endOfHour();     // End of the previous hour
        $currentHour = Carbon::now()->format('Y-m-d H:00:00');
        $SiteData = DB::table('site_data_by_minutes')
            // ->select('site_id', DB::raw('MAX(id) as id'))
            ->whereBetween('created_at', [$previousHourStart, $currentHour])
            ->get()
            ->groupBy('site_id');

        $FloorData = DB::table('floor_data_by_minutes')
            // ->select('site_id', DB::raw('MAX(id) as id'))
            ->whereBetween('created_at', [$previousHourStart, $currentHour])
            ->get()
            ->groupBy('floor_id');
        // dd($FloorData);
        // exit;

        foreach ($SiteData as $siteId => $entries) {

            // Initialize counters
            $inCount = 0;
            $outCount = 0;

            // Track the previous status
            $previousStatus = null;

            $min = 0;
            $max = 0;
            $flag = 1;

            foreach ($entries as $entry) {
                if ($entry->data != "[]") {

                    $currentStatus = json_decode($entry->data, true);

                    if ($previousStatus) {
                        foreach ($currentStatus as $sensorId => $status) {
                            if (isset($previousStatus[$sensorId])) {
                                // Check for IN (0 → 1)
                                if ($status == "1" && $previousStatus[$sensorId] == "0") {
                                    $inCount++;
                                }

                                // Check for OUT (1 → 0)
                                if ($status == "0" && $previousStatus[$sensorId] == "1") {
                                    $outCount++;
                                }
                            }
                        }
                    }

                    // Update previous status
                    $previousStatus = $currentStatus;
                }

                if ($entry->created_at != $currentHour) {
                    $dataAnalysis = json_decode($entry->data_analysis, true);
                    if ($dataAnalysis) {
                        if ($flag) {
                            $min = $dataAnalysis['Occupied'];
                            $max = $dataAnalysis['Occupied'];
                            $flag = 0;
                        }
                        if ($dataAnalysis['Occupied'] > $max) {
                            $max = $dataAnalysis['Occupied'];
                        }
                        if ($dataAnalysis['Occupied'] < $min) {
                            $min = $dataAnalysis['Occupied'];
                        }
                    }
                }
            }
            $dataStore = [
                'site_id' => $siteId,
                'date_time_slot' => $previousHourStart,
                'check_in_count' => $inCount,
                'check_out_count' => $outCount,
                'max_count' => $max,
                'min_count' => $min,
            ];
            site_data_by_hour::create($dataStore);
        }

        foreach ($FloorData as $floorId => $entries) {

            // Initialize counters
            $inCount = 0;
            $outCount = 0;

            // Track the previous status
            $previousStatus = null;

            $min = 0;
            $max = 0;
            $flag = 1;

            foreach ($entries as $entry) {
                $siteid = $entry->site_id;
                if ($entry->data != "[]") {

                    $currentStatus = json_decode($entry->data, true);

                    if ($previousStatus) {
                        foreach ($currentStatus as $sensorId => $status) {
                            if (isset($previousStatus[$sensorId])) {
                                // Check for IN (0 → 1)
                                if ($status == "1" && $previousStatus[$sensorId] == "0") {
                                    $inCount++;
                                }

                                // Check for OUT (1 → 0)
                                if ($status == "0" && $previousStatus[$sensorId] == "1") {
                                    $outCount++;
                                }
                            }
                        }
                    }

                    // Update previous status
                    $previousStatus = $currentStatus;
                }

                if ($entry->created_at != $currentHour) {
                    $dataAnalysis = json_decode($entry->data_analysis, true);
                    if ($dataAnalysis) {
                        if ($flag) {
                            $min = $dataAnalysis['Occupied'];
                            $max = $dataAnalysis['Occupied'];
                            $flag = 0;
                        }
                        if ($dataAnalysis['Occupied'] > $max) {
                            $max = $dataAnalysis['Occupied'];
                        }
                        if ($dataAnalysis['Occupied'] < $min) {
                            $min = $dataAnalysis['Occupied'];
                        }
                    }
                }
            }
            $dataStore = [
                'floor_id' => $floorId,
                'site_id' => $siteid,
                'date_time_slot' => $previousHourStart,
                'check_in_count' => $inCount,
                'check_out_count' => $outCount,
                'max_count' => $max,
                'min_count' => $min,
            ];
            floor_data_by_hour::create($dataStore);
        }

        echo "Done!";
        // return response()->json($data);
    }

    public function dashboardLogin()
    {
        return view('dashboard.login');
    }

    public function dashboardLoginPost(Request $request)
    {
        // dd($request->all());
        // $credentials = $request->validate([
        //     'email' => 'required|email',
        //     'password' => 'required',
        // ]);

        // $data = User::where('email', $request->email)->first();
        // if ($data) {
        //     if ($data->role_id == 3) {
        //         if (Auth::attempt($credentials)) {
        //             $request->session()->regenerate();
        //             return redirect()->route('dashboard/table');
        //         } else {
        //             return back()->withErrors([
        //                 'email' => 'Invalid Credentials!',
        //             ]);
        //         }
        //     } else {
        //         return back()->withErrors([
        //             'email' => 'Invalid Credentials!',
        //         ]);
        //     }
        // } else {
        //     return back()->withErrors([
        //         'email' => 'Invalid Credentials!',
        //     ]);
        // }
    }

    public function dashboardLogout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('dashboard/login');
    }

    public function test1()
    {
        $previousHourStart = Carbon::now()->subHour()->startOfHour(); // Start of the previous hour
        $previousHourEnd = Carbon::now()->subHour()->endOfHour();     // End of the previous hour
        $currentHour = Carbon::now()->format('Y-m-d H:00:00');
        $data = DB::table('site_data_by_minutes')
            // ->select('site_id', DB::raw('MAX(id) as id'))
            ->whereBetween('created_at', [$previousHourStart, $currentHour])
            ->get()
            ->groupBy('site_id');

        foreach ($data as $siteId => $entries) {

            // Initialize counters
            $inCount = 0;
            $outCount = 0;

            // Track the previous status
            $previousStatus = null;

            $min = 0;
            $max = 0;
            $flag = 1;

            if ($siteId == 6) {

                foreach ($entries as $entry) {
                    if ($entry->data != "[]") {

                        $currentStatus = json_decode($entry->data, true);

                        if ($previousStatus) {
                            foreach ($currentStatus as $sensorId => $status) {
                                echo $previousStatus . "<br>";
                                if (isset($previousStatus[$sensorId])) {
                                    // Check for IN (0 → 1)
                                    if ($status == "1" && $previousStatus[$sensorId] == "0") {
                                        // $inCount++;
                                    }

                                    // Check for OUT (1 → 0)
                                    if ($status == "0" && $previousStatus[$sensorId] == "1") {
                                        $outCount++;
                                    }
                                }
                            }
                        }

                        // Update previous status
                        $previousStatus = $currentStatus;
                    }

                    if ($entry->created_at != $currentHour) {
                        $dataAnalysis = json_decode($entry->data_analysis, true);
                        if ($dataAnalysis) {
                            if ($flag) {
                                $min = $dataAnalysis['Occupied'];
                                $max = $dataAnalysis['Occupied'];
                                $flag = 0;
                            }
                            if ($dataAnalysis['Occupied'] < $min) {
                                $min = $dataAnalysis['Occupied'];
                            }
                            if ($dataAnalysis['Occupied'] > $max) {
                                $max = $dataAnalysis['Occupied'];
                            }
                        }
                    }
                }
            }
            // $dataStore = [
            //     'site_id' => $siteId,
            //     'date_time_slot' => $previousHourStart,
            //     'check_in_count' => $inCount,
            //     'check_out_count' => $outCount,
            //     'max_count' => $max,
            //     'min_count' => $min,
            // ];
            // site_data_by_hour::create($dataStore);
        }
    }

    public function changeData(Request $req)
    {
        // return response()->json(['data' => $req->value]);
        $site_id = Auth::user()->site_id;
        // $userData = User::where('site_id', $site_id)->first();
        // $userData->chart_view = $req->value;
        // if ($userData->update()) {
        //     return response()->json(['status' => 'success']);
        // } else {
        //     return response()->json(['status' => 'error']);
        // }

        $updated = DB::table('users')
            ->where('site_id', $site_id)
            ->update(['chart_view' => $req->value]);

        if ($updated) {
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'error']);
        }
    }

    public function summaryReport()
    {
        $access = Auth::user()->access;
        $accessArray = explode(',', $access);
        $checkAccess = "summary-report";
        $flag11 = 0;
        if (in_array($checkAccess, $accessArray)) {
            $flag11 = 1;
        }
        if ($flag11 == 0) {
            return redirect('noaccess');
        }

        $active = "summary-report";
        $site_id = Auth::user()->site_id;
        $siteType = getSiteType($site_id);
        if ($siteType == "eecs" || $siteType == "slot_reservation") {
            return redirect('dashboard/404');
        }

        $floorData = floor_info::where('site_id', $site_id)->get();
        $startDate = Carbon::yesterday()->startOfDay(); // Yesterday 00:00:00
        $endDate = Carbon::yesterday()->endOfDay(); // Yesterday 23:59:59

        $data = DB::table('site_data_by_hours')
            ->where('site_id', $site_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $time_ranges = [];
        $time_data = [];
        foreach ($data as $entry) {
            $carbonDate = Carbon::parse($entry->created_at);
            $date = $carbonDate->toDateString(); // Extract date (YYYY-MM-DD)
            $day = $carbonDate->format('l'); // Get full day name (Monday, Tuesday, etc.)
            $hour = $carbonDate->hour; // Extract hour (0-23)

            if ($hour >= 0 && $hour < 8) {
                $range = '12AM to 8AM';
                $session = 'Overnight';
                $startdatetime = Carbon::parse("$date 0:00:00");
                $enddatetime = Carbon::parse("$date 8:00:00");
                $datedata = DB::table('site_data_by_minutes')
                    ->where('site_id', $site_id)
                    ->whereBetween('created_at', [$startdatetime, $enddatetime])
                    ->get();
            } elseif ($hour >= 8 && $hour < 12) {
                $range = '8AM to 12PM';
                $session = 'Morning';
                $startdatetime = Carbon::parse("$date 8:00:00");
                $enddatetime = Carbon::parse("$date 12:00:00");
                $datedata = DB::table('site_data_by_minutes')
                    ->where('site_id', $site_id)
                    ->whereBetween('created_at', [$startdatetime, $enddatetime])
                    ->get();
            } elseif ($hour >= 12 && $hour < 16) {
                $range = '12PM to 4PM';
                $session = 'Afternoon';
                $startdatetime = Carbon::parse("$date 12:00:00");
                $enddatetime = Carbon::parse("$date 16:00:00");
                $datedata = DB::table('site_data_by_minutes')
                    ->where('site_id', $site_id)
                    ->whereBetween('created_at', [$startdatetime, $enddatetime])
                    ->get();
            } elseif ($hour >= 16 && $hour < 20) {
                $range = '4PM to 8PM';
                $session = 'Evening';
                $startdatetime = Carbon::parse("$date 16:00:00");
                $enddatetime = Carbon::parse("$date 20:00:00");
                $datedata = DB::table('site_data_by_minutes')
                    ->where('site_id', $site_id)
                    ->whereBetween('created_at', [$startdatetime, $enddatetime])
                    ->get();
            } else {
                $range = '8PM to 12AM';
                $session = 'Night';
                $startdatetime = Carbon::parse("$date 20:00:00");
                $enddatetime = Carbon::parse("$date 23:59:59");
                $datedata = DB::table('site_data_by_minutes')
                    ->where('site_id', $site_id)
                    ->whereBetween('created_at', [$startdatetime, $enddatetime])
                    ->get();
            }

            // Initialize if not already set
            if (!isset($time_ranges[$date][$range])) {
                $sensorTime = [];
                foreach ($datedata as $entries) {
                    $currentStatus = json_decode($entries->data, true);
                    foreach ($currentStatus as $sensorId => $status) {
                        $sensorTime[] = [$sensorId, $status];
                    }
                }

                $groupedData = [];
                foreach ($sensorTime as $entries) {
                    $sensorId = (string) $entries[0]; // Convert to string to handle numeric IDs consistently
                    $status = $entries[1];

                    // Group by sensor ID
                    $groupedData[$sensorId][] = $status;
                }

                $minTime = 0;
                $maxTime = 0;
                $avgTime = 0;
                if (!empty($groupedData)) {
                    $processedData = [];

                    foreach ($groupedData as $sensorId => $statuses) {
                        $newArray = []; // Stores transformed values

                        foreach ($statuses as $status) {
                            $status = intval($status); // Ensure it's an integer

                            if ($status === 0) {
                                // Add 0 if the last element is not already 0
                                if (empty($newArray) || end($newArray) !== 0) {
                                    $newArray[] = 0;
                                }
                            } else {
                                // Increment last element if it's not empty
                                if (!empty($newArray)) {
                                    $newArray[count($newArray) - 1]++;
                                } else {
                                    $newArray[] = 1;
                                }
                            }
                        }

                        // Remove trailing zeros (optional cleanup step)
                        while (!empty($newArray) && end($newArray) === 0) {
                            array_pop($newArray);
                        }

                        // $processedData[$sensorId] = $newArray;
                        // $processedData[] = $newArray;
                        array_push($processedData, $newArray);
                    }
                    $mergedData = array_merge(...array_filter($processedData));
                    $avgTime = array_sum($mergedData) / count($mergedData);
                    $minTime = min($mergedData);
                    $maxTime = max($mergedData);
                    if ($range == '12AM to 8AM' && $maxTime > 479) {
                        $maxTime = $maxTime . "*";
                    }
                    if ($range != '12AM to 8AM' && $maxTime > 239) {
                        $maxTime = $maxTime . "*";
                    }
                }
                $time_data[$date][$range] = [
                    'min_time' => $minTime,
                    'max_time' => $maxTime,
                    'avg_time' => number_format($avgTime, 2),
                ];

                $time_ranges[$date][$range] = [
                    'date' => $date,
                    'day' => $day,
                    'session' => $session,
                    'min_count' => $entry->min_count,
                    'max_count' => $entry->max_count,
                    'check_in_count' => $entry->check_in_count,
                    'check_out_count' => $entry->check_out_count,
                ];
            } else {
                // Update min_count
                if ($entry->min_count < $time_ranges[$date][$range]['min_count']) {
                    $time_ranges[$date][$range]['min_count'] = $entry->min_count;
                }

                // Update max_count
                if ($entry->max_count > $time_ranges[$date][$range]['max_count']) {
                    $time_ranges[$date][$range]['max_count'] = $entry->max_count;
                }

                // Sum up check_in_count
                $time_ranges[$date][$range]['check_in_count'] += $entry->check_in_count;
                $time_ranges[$date][$range]['check_out_count'] += $entry->check_out_count;
            }
        }

        $combined_data = [];

        foreach ($time_ranges as $date => $sessions) {
            foreach ($sessions as $session => $details) {
                $combined_data[$date][$session] = array_merge(
                    $details, // Data from $time_ranges
                    $time_data[$date][$session] ?? [ // Data from $time_data (if exists)
                        "min_time" => 0,
                        "max_time" => 0,
                        "avg_time" => "0.00"
                    ]
                );
            }
        }

        // echo count($time_ranges);
        // exit;
        $flag = 0;
        if (count($time_ranges) > 0) {
            $flag = 1;
        }
        $floorFlag = 0;
        $floorId = 0;


        //access
        $access = Auth::user()->access;
        $accessArray = explode(',', $access);
        $checkAccess = "summary-report";
        $flag11 = 0;
        if (in_array($checkAccess, $accessArray)) {
            $flag11 = 1;
        }
        if ($flag11) {
            return view('dashboard.summary-report', compact('flag', 'startDate', 'endDate', 'combined_data', 'floorData', 'floorFlag', 'floorId', 'active'));
        } else {
            return redirect('noaccess');
        }
    }

    public function summaryReportStats($name)
    {
        $access = Auth::user()->access;
        $accessArray = explode(',', $access);
        $checkAccess = "summary-report";
        $flag11 = 0;
        if (in_array($checkAccess, $accessArray)) {
            $flag11 = 1;
        }
        if ($flag11 == 0) {
            return redirect('noaccess');
        }

        $active = "summary-report";
        $floorFlag = 0;
        $site_id = Auth::user()->site_id;
        $floorData = floor_info::where('site_id', $site_id)->get();
        $startDate = Carbon::yesterday()->startOfDay(); // Yesterday 00:00:00
        $endDate = Carbon::yesterday()->endOfDay(); // Yesterday 23:59:59
        $getFloor = floor_info::where('floor_name', $name)->first();
        if ($getFloor) {
            $floorId = $getFloor->floor_id;
            $combined_data = $this->summaryReportFloorData($floorId, $startDate, $endDate);
            $flag = 0;
            if (count($combined_data) > 0) {
                $flag = 1;
            }
            $floorFlag = 1;
            return view('dashboard.summary-report', compact('flag', 'combined_data', 'startDate', 'endDate', 'floorFlag', 'floorId', 'floorData', 'active'));
        } else {
            return redirect('dashboard/summary-report');
        }
    }

    public function summaryReportFloorData($floorId, $startDate, $endDate)
    {
        $data = DB::table('floor_data_by_hours')
            ->where('floor_id', $floorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $time_ranges = [];
        $time_data = [];
        foreach ($data as $entry) {
            $carbonDate = Carbon::parse($entry->created_at);
            $date = $carbonDate->toDateString(); // Extract date (YYYY-MM-DD)
            $day = $carbonDate->format('l'); // Get full day name (Monday, Tuesday, etc.)
            $hour = $carbonDate->hour; // Extract hour (0-23)

            if ($hour >= 0 && $hour < 8) {
                $range = '12AM to 8AM';
                $session = 'Overnight';
                $startdatetime = Carbon::parse("$date 0:00:00");
                $enddatetime = Carbon::parse("$date 8:00:00");
                $datedata = DB::table('floor_data_by_minutes')
                    ->where('floor_id', $floorId)
                    ->whereBetween('created_at', [$startdatetime, $enddatetime])
                    ->get();
            } elseif ($hour >= 8 && $hour < 12) {
                $range = '8AM to 12PM';
                $session = 'Morning';
                $startdatetime = Carbon::parse("$date 8:00:00");
                $enddatetime = Carbon::parse("$date 12:00:00");
                $datedata = DB::table('floor_data_by_minutes')
                    ->where('floor_id', $floorId)
                    ->whereBetween('created_at', [$startdatetime, $enddatetime])
                    ->get();
            } elseif ($hour >= 12 && $hour < 16) {
                $range = '12PM to 4PM';
                $session = 'Afternoon';
                $startdatetime = Carbon::parse("$date 12:00:00");
                $enddatetime = Carbon::parse("$date 16:00:00");
                $datedata = DB::table('floor_data_by_minutes')
                    ->where('floor_id', $floorId)
                    ->whereBetween('created_at', [$startdatetime, $enddatetime])
                    ->get();
            } elseif ($hour >= 16 && $hour < 20) {
                $range = '4PM to 8PM';
                $session = 'Evening';
                $startdatetime = Carbon::parse("$date 16:00:00");
                $enddatetime = Carbon::parse("$date 20:00:00");
                $datedata = DB::table('floor_data_by_minutes')
                    ->where('floor_id', $floorId)
                    ->whereBetween('created_at', [$startdatetime, $enddatetime])
                    ->get();
            } else {
                $range = '8PM to 12AM';
                $session = 'Night';
                $startdatetime = Carbon::parse("$date 20:00:00");
                $enddatetime = Carbon::parse("$date 23:59:59");
                $datedata = DB::table('floor_data_by_minutes')
                    ->where('floor_id', $floorId)
                    ->whereBetween('created_at', [$startdatetime, $enddatetime])
                    ->get();
            }

            // Initialize if not already set
            if (!isset($time_ranges[$date][$range])) {
                $sensorTime = [];
                foreach ($datedata as $entries) {
                    $currentStatus = json_decode($entries->data, true);
                    if (!is_array($currentStatus)) {
                        continue; // Skip invalid JSON data
                    }
                    foreach ($currentStatus as $sensorId => $status) {
                        $sensorTime[] = [$sensorId, $status];
                    }
                }

                $groupedData = [];
                foreach ($sensorTime as $entries) {
                    $sensorId = (string) $entries[0]; // Convert to string to handle numeric IDs consistently
                    $status = $entries[1];

                    // Group by sensor ID
                    $groupedData[$sensorId][] = $status;
                }

                $minTime = 0;
                $maxTime = 0;
                $avgTime = 0;
                if (!empty($groupedData)) {
                    $processedData = [];

                    foreach ($groupedData as $sensorId => $statuses) {
                        $newArray = []; // Stores transformed values

                        foreach ($statuses as $status) {
                            $status = intval($status); // Ensure it's an integer

                            if ($status === 0) {
                                // Add 0 if the last element is not already 0
                                if (empty($newArray) || end($newArray) !== 0) {
                                    $newArray[] = 0;
                                }
                            } else {
                                // Increment last element if it's not empty
                                if (!empty($newArray)) {
                                    $newArray[count($newArray) - 1]++;
                                } else {
                                    $newArray[] = 1;
                                }
                            }
                        }

                        // Remove trailing zeros (optional cleanup step)
                        while (!empty($newArray) && end($newArray) === 0) {
                            array_pop($newArray);
                        }

                        // $processedData[$sensorId] = $newArray;
                        // $processedData[] = $newArray;
                        array_push($processedData, $newArray);
                    }
                    $mergedData = array_merge(...array_filter($processedData));
                    $avgTime = array_sum($mergedData) / count($mergedData);
                    $minTime = min($mergedData);
                    $maxTime = max($mergedData);

                    if ($range == '12AM to 8AM' && $maxTime > 479) {
                        $maxTime = $maxTime . "*";
                    }
                    if ($range != '12AM to 8AM' && $maxTime > 239) {
                        $maxTime = $maxTime . "*";
                    }
                }
                $time_data[$date][$range] = [
                    'min_time' => $minTime,
                    'max_time' => $maxTime,
                    'avg_time' => number_format($avgTime, 2),
                ];

                $time_ranges[$date][$range] = [
                    'date' => $date,
                    'day' => $day,
                    'session' => $session,
                    'min_count' => $entry->min_count,
                    'max_count' => $entry->max_count,
                    'check_in_count' => $entry->check_in_count,
                    'check_out_count' => $entry->check_out_count,
                ];
            } else {
                // Update min_count
                if ($entry->min_count < $time_ranges[$date][$range]['min_count']) {
                    $time_ranges[$date][$range]['min_count'] = $entry->min_count;
                }

                // Update max_count
                if ($entry->max_count > $time_ranges[$date][$range]['max_count']) {
                    $time_ranges[$date][$range]['max_count'] = $entry->max_count;
                }

                // Sum up check_in_count
                $time_ranges[$date][$range]['check_in_count'] += $entry->check_in_count;
                $time_ranges[$date][$range]['check_out_count'] += $entry->check_out_count;
            }
        }

        $combined_data = [];

        foreach ($time_ranges as $date => $sessions) {
            foreach ($sessions as $session => $details) {
                $combined_data[$date][$session] = array_merge(
                    $details, // Data from $time_ranges
                    $time_data[$date][$session] ?? [ // Data from $time_data (if exists)
                        "min_time" => 0,
                        "max_time" => 0,
                        "avg_time" => "0.00"
                    ]
                );
            }
        }
        // dd($combined_data);
        return $combined_data;
    }

    public function summaryReportPost(Request $request)
    {
        $active = "summary-report";
        $floorFlag = 0;
        $site_id = Auth::user()->site_id;
        $floorData = floor_info::where('site_id', $site_id)->get();
        $startDate = $request->startdate;
        $endDate = $request->enddate . ' 23:59:59'; // Extend end date to the full day
        $floorId = $request->filter;
        if ($floorId != 'site' && $floorId > 0) {
            $combined_data = $this->summaryReportFloorData($floorId, $startDate, $endDate);
            $flag = 0;
            if (count($combined_data) > 0) {
                $flag = 1;
            }
            $floorFlag = 1;
            return view('dashboard.summary-report', compact('flag', 'combined_data', 'startDate', 'endDate', 'floorFlag', 'floorId', 'floorData', 'active'));
        }
        $data = DB::table('site_data_by_hours')
            ->where('site_id', $site_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $time_ranges = [];
        $time_data = [];
        foreach ($data as $entry) {
            $carbonDate = Carbon::parse($entry->created_at);
            $date = $carbonDate->toDateString(); // Extract date (YYYY-MM-DD)
            $day = $carbonDate->format('l'); // Get full day name (Monday, Tuesday, etc.)
            $hour = $carbonDate->hour; // Extract hour (0-23)

            if ($hour >= 0 && $hour < 8) {
                $range = '12AM to 8AM';
                $session = 'Overnight';
                $startdatetime = Carbon::parse("$date 0:00:00");
                $enddatetime = Carbon::parse("$date 8:00:00");
                $datedata = DB::table('site_data_by_minutes')
                    ->where('site_id', $site_id)
                    ->whereBetween('created_at', [$startdatetime, $enddatetime])
                    ->get();
            } elseif ($hour >= 8 && $hour < 12) {
                $range = '8AM to 12PM';
                $session = 'Morning';
                $startdatetime = Carbon::parse("$date 8:00:00");
                $enddatetime = Carbon::parse("$date 12:00:00");
                $datedata = DB::table('site_data_by_minutes')
                    ->where('site_id', $site_id)
                    ->whereBetween('created_at', [$startdatetime, $enddatetime])
                    ->get();
            } elseif ($hour >= 12 && $hour < 16) {
                $range = '12PM to 4PM';
                $session = 'Afternoon';
                $startdatetime = Carbon::parse("$date 12:00:00");
                $enddatetime = Carbon::parse("$date 16:00:00");
                $datedata = DB::table('site_data_by_minutes')
                    ->where('site_id', $site_id)
                    ->whereBetween('created_at', [$startdatetime, $enddatetime])
                    ->get();
            } elseif ($hour >= 16 && $hour < 20) {
                $range = '4PM to 8PM';
                $session = 'Evening';
                $startdatetime = Carbon::parse("$date 16:00:00");
                $enddatetime = Carbon::parse("$date 20:00:00");
                $datedata = DB::table('site_data_by_minutes')
                    ->where('site_id', $site_id)
                    ->whereBetween('created_at', [$startdatetime, $enddatetime])
                    ->get();
            } else {
                $range = '8PM to 12AM';
                $session = 'Night';
                $startdatetime = Carbon::parse("$date 20:00:00");
                $enddatetime = Carbon::parse("$date 23:59:59");
                $datedata = DB::table('site_data_by_minutes')
                    ->where('site_id', $site_id)
                    ->whereBetween('created_at', [$startdatetime, $enddatetime])
                    ->get();
            }

            // Initialize if not already set
            if (!isset($time_ranges[$date][$range])) {
                $sensorTime = [];
                foreach ($datedata as $entries) {
                    $currentStatus = json_decode($entries->data, true);
                    if (!is_array($currentStatus)) {
                        continue; // Skip invalid JSON data
                    }
                    foreach ($currentStatus as $sensorId => $status) {
                        $sensorTime[] = [$sensorId, $status];
                    }
                }

                $groupedData = [];
                foreach ($sensorTime as $entries) {
                    $sensorId = (string) $entries[0]; // Convert to string to handle numeric IDs consistently
                    $status = $entries[1];

                    // Group by sensor ID
                    $groupedData[$sensorId][] = $status;
                }

                $minTime = 0;
                $maxTime = 0;
                $avgTime = 0;
                if (!empty($groupedData)) {
                    $processedData = [];

                    foreach ($groupedData as $sensorId => $statuses) {
                        $newArray = []; // Stores transformed values

                        foreach ($statuses as $status) {
                            $status = intval($status); // Ensure it's an integer

                            if ($status === 0) {
                                // Add 0 if the last element is not already 0
                                if (empty($newArray) || end($newArray) !== 0) {
                                    $newArray[] = 0;
                                }
                            } else {
                                // Increment last element if it's not empty
                                if (!empty($newArray)) {
                                    $newArray[count($newArray) - 1]++;
                                } else {
                                    $newArray[] = 1;
                                }
                            }
                        }

                        // Remove trailing zeros (optional cleanup step)
                        while (!empty($newArray) && end($newArray) === 0) {
                            array_pop($newArray);
                        }

                        // $processedData[$sensorId] = $newArray;
                        // $processedData[] = $newArray;
                        array_push($processedData, $newArray);
                    }
                    $mergedData = array_merge(...array_filter($processedData));
                    $avgTime = array_sum($mergedData) / count($mergedData);
                    $minTime = min($mergedData);
                    $maxTime = max($mergedData);

                    if ($range == '12AM to 8AM' && $maxTime > 479) {
                        $maxTime = $maxTime . "*";
                    }
                    if ($range != '12AM to 8AM' && $maxTime > 239) {
                        $maxTime = $maxTime . "*";
                    }
                }
                $time_data[$date][$range] = [
                    'min_time' => $minTime,
                    'max_time' => $maxTime,
                    'avg_time' => number_format($avgTime, 2),
                ];

                $time_ranges[$date][$range] = [
                    'date' => $date,
                    'day' => $day,
                    'session' => $session,
                    'min_count' => $entry->min_count,
                    'max_count' => $entry->max_count,
                    'check_in_count' => $entry->check_in_count,
                    'check_out_count' => $entry->check_out_count,
                ];
            } else {
                // Update min_count
                if ($entry->min_count < $time_ranges[$date][$range]['min_count']) {
                    $time_ranges[$date][$range]['min_count'] = $entry->min_count;
                }

                // Update max_count
                if ($entry->max_count > $time_ranges[$date][$range]['max_count']) {
                    $time_ranges[$date][$range]['max_count'] = $entry->max_count;
                }

                // Sum up check_in_count
                $time_ranges[$date][$range]['check_in_count'] += $entry->check_in_count;
                $time_ranges[$date][$range]['check_out_count'] += $entry->check_out_count;
            }
        }

        $combined_data = [];

        foreach ($time_ranges as $date => $sessions) {
            foreach ($sessions as $session => $details) {
                $combined_data[$date][$session] = array_merge(
                    $details, // Data from $time_ranges
                    $time_data[$date][$session] ?? [ // Data from $time_data (if exists)
                        "min_time" => 0,
                        "max_time" => 0,
                        "avg_time" => "0.00"
                    ]
                );
            }
        }

        // echo count($time_ranges);
        // exit;
        $flag = 0;
        if (count($time_ranges) > 0) {
            $flag = 1;
        }

        return view('dashboard.summary-report', compact('flag', 'combined_data', 'startDate', 'endDate', 'floorData', 'floorFlag', 'floorId', 'active'));
    }

    public function reservations($floor_id)
    {
        $site_id = Auth::user()->site_id;
        $slots_ids = explode(',', Auth::user()->slots_ids);
        $siteType = getSiteType($site_id);
        if ($siteType == "eecs" || $siteType == "sspi" || $siteType == "findmycar") {
            return redirect('dashboard/404');
        }
        $active = "reservations";

        $FloorData = floor_info::where('floor_id', $floor_id)->first();
        if (!$FloorData) {
            return "Data not found for the specified floor.";
        }
        $floorImg = $FloorData->floor_image;
        $path = public_path('floors/' . $floorImg);
        if (!File::exists($path)) {
            return response()->json(['error' => 'Floor image not found.'], 404);
        }
        if (!File::isFile($path)) {
            return response()->json(['error' => 'Floor image is not a valid file.'], 500);
        }
        $img = Image::make($path);
        if (!$img) {
            return response()->json(['error' => 'Failed to load floor image.'], 500);
        }
        $width = $img->width();
        $height = $img->height();

        $coordinates = json_decode($FloorData->floor_map_coordinate);
        $filtered = array_filter($coordinates, function ($item) use ($slots_ids) {
            return in_array($item->reservation_id, $slots_ids);
        });
        if (!$coordinates) {
            return "No coordinates found for the specified floor.";
        }
        foreach ($filtered as &$c) {
            $status = getSlotStatus($c->reservation_id);
            $c->status = $status;
            $c->x_pct = ($c->x / $width) * 100;
            $c->y_pct = ($c->y / $height) * 100;
        }
        unset($c);
        return view('dashboard.reservations', compact('active', 'filtered', 'floorImg', 'floor_id'));
    }
}
