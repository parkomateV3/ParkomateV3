<?php

namespace App\Http\Controllers;

use App\Models\floor_data_by_day;
use App\Models\sensor_data_logging;
use App\Models\site_data_by_day;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class historyController extends Controller
{
    private function getChartData($entries)
    {
        $chartData = [];
        $chartType = Auth::user()->chart_view;

        foreach ($entries as $entry) {
            $dataAnalysis = json_decode($entry->data_analysis, true);
            $value = $chartType == 1 ? $dataAnalysis['Occupied'] : ($chartType == 2 ? $dataAnalysis['Available'] : null);

            if ($value !== null) {
                $chartData[] = [
                    'date' => (int)str_pad(strtotime($entry->created_at), 13, 0, STR_PAD_RIGHT),
                    'value' => $value,
                ];
            }
        }
        return $chartData;
    }

    private function calculateHourlyData($id, $startDate, $endDate, $table = 'floor_data_by_minutes')
    {
        $hourData = getTodayHoursData();
        $hoursArray12H = $hourData['hoursArray12H'];
        // $currentHour = Carbon::now()->endOfDay()->hour;
        $currentHour = Carbon::now()->endOfDay()->format('H');
        $InData = array_fill(0, $currentHour + 1, 0);
        $OutData = array_fill(0, $currentHour + 1, 0);
        $previousStatus = [];

        for ($hour = 0; $hour <= $currentHour; $hour++) {
            $startHour = $startDate->copy()->setHour($hour)->format('Y-m-d H:i:s');
            $endHour = $endDate->copy()->setHour($hour)->format('Y-m-d H:i:s');

            $entries = DB::table($table)
                ->where($table === 'site_data_by_minutes' ? 'site_id' : 'floor_id', $id)
                ->whereBetween('created_at', [$startHour, $endHour])
                ->get();

            foreach ($entries as $entry) {
                if (!empty($entry->data)) {
                    $currentStatus = json_decode($entry->data, true);

                    foreach ($currentStatus as $sensorId => $status) {
                        if (isset($previousStatus[$sensorId])) {
                            if ($status == "1" && $previousStatus[$sensorId] == "0") {
                                $InData[$hour]++;
                            }
                            if ($status == "0" && $previousStatus[$sensorId] == "1") {
                                $OutData[$hour]++;
                            }
                        }
                    }
                    $previousStatus = $currentStatus;
                }
            }
        }

        return [
            'hoursArray12H' => $hoursArray12H,
            'InData' => $InData,
            'OutData' => $OutData,
        ];
    }

    public function getSiteChartData($startDate, $endDate)
    {
        $site_id = Auth::user()->site_id;
        $perMinuteData = DB::table('site_data_by_minutes')
            ->where('site_id', $site_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $columnData = [];
        $chartData = [];
        if (Auth::user()->chart_view == 3) {
            $columnData = $this->calculateHourlyData($site_id, $startDate, $endDate, 'site_data_by_minutes');
        }
        if (Auth::user()->chart_view == 1 || Auth::user()->chart_view == 2) {
            $chartData = $this->getChartData($perMinuteData);
        }

        $dayData = site_data_by_day::where('site_id', $site_id)
            ->whereDate('date', $startDate)
            ->first() ?? (object)[
                'check_in_count' => 0,
                'check_out_count' => 0,
                'max_count' => 0,
                'min_count' => 0,
                'min_time' => 0,
                'max_time' => 0,
                'avg_time' => 0,
            ];

        return [[
            'name' => getSitename($site_id),
            'chart' => $chartData,
            'columndata' => $columnData,
            'check_in_count' => $dayData->check_in_count,
            'check_out_count' => $dayData->check_out_count,
            'max_count' => $dayData->max_count,
            'min_count' => $dayData->min_count,
            'min_time' => $dayData->min_time,
            'max_time' => $dayData->max_time,
            'avg_time' => $dayData->avg_time,
        ]];
    }

    public function historySiteData($startDate, $endDate)
    {
        $chartType = Auth::user()->chart_view;
        $site_id = Auth::user()->site_id;
        $siteData = DB::table('site_data_by_days')
            ->where('site_id', $site_id)
            // ->whereDate('created_at', Carbon::today())
            // ->get();
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $chartData = [];
        $check_in_count = 0;
        $check_out_count = 0;
        $max_count = 0;
        $min_count = 0;
        $flag = 1;
        $min_amount = null;
        $max_amount = null;
        foreach ($siteData as $entry) {
            $check_in_count += $entry->check_in_count;
            $check_out_count += $entry->check_out_count;

            if ($flag) {
                $flag = 0;
                $min_count = $entry->min_count;
                $max_count = $entry->max_count;
            }
            if ($entry->min_count < $min_count) {
                $min_count = $entry->min_count;
            }
            if ($entry->max_count > $max_count) {
                $max_count = $entry->max_count;
            }

            $amount = (int) $entry->expected_amount;
            if (is_null($min_amount) || $amount < $min_amount) {
                $min_amount = $amount;
            }

            if (is_null($max_amount) || $amount > $max_amount) {
                $max_amount = $amount;
            }

            $epochTime = (int)str_pad(strtotime($entry->date), 13, 0, STR_PAD_RIGHT);
            if ($chartType == 1) {
                $chartData[] = [
                    'date' => $epochTime,
                    'checkIn' => $entry->check_in_count,
                    'checkOut' => $entry->check_out_count,
                ];
            } else if ($chartType == 2) {
                $chartData[] = [
                    'date' => $epochTime,
                    'checkIn' => $entry->min_count,
                    'checkOut' => $entry->max_count,
                ];
            } else {
                $chartData[] = [
                    'date' => $epochTime,
                    'checkIn' => $amount,
                ];
            }

            // $chartData[] = [
            //     'date' => $epochTime,
            //     'checkIn' => $chartType == 1 || $chartType == 3 ? $entry->check_in_count : $entry->min_count,
            //     'checkOut' => $chartType == 1 || $chartType == 3 ? $entry->check_out_count : $entry->max_count,
            // ];
        }
        return [[
            'name' => getSitename($site_id),
            'chart' => $chartData,
            'check_in_count' => $check_in_count,
            'check_out_count' => $check_out_count,
            'max_count' => $max_count,
            'min_count' => $min_count,
            'min_amount' => $min_amount,
            'max_amount' => $max_amount,
        ]];
    }

    public function historicalData(Request $req)
    {
        $site_id = Auth::user()->site_id;
        $siteType = getSiteType($site_id);
        if ($siteType == "eecs" || $siteType == "slot_reservation") {
            return redirect('dashboard/404');
        }
        $access = Auth::user()->access;
        $accessArray = explode(',', $access);
        $checkAccess = "historical-data";
        $flag = 0;
        if (in_array($checkAccess, $accessArray)) {
            $flag = 1;
        }
        if ($flag == 0) {
            return redirect('noaccess');
        }

        $startDate = $req->startDate ? Carbon::parse($req->startDate)->startOfDay() : Carbon::yesterday()->startOfDay();
        $endDate = $req->endDate ? Carbon::parse($req->endDate)->endOfDay() : Carbon::yesterday()->endOfDay();
        $active = 'history';

        if ($startDate->isSameDay($endDate)) {
            $floorData = DB::table('floor_data_by_minutes')
                ->where('site_id', $site_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get()
                ->groupBy('floor_id');

            $siteChartData = $this->getSiteChartData($startDate, $endDate);
            $floorWiseData = $siteChartData;
            $chartData = [];
            $columnData = [];
            foreach ($floorData as $floorId => $entries) {
                if (Auth::user()->chart_view == 3) {
                    $columnData = $this->calculateHourlyData($floorId, $startDate, $endDate);
                }
                if (Auth::user()->chart_view == 2 || Auth::user()->chart_view == 1) {
                    $chartData = $this->getChartData($entries);
                }

                $dayData = floor_data_by_day::where('floor_id', $floorId)
                    ->whereDate('date', $startDate)
                    ->first() ?? (object)[
                        'check_in_count' => 0,
                        'check_out_count' => 0,
                        'max_count' => 0,
                        'min_count' => 0,
                        'min_time' => 0,
                        'max_time' => 0,
                        'avg_time' => 0,
                    ];

                $floorWiseData[] = [
                    'name' => getFloorname($floorId),
                    'chart' => $chartData,
                    'columndata' => $columnData,
                    'check_in_count' => $dayData->check_in_count,
                    'check_out_count' => $dayData->check_out_count,
                    'max_count' => $dayData->max_count,
                    'min_count' => $dayData->min_count,
                    'min_time' => $dayData->min_time,
                    'max_time' => $dayData->max_time,
                    'avg_time' => $dayData->avg_time,
                ];
            }
            $count = count($floorWiseData);
            // $count = 1;
            // dd($floorWiseData);
            return view('dashboard.historical-data', compact('floorWiseData', 'startDate', 'endDate', 'active', 'count'));
        } else {
            $chartType = Auth::user()->chart_view;
            $siteChartData = $this->historySiteData($startDate, $endDate);
            $floorWiseData = $siteChartData;
            $getData = DB::table('floor_data_by_days')
                ->where('site_id', $site_id)
                // ->whereDate('created_at', Carbon::today())
                // ->get();
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->groupBy('floor_id');

            foreach ($getData as $floorId => $entries) {
                $chartData = [];
                $check_in_count = 0;
                $check_out_count = 0;
                $max_count = 0;
                $min_count = 0;
                $flag = 1;
                $min_amount = null;
                $max_amount = null;
                foreach ($entries as $entry) {
                    $check_in_count += $entry->check_in_count;
                    $check_out_count += $entry->check_out_count;

                    if ($flag) {
                        $flag = 0;
                        $min_count = $entry->min_count;
                        $max_count = $entry->max_count;
                    }
                    if ($entry->min_count < $min_count) {
                        $min_count = $entry->min_count;
                    }
                    if ($entry->max_count > $max_count) {
                        $max_count = $entry->max_count;
                    }

                    $amount = (int) $entry->expected_amount;
                    if (is_null($min_amount) || $amount < $min_amount) {
                        $min_amount = $amount;
                    }

                    if (is_null($max_amount) || $amount > $max_amount) {
                        $max_amount = $amount;
                    }

                    $epochTime = (int)str_pad(strtotime($entry->date), 13, 0, STR_PAD_RIGHT);
                    if ($chartType == 1) {
                        $chartData[] = [
                            'date' => $epochTime,
                            'checkIn' => $entry->check_in_count,
                            'checkOut' => $entry->check_out_count,
                        ];
                    } else if ($chartType == 2) {
                        $chartData[] = [
                            'date' => $epochTime,
                            'checkIn' => $entry->min_count,
                            'checkOut' => $entry->max_count,
                        ];
                    } else {
                        $chartData[] = [
                            'date' => $epochTime,
                            'checkIn' => $amount,
                        ];
                    }

                    // $chartData[] = [
                    //     'date' => $epochTime,
                    //     'checkIn' => $chartType == 1 || $chartType == 3 ? $entry->check_in_count : $entry->min_count,
                    //     'checkOut' => $chartType == 1 || $chartType == 3 ? $entry->check_out_count : $entry->max_count,
                    // ];
                }
                // dd($chartData);
                $floorWiseData[] = [
                    'name' => getFloorname($floorId),
                    'chart' => $chartData,
                    'check_in_count' => $check_in_count,
                    'check_out_count' => $check_out_count,
                    'max_count' => $max_count,
                    'min_count' => $min_count,
                    'min_amount' => $min_amount,
                    'max_amount' => $max_amount,
                ];
            }
            // dd($floorWiseData);

            // $floorWiseData = [];
            // foreach ($getData as $entry) {

            //     $epochTime = (int)str_pad(strtotime($entry->date), 13, 0, STR_PAD_RIGHT);

            //     $floorWiseData[] = [
            //         'date' => $epochTime,
            //         'checkIn' => $entry->check_in_count,
            //         'checkOut' => $entry->check_out_count,
            //     ];
            // }

            // dd($data);
            // exit;
            $count = count($floorWiseData);

            return view('dashboard.history-multiple-days', compact('floorWiseData', 'startDate', 'endDate', 'active', 'count'));
        }
    }

    public function testChart()
    {
        $site_id = Auth::user()->site_id;
        $startDate = Carbon::now()->subDays(2)->startOfDay();
        $endDate = Carbon::now()->subDays(2)->endOfDay();
        $active = 'historical-data';
        $floorData = DB::table('floor_data_by_minutes')
            ->where('site_id', $site_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy('floor_id');

        $floorWiseData = [];

        foreach ($floorData as $floorId => $entries) {

            // column data start
            $HourData = getTodayHoursData();
            $hoursArray12H = $HourData['hoursArray12H'];

            // Get the current hour (up to now)
            $currentHour = Carbon::now()->endOfDay()->format('H');
            $InData = [];
            $OutData = [];
            $columnData = [];

            if (Auth::user()->chart_view == 3) {
                // Loop to initialize data for every hour from 00:00:00 to the current hour
                for ($hour = 0; $hour <= $currentHour; $hour++) {
                    $startHour = $startDate->setHour($hour)->format('Y-m-d H:i:s'); // Start of the hour
                    $endHour = $endDate->setHour($hour)->format('Y-m-d H:i:s'); // End of the hour

                    echo $startHour . ' - ' . $endHour . '<br>';

                    // Initialize counters
                    $inCount = 0;
                    $outCount = 0;

                    // Track the previous status
                    $previousStatus = null;

                    $getData = DB::table('floor_data_by_minutes')
                        ->where('floor_id', $floorId)
                        // ->whereDate('created_at', $startDate)
                        ->whereBetween('created_at', [$startHour, $endHour])
                        ->get();

                    foreach ($getData as $entry) {
                        // $currentStatus = json_decode($entry->data_analysis, true);
                        // echo $currentStatus['Available'] . " " . $currentStatus['Occupied'] . " " . $entry->created_at . " " . $entry->floor_id;
                        // echo '<br>';
                        if (!empty($entry->data_analysis) && $entry->data_analysis != "[]") {
                            $currentStatus = json_decode($entry->data_analysis, true);

                            if ($previousStatus !== null) {
                                if (isset($currentStatus['Available']) && isset($previousStatus['Available'])) {
                                    $currentAvailable = $currentStatus['Available'];
                                    $previousAvailable = $previousStatus['Available'];

                                    if ($currentAvailable > $previousAvailable) {
                                        // Available count increased → OUT count increases
                                        $outCount++;
                                    } elseif ($currentAvailable < $previousAvailable) {
                                        // Available count decreased → IN count increases
                                        $inCount++;
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

            $dayData = floor_data_by_day::where('floor_id', $floorId)
                ->whereDate('date', $startDate)
                ->first();

            if ($dayData == null || $dayData == '') {
                $dayData = (object)[
                    'check_in_count' => 0,
                    'check_out_count' => 0,
                    'max_count' => 0,
                    'min_count' => 0,
                    'min_time' => 0,
                    'max_time' => 0,
                    'avg_time' => 0,
                ];
            }

            $floorWiseData[] = [
                'name' => getFloorname($floorId),
                'chart' => $chartData,
                'columndata' => $columnData,
                'check_in_count' => $dayData->check_in_count,
                'check_out_count' => $dayData->check_out_count,
                'max_count' => $dayData->max_count,
                'min_count' => $dayData->min_count,
                'min_time' => $dayData->min_time,
                'max_time' => $dayData->max_time,
                'avg_time' => $dayData->avg_time,
            ];
        }

        // dd($floorWiseData);

        // // $siteChartData = $this->getSiteChartData($startDate, $endDate);
        // // $floorWiseData = $siteChartData;
        // $floorWiseData = [];
        // $chartData = [];
        // $columnData = [];
        // foreach ($floorData as $floorId => $entries) {
        //     if (Auth::user()->chart_view == 3) {
        //         $columnData = $this->calculateHourlyData($floorId, $startDate, $endDate);
        //     }
        //     if (Auth::user()->chart_view == 2 || Auth::user()->chart_view == 1) {
        //         $chartData = $this->getChartData($entries);
        //     }

        //     $dayData = floor_data_by_day::where('floor_id', $floorId)
        //         ->whereDate('date', $startDate)
        //         ->first() ?? (object)[
        //             'check_in_count' => 0,
        //             'check_out_count' => 0,
        //             'max_count' => 0,
        //             'min_count' => 0,
        //             'min_time' => 0,
        //             'max_time' => 0,
        //             'avg_time' => 0,
        //         ];

        //     $floorWiseData[] = [
        //         'name' => getFloorname($floorId),
        //         'chart' => $chartData,
        //         'columndata' => $columnData,
        //         'check_in_count' => $dayData->check_in_count,
        //         'check_out_count' => $dayData->check_out_count,
        //         'max_count' => $dayData->max_count,
        //         'min_count' => $dayData->min_count,
        //         'min_time' => $dayData->min_time,
        //         'max_time' => $dayData->max_time,
        //         'avg_time' => $dayData->avg_time,
        //     ];
        // }
        // $count = count($floorWiseData);

        // dd($floorWiseData);
    }

    // public function getSiteChartData($startDate, $endDate)
    // {
    //     $startDate = Carbon::parse($startDate); // Convert string to Carbon instance
    //     $endDate = Carbon::parse($endDate); // Convert string to Carbon instance
    //     $site_id = Auth::user()->site_id;
    //     $perminutedata = DB::table('site_data_by_minutes')
    //         ->where('site_id', $site_id)
    //         ->whereDate('created_at', $startDate)
    //         ->get();


    //     // column data start
    //     $HourData = getTodayHoursData();
    //     $hoursArray12H = $HourData['hoursArray12H'];

    //     // Get the current hour (up to now)
    //     $currentHour = Carbon::now()->format('H');
    //     $InData = [];
    //     $OutData = [];
    //     $columnData = [];

    //     if (Auth::user()->chart_view == 3) {
    //         // Loop to initialize data for every hour from 00:00:00 to the current hour
    //         for ($hour = 0; $hour <= $currentHour; $hour++) {
    //             $startHour = $startDate->setHour($hour)->format('Y-m-d H:i:s'); // Start of the hour
    //             $endHour = $endDate->setHour($hour)->format('Y-m-d H:i:s'); // End of the hour

    //             // Initialize counters
    //             $inCount = 0;
    //             $outCount = 0;

    //             // Track the previous status
    //             $previousStatus = null;

    //             $getData = DB::table('site_data_by_minutes')
    //                 ->where('site_id', $site_id)
    //                 // ->whereDate('created_at', $startDate)
    //                 ->whereBetween('created_at', [$startHour, $endHour])
    //                 ->get();

    //             foreach ($getData as $entry) {
    //                 if ($entry->data != "[]") {

    //                     $currentStatus = json_decode($entry->data, true);

    //                     if ($previousStatus) {
    //                         foreach ($currentStatus as $sensorId => $status) {
    //                             if (isset($previousStatus[$sensorId])) {
    //                                 // Check for IN (0 → 1)
    //                                 if ($status == "1" && $previousStatus[$sensorId] == "0") {
    //                                     $inCount++;
    //                                 }

    //                                 // Check for OUT (1 → 0)
    //                                 if ($status == "0" && $previousStatus[$sensorId] == "1") {
    //                                     $outCount++;
    //                                 }
    //                             }
    //                         }
    //                     }

    //                     // Update previous status
    //                     $previousStatus = $currentStatus;
    //                 }
    //             }

    //             $InData[] = $inCount;
    //             $OutData[] = $outCount;
    //         }
    //         $columnData = [
    //             'hoursArray12H' => $hoursArray12H,
    //             'InData' => $InData,
    //             'OutData' => $OutData,
    //         ];
    //     }
    //     // column data end


    //     // per minute data start
    //     $chartData = [];
    //     if (Auth::user()->chart_view == 1) {
    //         foreach ($perminutedata as $entry) {
    //             $dataAnalysis = json_decode($entry->data_analysis, true);
    //             $Occupied = $dataAnalysis['Occupied'];

    //             $epochTime = (int)str_pad(strtotime($entry->created_at), 13, 0, STR_PAD_RIGHT);

    //             $chartData[] = [
    //                 'date' => $epochTime,
    //                 'value' => $Occupied,
    //             ];
    //         }
    //     }
    //     if (Auth::user()->chart_view == 2) {
    //         foreach ($perminutedata as $entry) {
    //             $dataAnalysis = json_decode($entry->data_analysis, true);
    //             $Available = $dataAnalysis['Available'];

    //             $epochTime = (int)str_pad(strtotime($entry->created_at), 13, 0, STR_PAD_RIGHT);

    //             $chartData[] = [
    //                 'date' => $epochTime,
    //                 'value' => $Available,
    //             ];
    //         }
    //     }
    //     // per minute data end

    //     $dayData = site_data_by_day::where('site_id', $site_id)
    //         ->whereDate('date', $startDate)
    //         ->first();

    //     if ($dayData == null || $dayData == '') {
    //         $dayData = (object)[
    //             'check_in_count' => 0,
    //             'check_out_count' => 0,
    //             'max_count' => 0,
    //             'min_count' => 0,
    //             'min_time' => 0,
    //             'max_time' => 0,
    //             'avg_time' => 0,
    //         ];
    //     }

    //     $floorWiseData[] = [
    //         'name' => getSitename($site_id),
    //         'chart' => $chartData,
    //         'columndata' => $columnData,
    //         'check_in_count' => $dayData->check_in_count,
    //         'check_out_count' => $dayData->check_out_count,
    //         'max_count' => $dayData->max_count,
    //         'min_count' => $dayData->min_count,
    //         'min_time' => $dayData->min_time,
    //         'max_time' => $dayData->max_time,
    //         'avg_time' => $dayData->avg_time,
    //     ];

    //     return $floorWiseData;
    // }

    // public function historicalData(Request $req)
    // {
    //     if ($req->startDate == null || $req->startDate == '') {
    //         // $startDate = Carbon::yesterday()->toDateString();
    //         // $startDate = Carbon::yesterday()->toDateString();
    //         $startDate = Carbon::yesterday()->startOfDay(); // Today 00:00:00
    //         $endDate = Carbon::yesterday()->endOfDay(); // Today 23:59:59
    //     } else {
    //         $startDate = $req->startDate . ' 00:00:00';
    //         $endDate = $req->endDate . ' 23:59:59';
    //         $startDate = Carbon::parse($startDate); // Convert string to Carbon instance
    //         $endDate = Carbon::parse($endDate); // Convert string to Carbon instance
    //         // $endDate = $req->endDate . ' 23:59:59';
    //     }

    //     $site_id = Auth::user()->site_id;

    //     $floorData = DB::table('floor_data_by_minutes')
    //         ->where('site_id', $site_id)
    //         // ->whereDate('created_at', $startDate)
    //         ->whereBetween('created_at', [$startDate, $endDate])
    //         ->get()
    //         ->groupBy('floor_id');

    //     $siteChartData = $this->getSiteChartData($startDate, $endDate);


    //     $floorWiseData = [];

    //     $floorWiseData = $siteChartData;
    //     foreach ($floorData as $floorId => $entries) {

    //         // column data start
    //         $HourData = getTodayHoursData();
    //         $hoursArray12H = $HourData['hoursArray12H'];

    //         // Get the current hour (up to now)
    //         $currentHour = Carbon::now()->format('H');
    //         $InData = [];
    //         $OutData = [];
    //         $columnData = [];

    //         if (Auth::user()->chart_view == 3) {
    //             // Loop to initialize data for every hour from 00:00:00 to the current hour
    //             for ($hour = 0; $hour <= $currentHour; $hour++) {
    //                 $startHour = $startDate->setHour($hour)->format('Y-m-d H:i:s'); // Start of the hour
    //                 $endHour = $endDate->setHour($hour)->format('Y-m-d H:i:s'); // End of the hour

    //                 // echo $startHour . ' - ' . $endHour . '<br>';

    //                 // Initialize counters
    //                 $inCount = 0;
    //                 $outCount = 0;

    //                 // Track the previous status
    //                 $previousStatus = null;

    //                 $getData = DB::table('floor_data_by_minutes')
    //                     ->where('floor_id', $floorId)
    //                     ->whereDate('created_at', $startDate)
    //                     ->whereBetween('created_at', [$startHour, $endHour])
    //                     ->get();

    //                 foreach ($getData as $entry) {
    //                     if ($entry->data != "[]") {

    //                         $currentStatus = json_decode($entry->data, true);

    //                         if ($previousStatus) {
    //                             foreach ($currentStatus as $sensorId => $status) {
    //                                 if (isset($previousStatus[$sensorId])) {
    //                                     // Check for IN (0 → 1)
    //                                     if ($status == "1" && $previousStatus[$sensorId] == "0") {
    //                                         $inCount++;
    //                                     }

    //                                     // Check for OUT (1 → 0)
    //                                     if ($status == "0" && $previousStatus[$sensorId] == "1") {
    //                                         $outCount++;
    //                                     }
    //                                 }
    //                             }
    //                         }

    //                         // Update previous status
    //                         $previousStatus = $currentStatus;
    //                     }
    //                 }

    //                 $InData[] = $inCount;
    //                 $OutData[] = $outCount;
    //             }
    //             $columnData = [
    //                 'hoursArray12H' => $hoursArray12H,
    //                 'InData' => $InData,
    //                 'OutData' => $OutData,
    //             ];
    //         }
    //         // column data end


    //         // per minute data start
    //         $chartData = [];
    //         if (Auth::user()->chart_view == 1) {
    //             foreach ($entries as $entry) {
    //                 $dataAnalysis = json_decode($entry->data_analysis, true);
    //                 $Occupied = $dataAnalysis['Occupied'];

    //                 $epochTime = (int)str_pad(strtotime($entry->created_at), 13, 0, STR_PAD_RIGHT);

    //                 $chartData[] = [
    //                     'date' => $epochTime,
    //                     'value' => $Occupied,
    //                 ];
    //             }
    //         }

    //         if (Auth::user()->chart_view == 2) {
    //             foreach ($entries as $entry) {
    //                 $dataAnalysis = json_decode($entry->data_analysis, true);
    //                 $Available = $dataAnalysis['Available'];

    //                 $epochTime = (int)str_pad(strtotime($entry->created_at), 13, 0, STR_PAD_RIGHT);

    //                 $chartData[] = [
    //                     'date' => $epochTime,
    //                     'value' => $Available,
    //                 ];
    //             }
    //         }
    //         // per minute data end

    //         $dayData = floor_data_by_day::where('floor_id', $floorId)
    //             ->whereDate('date', $startDate)
    //             ->first();

    //         if ($dayData == null || $dayData == '') {
    //             $dayData = (object)[
    //                 'check_in_count' => 0,
    //                 'check_out_count' => 0,
    //                 'max_count' => 0,
    //                 'min_count' => 0,
    //                 'min_time' => 0,
    //                 'max_time' => 0,
    //                 'avg_time' => 0,
    //             ];
    //         }

    //         $floorWiseData[] = [
    //             'name' => getFloorname($floorId),
    //             'chart' => $chartData,
    //             'columndata' => $columnData,
    //             'check_in_count' => $dayData->check_in_count,
    //             'check_out_count' => $dayData->check_out_count,
    //             'max_count' => $dayData->max_count,
    //             'min_count' => $dayData->min_count,
    //             'min_time' => $dayData->min_time,
    //             'max_time' => $dayData->max_time,
    //             'avg_time' => $dayData->avg_time,
    //         ];
    //     }
    //     $count = count($floorWiseData);
    //     // $count = 1;
    //     // dd($floorWiseData);
    //     $active = 'historical-data';
    //     // return response()->json(['data' => $floorWiseData, 'count' => $count]);
    //     return view('dashboard.historical-data', compact('floorWiseData', 'count', 'startDate', 'endDate', 'active'));
    // }
}
