<?php

namespace App\Http\Controllers;

use App\Models\eece_data_logging_floor;
use App\Models\eece_data_logging_site;
use App\Models\eecs_device_info;
use App\Models\floor_info;
use App\Models\site_info;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class eecsDashboardController extends Controller
{
    public function getEecsSiteData()
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

    public function eecsDetailedChartData()
    {
        $site_id = Auth::user()->site_id;
        $today = Carbon::today();
        $floorWiseData = [];
        $eecsSiteData = eece_data_logging_site::whereDate('created_at', $today)->where('site_id', $site_id)->get();

        $chartData = [];
        $columnData = [];
        if (Auth::user()->chart_view == 1) {
            foreach ($eecsSiteData as $eecs) {
                $epochTime = (int)str_pad(strtotime($eecs->created_at), 13, 0, STR_PAD_RIGHT);
                $chartData[] = [
                    'date' => $epochTime,
                    'value' => $eecs->occupied,
                ];
            }
        }
        if (Auth::user()->chart_view == 2) {
            foreach ($eecsSiteData as $eecs) {
                $epochTime = (int)str_pad(strtotime($eecs->created_at), 13, 0, STR_PAD_RIGHT);
                $chartData[] = [
                    'date' => $epochTime,
                    'value' => $eecs->available,
                ];
            }
        }
        if (Auth::user()->chart_view == 3) {
            $HourData = getTodayHoursData();
            $hoursArray12H = $HourData['hoursArray12H'];

            // Get the current hour (up to now)
            $currentHour = Carbon::now()->format('H');
            $InData = [];
            $OutData = [];

            for ($hour = 0; $hour <= $currentHour; $hour++) {
                $startHour = Carbon::today()->setHour($hour)->format('Y-m-d H:i:s'); // Start of the hour
                $endHour = Carbon::today()->setHour($hour)->addHour()->format('Y-m-d H:i:s'); // End of the hour

                // Initialize counters
                $inCount = 0;
                $outCount = 0;
                $filteredEntries = $eecsSiteData->filter(function ($entry) use ($startHour, $endHour) {
                    return $entry->created_at >= $startHour && $entry->created_at < $endHour;
                });

                foreach ($filteredEntries as $entry) {
                    if ($entry->count < 0) {
                        $outCount += abs($entry->count);
                    } elseif ($entry->count > 0) {
                        $inCount += $entry->count;
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

        $floorWiseData[] = [
            'name' => getSitename($site_id),
            'chart' => $chartData,
            'columndata' => $columnData,
        ];

        // floor data
        $floorIds = floor_info::where('site_id', $site_id)->pluck('floor_id')->toArray();
        // if (!in_array(191, $floorIds)) {
        //     return "fsfsd";
        // }
        // exit;
        $eecsFloorData = eece_data_logging_floor::whereDate('created_at', $today)->where('site_id', $site_id)->get()->groupBy('floor_id');
        foreach ($floorIds as $floorId) {
            $entries = $eecsFloorData->get($floorId, collect()); // empty if no data

            $chartData = [];
            $columnData = [];

            if ($entries->isNotEmpty()) {
                if (Auth::user()->chart_view == 1) {
                    foreach ($entries as $entry) {
                        $epochTime = (int) str_pad(strtotime($entry->created_at), 13, 0, STR_PAD_RIGHT);
                        $chartData[] = [
                            'date' => $epochTime,
                            'value' => $entry->occupied,
                        ];
                    }
                }

                if (Auth::user()->chart_view == 2) {
                    foreach ($entries as $entry) {
                        $epochTime = (int) str_pad(strtotime($entry->created_at), 13, 0, STR_PAD_RIGHT);
                        $chartData[] = [
                            'date' => $epochTime,
                            'value' => $entry->available,
                        ];
                    }
                }

                if (Auth::user()->chart_view == 3) {
                    // add your logic here
                    // column data start
                    $HourData = getTodayHoursData();
                    $hoursArray12H = $HourData['hoursArray12H'];

                    // Get the current hour (up to now)
                    $currentHour = Carbon::now()->format('H');
                    $InData = [];
                    $OutData = [];

                    for ($hour = 0; $hour <= $currentHour; $hour++) {
                        $startHour = Carbon::today()->setHour($hour)->format('Y-m-d H:i:s'); // Start of the hour
                        $endHour = Carbon::today()->setHour($hour)->addHour()->format('Y-m-d H:i:s'); // End of the hour

                        // Initialize counters
                        $inCount = 0;
                        $outCount = 0;
                        $filteredEntries = $entries->filter(function ($entry) use ($startHour, $endHour) {
                            return $entry->created_at >= $startHour && $entry->created_at < $endHour;
                        });

                        foreach ($filteredEntries as $entry) {
                            if ($entry->count < 0) {
                                $outCount += abs($entry->count);
                            } elseif ($entry->count > 0) {
                                $inCount += $entry->count;
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
            }

            $floorWiseData[] = [
                'name' => getFloorname($floorId),
                'chart' => $chartData,
                'columndata' => $columnData,
            ];
        }

        $count = count($floorWiseData);
        return response()->json(['data' => $floorWiseData, 'count' => $count]);
    }
}
