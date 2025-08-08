<?php

namespace App\Console\Commands;

use App\Models\floor_data_by_minute;
use App\Models\floor_info;
use App\Models\site_info;
use App\Models\sensor_data_logging;
use App\Models\site_data_by_minute;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DataByMinute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'databyminute:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Data By Minute';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $siteData = site_info::get();
        $floorData = floor_info::get();
        $datetime = Carbon::now()->format('Y-m-d H:i:s');

        foreach ($siteData as $site) {
            $flag = 0;
            $checkemptydata = sensor_data_logging::where('site_id', $site['site_id'])->latest('updated_at')->first();
            if ($checkemptydata) {
                $flag = 1;
                // if (Carbon::now()->diffInMinutes($checkemptydata->updated_at) >= 15) {
                //     $flag = 0;
                // }
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
                    if ($sensor->status == 1 || $sensor->status == 2 || $sensor->status == 4) {
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
        foreach ($floorData as $floor) {
            $flag = 0;
            $checkemptydata = sensor_data_logging::where('floor_id', $floor['floor_id'])->latest('updated_at')->first();
            if ($checkemptydata) {
                $flag = 1;
                // if (Carbon::now()->diffInMinutes($checkemptydata->updated_at) >= 15) {
                //     $flag = 0;
                // }
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
                $sensorinfo = sensor_data_logging::where('floor_id', $floor['floor_id'])->get();
                if ($sensorinfo->isNotEmpty()) {
                    foreach ($sensorinfo as $sensor) {
                        $sensorData[$sensor->sensor] = $sensor->status;
                        if ($sensor->status == 1 || $sensor->status == 2 || $sensor->status == 4) {
                            $occupied++;
                        }
                        if ($sensor->status == 0) {
                            $available++;
                        }

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
                    }
                    $data = json_encode($sensorData);
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

                    $dataStore = [
                        'site_id' => $floor['site_id'],
                        'floor_id' => $floor['floor_id'],
                        'data' => $data,
                        'date_time' => $datetime,
                        // 'total_occupied' => $occupied,
                        // 'total_available' => $available,
                        'data_analysis' => $jsonDataAnalysis,
                    ];
                    floor_data_by_minute::create($dataStore);
                }
            }
        }
        // $data = json_encode($sensorData);
    }
}
