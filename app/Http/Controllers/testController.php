<?php

namespace App\Http\Controllers;

use App\Models\floor_data_by_hour;
use App\Models\floor_info;
use App\Models\reservation_device_info;
use App\Models\site_data_by_hour;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class testController extends Controller
{
    public function test()
    {
        $site_id = Auth::user()->site_id;
        $startDate = Carbon::yesterday()->startOfDay(); // Yesterday 00:00:00
        $endDate = Carbon::today()->endOfDay(); // Yesterday 23:59:59

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


            // echo $date . " " . $day . " " . $hour . "<br>";

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

        // foreach($time_ranges as $time){
        //     print_r($time);
        //     echo "<br><br>";
        // }


        // print_r($datedata);
        // return $time_ranges;
        // return $time_data;

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

        return $combined_data;
    }

    public function test3()
    {
        $active = "fs";
        // return view('dashboard.testchart', compact('active'));

        $FloorData = floor_info::where('floor_id', 22)->first();
        $coordinates = json_decode($FloorData->floor_map_coordinate);
        foreach ($coordinates as &$c) {
            $status = getSlotStatus($c->reservation_id);
            $c->status = $status;
        }
        unset($c);
        $floorImg = $FloorData->floor_image;
        return view('dashboard.test', compact('active', 'coordinates', 'floorImg'));
    }

    public function toggleStatus(Request $request)
    {
        $id = $request->id;

        // Example using DB::table, adjust based on your actual table
        $record = reservation_device_info::find($id);

        if ($record) {
            $newStatus = $record->status == 1 ? 0 : 1;

            reservation_device_info::where('id', $id)->update(['status' => $newStatus]);

            return response()->json(['status' => $newStatus]);
        }

        return response()->json(['error' => 'Not found'], 404);
    }

    public function test11()
    {
        // $startdate = $request->startdate;
        // $enddate = $request->enddate;
        $startDate = Carbon::yesterday()->startOfDay(); // Yesterday's start time (00:00:00)
        $endDate = Carbon::today()->endOfDay(); // Today's end time (23:59:59)
        // dd($startDate, $endDate);
        $currentDateTime = Carbon::now()->startOfHour(); // Current hour's start time
        $currentDateTime1 = Carbon::now()->endOfHour(); // Current hour's start time


        $sessions = [
            'Overnight'  => ['start' => 0, 'end' => 7],
            'Morning'    => ['start' => 8, 'end' => 11],
            'Afternoon'  => ['start' => 12, 'end' => 15],
            'Evening'    => ['start' => 16, 'end' => 19],
            'Night'      => ['start' => 20, 'end' => 23],
        ];

        $SiteData = DB::table('site_data_by_minutes')
            ->where('site_id', 6)  // Fetch data for site_id = 6 only
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // dd($SiteData);


        $allSessionData = [];

        foreach ($sessions as $sessionName => $timeRange) {
            $filteredEntries = $SiteData->filter(function ($entry) use ($timeRange) {
                $hour = Carbon::parse($entry->created_at)->hour;
                return $hour >= $timeRange['start'] && $hour <= $timeRange['end'];
            });

            // print_r($filteredEntries);
            // echo "<br>";

            if ($filteredEntries->isEmpty()) {
                continue; // Skip empty sessions
            }

            // Initialize counters
            $inCount = 0;
            $outCount = 0;
            $previousStatus = null;

            $min = 0;
            $max = 0;
            $flag = 1;

            $sensorTime = [];
            foreach ($filteredEntries as $entry) {
                if ($entry->data != "[]") {
                    $currentStatus = json_decode($entry->data, true);

                    if ($previousStatus) {
                        foreach ($currentStatus as $sensorId => $status) {
                            if (isset($previousStatus[$sensorId])) {
                                if ($status == "1" && $previousStatus[$sensorId] == "0") {
                                    $inCount++;
                                }
                                if ($status == "0" && $previousStatus[$sensorId] == "1") {
                                    $outCount++;
                                }
                            }
                        }
                    }

                    foreach ($currentStatus as $sensorId => $status) {
                        $sensorTime[] = [$sensorId, $status];
                    }

                    $previousStatus = $currentStatus;
                }

                $dataAnalysis = json_decode($entry->data_analysis, true);
                if ($dataAnalysis) {
                    if ($flag) {
                        $min = $dataAnalysis['Occupied'];
                        $max = $dataAnalysis['Occupied'];
                        $flag = 0;
                    }
                    $max = max($max, $dataAnalysis['Occupied']);
                    $min = min($min, $dataAnalysis['Occupied']);
                }
            }

            $groupedData = [];
            foreach ($sensorTime as $entry) {
                $sensorId = (string) $entry[0];
                $status = $entry[1];
                $groupedData[$sensorId][] = $status;
            }

            $minTime = 0;
            $maxTime = 0;
            $avgTime = 0;
            if (!empty($groupedData)) {
                $processedData = [];
                foreach ($groupedData as $sensorId => $statuses) {
                    $newArray = [];
                    foreach ($statuses as $status) {
                        $status = intval($status);
                        if ($status === 0) {
                            if (empty($newArray) || end($newArray) !== 0) {
                                $newArray[] = 0;
                            }
                        } else {
                            if (!empty($newArray)) {
                                $newArray[count($newArray) - 1]++;
                            } else {
                                $newArray[] = 1;
                            }
                        }
                    }

                    while (!empty($newArray) && end($newArray) === 0) {
                        array_pop($newArray);
                    }
                    array_push($processedData, $newArray);
                }

                $mergedData = array_merge(...array_filter($processedData));
                if (!empty($mergedData)) {
                    $avgTime = array_sum($mergedData) / count($mergedData);
                    $minTime = min($mergedData);
                    $maxTime = max($mergedData);
                }
            }

            // Get the date and day from the first entry in this session
            $firstEntryDate = Carbon::parse($filteredEntries->first()->created_at);
            $formattedDate = $firstEntryDate->toDateString(); // "YYYY-MM-DD"
            $dayName = $firstEntryDate->format('l'); // "Monday", "Tuesday", etc.

            $allSessionData[] = [
                'date' => $formattedDate,
                'day' => $dayName,
                'session' => $sessionName,
                'site_id' => 6,  // Fixed site ID
                'check_in_count' => $inCount,
                'check_out_count' => $outCount,
                'max_count' => $max,
                'min_count' => $min,
                'min_time' => $minTime,
                'max_time' => $maxTime,
                'avg_time' => $avgTime,
            ];
        }
        // $allSessionData = array_reverse($allSessionData);
        return $allSessionData;
    }

    public function test2()
    {
        $previousHourStart = Carbon::now()->subHour()->startOfHour(); // Start of the previous hour
        $previousHourEnd = Carbon::now()->subHour()->endOfHour();     // End of the previous hour
        $currentHour = Carbon::now()->format('Y-m-d H:00:00');
        $SiteData = DB::table('site_data_by_minutes')
            // ->select('site_id', DB::raw('MAX(id) as id'))
            // ->whereDate('created_at', Carbon::now())
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

            $sensorTime = []; // Store time occupied per sensor
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
            $groupedData = [];

            foreach ($sensorTime as $entry) {
                $sensorId = (string) $entry[0]; // Convert to string to handle numeric IDs consistently
                $status = $entry[1];

                // Group by sensor ID
                $groupedData[$sensorId][] = $status;
            }


            // if(!empty($groupedData)){
            //     return response($groupedData);
            //     $firstSensorId = array_key_first($groupedData);
            //     $previousStatuses = [1];

            //     foreach ($groupedData as $sensorId => $statuses) {
            //         if($sensorId != $firstSensorId){
            //             $lastStatus = end($previousStatuses);
            //             echo $firstSensorId." ".$lastStatus."<br>";
            //             $firstSensorId = $sensorId;
            //         }
            //         $previousStatuses = $statuses;
            //         // echo "Sensor ID: $sensorId\n";
            //         // echo "Statuses: " . implode(", ", $statuses) . "\n";
            //         // echo "Total Readings: " . count($statuses) . "\n";
            //         // echo "------------------------\n";
            //     }
            // }
            // $groupedData = [
            //     "16641053D" => [
            //         "1",
            //         "1",
            //         "1",
            //         "1",
            //         "1",
            //         "1",
            //         "1",
            //         "1",
            //         "1",
            //         "1"
            //     ],
            //     "1871760D" => [
            //         "0",
            //         "0",
            //         "0",
            //         "0",
            //         "0",
            //         "0",
            //         "0",
            //         "0",
            //         "0",
            //         "0"
            //     ],
            //     "2210672D" => [
            //         "1",
            //         "1",
            //         "1",
            //         "1",
            //         "1",
            //         "1",
            //         "1",
            //         "1",
            //         "1",
            //         "1"
            //     ]
            // ];
            // return $groupedData;
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
                dd($mergedData);
                $avgTime = array_sum($mergedData) / count($mergedData);
                $minTime = min($mergedData);
                $maxTime = max($mergedData);
            }


            // Calculate min_time, max_time, and avg_time
            // $minTime = !empty($sensorTime) ? floor(min($sensorTime)) : 0;
            // $maxTime = !empty($sensorTime) ? floor(max($sensorTime)) : 0;
            // $avgTime = !empty($sensorTime) ? floor(array_sum($sensorTime) / count($sensorTime)) : 0;




            $dataStore = [
                'site_id' => $siteId,
                'date_time_slot' => $currentHour,
                'check_in_count' => $inCount,
                'check_out_count' => $outCount,
                'max_count' => $max,
                'min_count' => $min,
                'min_time' => $minTime,
                'max_time' => $maxTime,
                'avg_time' => $avgTime,
            ];

            print_r($dataStore);
            echo "<br>";

            // site_data_by_hour::create($dataStore);
        }

        // foreach ($FloorData as $floorId => $entries) {

        //     // Initialize counters
        //     $inCount = 0;
        //     $outCount = 0;

        //     // Track the previous status
        //     $previousStatus = null;

        //     $min = 0;
        //     $max = 0;
        //     $flag = 1;

        //     $sensorTime = []; // Store time occupied per sensor

        //     foreach ($entries as $entry) {
        //         $siteid = $entry->site_id;
        //         if ($entry->data != "[]") {

        //             $currentStatus = json_decode($entry->data, true);

        //             if ($previousStatus) {
        //                 foreach ($currentStatus as $sensorId => $status) {
        //                     if (isset($previousStatus[$sensorId])) {
        //                         // Check for IN (0 → 1)
        //                         if ($status == "1" && $previousStatus[$sensorId] == "0") {
        //                             $inCount++;
        //                         }

        //                         // Check for OUT (1 → 0)
        //                         if ($status == "0" && $previousStatus[$sensorId] == "1") {
        //                             $outCount++;
        //                         }
        //                     }
        //                 }
        //             }

        //             foreach ($currentStatus as $sensorId => $status) {
        //                 $sensorTime[] = [$sensorId, $status];
        //             }

        //             // Update previous status
        //             $previousStatus = $currentStatus;
        //         }

        //         if ($entry->created_at != $currentHour) {
        //             $dataAnalysis = json_decode($entry->data_analysis, true);
        //             if ($dataAnalysis) {
        //                 if ($flag) {
        //                     $min = $dataAnalysis['Occupied'];
        //                     $max = $dataAnalysis['Occupied'];
        //                     $flag = 0;
        //                 }
        //                 if ($dataAnalysis['Occupied'] > $max) {
        //                     $max = $dataAnalysis['Occupied'];
        //                 }
        //                 if ($dataAnalysis['Occupied'] < $min) {
        //                     $min = $dataAnalysis['Occupied'];
        //                 }
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

        //     $minTime = 0;
        //     $maxTime = 0;
        //     $avgTime = 0;
        //     if (!empty($groupedData)) {
        //         $processedData = [];

        //         foreach ($groupedData as $sensorId => $statuses) {
        //             $newArray = []; // Stores transformed values

        //             foreach ($statuses as $status) {
        //                 $status = intval($status); // Ensure it's an integer

        //                 if ($status === 0) {
        //                     // Add 0 if the last element is not already 0
        //                     if (empty($newArray) || end($newArray) !== 0) {
        //                         $newArray[] = 0;
        //                     }
        //                 } else {
        //                     // Increment last element if it's not empty
        //                     if (!empty($newArray)) {
        //                         $newArray[count($newArray) - 1]++;
        //                     } else {
        //                         $newArray[] = 1;
        //                     }
        //                 }
        //             }

        //             // Remove trailing zeros (optional cleanup step)
        //             while (!empty($newArray) && end($newArray) === 0) {
        //                 array_pop($newArray);
        //             }

        //             // $processedData[$sensorId] = $newArray;
        //             // $processedData[] = $newArray;
        //             array_push($processedData, $newArray);
        //         }
        //         $mergedData = array_merge(...array_filter($processedData));
        //         // dd($mergedData);
        //         $avgTime = array_sum($mergedData) / count($mergedData);
        //         $minTime = min($mergedData);
        //         $maxTime = max($mergedData);
        //     }

        //     // echo $avgTime . ' ' . $minTime . ' ' . $maxTime . "<br>";

        //     $dataStore = [
        //         'floor_id' => $floorId,
        //         'site_id' => $siteid,
        //         'date_time_slot' => $currentHour,
        //         'check_in_count' => $inCount,
        //         'check_out_count' => $outCount,
        //         'max_count' => $max,
        //         'min_count' => $min,
        //         'min_time' => $minTime,
        //         'max_time' => $maxTime,
        //         'avg_time' => $avgTime,
        //     ];
        //     floor_data_by_hour::create($dataStore);
        // }
    }

    public function historyData($zonal, $slot)
    {
        $data = DB::table('tbl_historical_data_by_minute')
            ->whereDate('updated_at', '2025-06-07') // Replace 'your_date_column' with the actual column name
            ->where('zone_code', $zonal)
            ->pluck($slot);

        $streaks = [];
        $count = 0;

        foreach ($data as $value) {
            if ($value == 1) {
                $count++;
            } else {
                if ($count > 0) {
                    $streaks[] = $count;
                    $count = 0;
                }
            }
        }

        // Push the final streak if the sequence ended on 1
        if ($count > 0) {
            $streaks[] = $count;
        }

        dd($streaks);

        dd($data);
    }
}
