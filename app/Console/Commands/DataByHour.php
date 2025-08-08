<?php

namespace App\Console\Commands;

use App\Models\floor_data_by_hour;
use App\Models\site_data_by_hour;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DataByHour extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'databyhour:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Data By Hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
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
                // dd($mergedData);
                $avgTime = array_sum($mergedData) / count($mergedData);
                $avgTime = number_format($avgTime, 2);
                $minTime = min($mergedData);
                $maxTime = max($mergedData);
            }

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

            $sensorTime = []; // Store time occupied per sensor

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
                // dd($mergedData);
                $avgTime = array_sum($mergedData) / count($mergedData);
                $avgTime = number_format($avgTime, 2);
                $minTime = min($mergedData);
                $maxTime = max($mergedData);
            }

            $dataStore = [
                'floor_id' => $floorId,
                'site_id' => $siteid,
                'date_time_slot' => $currentHour,
                'check_in_count' => $inCount,
                'check_out_count' => $outCount,
                'max_count' => $max,
                'min_count' => $min,
                'min_time' => $minTime,
                'max_time' => $maxTime,
                'avg_time' => $avgTime,
            ];
            floor_data_by_hour::create($dataStore);
        }
    }
}
