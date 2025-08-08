<?php

namespace App\Http\Controllers;

use App\Models\floor_data_by_minute;
use App\Models\site_info;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class financialModelController extends Controller
{
    public function financialmodel()
    {
        $date = Carbon::yesterday()->toDateString();

        // $FloorData = DB::table('floor_data_by_minutes')
        //     // ->select('site_id', DB::raw('MAX(id) as id'))
        //     ->whereDate('created_at', $date)
        //     ->get()
        //     ->groupBy('floor_id');
        // dd($FloorData);
        // exit;

        $FloorData = floor_data_by_minute::whereDate('created_at', Carbon::today())->where('floor_id', 6)->get();

        // Initialize counters
        $inCount = 0;
        $outCount = 0;

        // Track the previous status
        $previousStatus = null;

        $min = 0;
        $max = 0;
        $flag = 1;

        $sensorTime = []; // Store time occupied per sensor

        foreach ($FloorData as $entry) {
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

        $groupedData = [];


        foreach ($sensorTime as $entry) {
            $sensorId = (string) $entry[0]; // Convert to string to handle numeric IDs consistently
            $status = $entry[1];

            // Group by sensor ID
            $groupedData[$sensorId][] = $status;
        }

        // dd($groupedData);


        if (!empty($groupedData)) {
            $result = [];
            foreach ($groupedData as $sensorId => $statuses) {
                $count = 0;

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

                // Capture any final trailing sequence of 1s
                if ($count > 0) {
                    $result[] = $count;
                }
            }
        }



        // dd($result);
        // 0 => 195
        // 1 => 153
        // 2 => 42
        $prices = [];
        foreach($result as $value){
            echo $value;
            $prices[] = getPrice($value);
        }
        dd($prices);
        
        // [{1:30},{2:50},{6:80},{12:120}]

        $dataStore = [
            'floor_id' => 6,
            'site_id' => $siteid,
            'date' => $date,
            'check_in_count' => $inCount,
            'check_out_count' => $outCount,
            'max_count' => $max,
            'min_count' => $min,
            // 'min_time' => $minTime,
            // 'max_time' => $maxTime,
            // 'avg_time' => $avgTime,
        ];


        // dd($dataStore);
    }
}
