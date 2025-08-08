<?php

namespace App\Http\Controllers;

use App\Exports\SummaryReportExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class exportController extends Controller
{
    public function summaryReportExportFloor($floorId, $startDate, $endDate)
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

        return $combined_data;
    }

    public function summaryReportExport(Request $request)
    {
        $site_id = Auth::user()->site_id;
        $startDate = $request->startdate;
        $endDate = $request->enddate . ' 23:59:59'; // Extend end date to the full day
        $floorId = $request->floorId;
        $floorFlag = $request->floorFlag;
        // dd($request->all());
        // exit;
        if ($floorId != 'site' && $floorId > 0) {
            $combined_data = $this->summaryReportExportFloor($floorId, $startDate, $endDate);
            $floorFlag = 1;
            return Excel::download(new SummaryReportExport($startDate, $endDate, $combined_data, $floorFlag, $floorId), 'parkomate_summary_report.csv');
        } else {


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

            // dd($combined_data);
            // exit;

            return Excel::download(new SummaryReportExport($startDate, $endDate, $combined_data, $floorFlag, $floorId), 'parkomate_summary_report.csv');
        }
    }
}
