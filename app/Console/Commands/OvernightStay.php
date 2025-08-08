<?php

namespace App\Console\Commands;

use App\Models\overnight_occupancy;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OvernightStay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'overnightstay:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Overstay Data Entry';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $start = Carbon::today()->format('Y-m-d 00:00:00');
        $end = Carbon::today()->format('Y-m-d 07:59:59');
        $floorData = DB::table('floor_data_by_minutes')
            // ->select('site_id', DB::raw('MAX(id) as id'))
            // ->where('floor_id', $floor_id)
            ->whereBetween('created_at', [$start, $end])
            // ->whereDate('created_at', Carbon::yesterday())
            ->get()
            ->groupBy('floor_id');
        // dd($floorData);

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
                foreach ($groupedData as $sensorId => $statuses) {
                    $checkValues = [0, 2, 4]; // the values you want to check

                    if (!empty(array_intersect($checkValues, $statuses))) {
                    } else {
                        $sensorID = getSensorId($sensorId);
                        $sensors[] = $sensorID;
                        // echo $sensorId . "<br>";
                    }
                }
            }
            $dataStore = [
                'floor_id' => (int) $floorId,
                'site_id' => (int) $siteId,
                'data' => json_encode($sensors),
            ];
            overnight_occupancy::create($dataStore);
        }
    }
}
