<?php

namespace App\Http\Controllers;

use App\Jobs\SendSensorMail;
use App\Models\display_info;
use App\Models\displaydata;
use App\Models\eece_data_logging_floor;
use App\Models\eece_data_logging_site;
use App\Models\eecs_data;
use App\Models\eecs_device_info;
use App\Models\eecs_sensor_info;
use App\Models\email_log;
use App\Models\floor_info;
use App\Models\reservation_device_info;
use App\Models\sensor_data_logging;
use App\Models\sensor_info;
use App\Models\sensor_reservation;
use App\Models\site_info;
use App\Models\symbol_on_display;
use App\Models\upload_image;
use App\Models\vehicle_count;
use App\Models\zonal_info;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class apiController extends Controller
{
    public function updateVehicleCount(Request $request)
    {

        $data = vehicle_count::where('vehicle', $request->vehicle)->first();

        if ($data) {
            if ($request->number > 0) {
                $data->count = $data->count + $request->number;
            } else {
                $data->count = $data->count - abs($request->number);
            }
            $data->update();

            return response()->json(['status' => 1]);
        } else {
            return response()->json(['status' => 0]);
        }
    }

    public function getVehicleCount()
    {
        $data = DB::table('vehicle_counts')->select('vehicle', 'count')->get();

        $formatted = [];
        foreach ($data as $item) {
            $formatted[$item->vehicle] = $item->count;
        }

        return response()->json($formatted);
    }

    public function getDisplayOld(Request $request)
    {
        // if ($request->json('display_no') != null || $request->json('display_no') != '') {
        //     $data = display_info::where('display_unique_no', $request->json('display_no'))->first();
        if ($request->display_no != null || $request->display_no != '') {
            $data = display_info::where('display_unique_no', $request->display_no)->first();
            if (empty($data)) {
                $emailLogZ = email_log::whereDate('created_at', Carbon::today())->where('sensor_id', $request->display_no)->latest('created_at')->first();

                if ($emailLogZ) {
                    $diffInMinutesZ = Carbon::now()->diffInMinutes($emailLogZ->created_at);
                    // Now you can check like:
                    if ($diffInMinutesZ > 120) {
                        // $email = "ajay.ladkat@parkomate.com";
                        $email = "maheshyangandul@gmail.com";
                        $mailData = [
                            'from_email' => $email,
                            'from_name' => "Display not registered",
                            'subject' => "Display not registered",
                            'site_name' => 1,
                            'floor_name' => 1,
                            'zonal_name' => 1,
                            'sensor_unique_no' => 1,
                            'sensor_name' => 1,
                            'sensor_id' => $request->display_no,
                            'status' => 1,
                            'date' => Carbon::now(),
                            'device' => 'display'
                        ];
                        SendSensorMail::dispatch($email, $mailData);
                    }
                } else {
                    // $email = "ajay.ladkat@parkomate.com";
                    $email = "maheshyangandul@gmail.com";
                    $mailData = [
                        'from_email' => $email,
                        'from_name' => "Display not registered",
                        'subject' => "Display not registered",
                        'site_name' => 1,
                        'floor_name' => 1,
                        'zonal_name' => 1,
                        'sensor_unique_no' => 1,
                        'sensor_name' => 1,
                        'sensor_id' => $request->display_no,
                        'status' => 1,
                        'date' => Carbon::now(),
                        'device' => 'display'
                    ];
                    SendSensorMail::dispatch($email, $mailData);
                }
                return response()->json(['S' => 1, 'C' => 7]);
            }

            if (isset($data)) {
                $id = $data->display_id;
                $displayData = displaydata::where('display_id', $id)->get();
                $formattedSensors = [];
                // $colorMap = [
                //     'red' => 'R',
                //     'green' => 'G',
                //     'blue' => 'B',
                //     'yellow' => 'Y',
                //     'cyan' => 'C',
                //     'magenta' => 'M',
                //     'white' => 'W',
                // ];
                if (count($displayData) > 0) {

                    foreach ($displayData as $index => $display) {
                        // Convert JSON to an associative array
                        $array = json_decode($display->floor_zonal_sensor_ids, true);

                        // Separate the values for Floor, Zonals, and Sensors
                        $floors = explode(',', $array['Floor']);  // ['2', '4']
                        $zonals = explode(',', $array['Zonals']); // ['4', '5']
                        $sensors = explode(',', $array['Sensors']); // ['4']
                        // "floors" => $floors[0] == "" ? 'empty' : 'no empty',
                        $logic_to_calculate_color = explode(',', $display->logic_calculate_number);

                        $sensorDataLog = sensor_data_logging::where('site_id', $display->site_id)->get();

                        $SensorData = [];
                        $output = "";
                        $finalOutput = "";

                        $dformat = $display->display_format;
                        if (str_starts_with(trim($dformat), '{')) {
                            $decoded = json_decode($dformat, true);
                            $link = $decoded['link'] ?? null;
                            $depth = $decoded['depth'] ?? null;
                            $format = $decoded['format'] ?? null;
                            $response = Http::get($link);
                            if ($response->successful()) {
                                $responseData = $response->json();

                                // Step 3: Extract using $depth
                                $depthKeys = explode(',', $depth); // ['floors', 'P1', 'available_slots']
                                $value = $responseData;

                                foreach ($depthKeys as $key) {
                                    if (is_array($value) && array_key_exists($key, $value)) {
                                        $value = $value[$key];
                                    } else {
                                        $value = null;
                                        break;
                                    }
                                }

                                // return $value;

                                $starCount = substr_count($format, '*');
                                $output = sprintf("%0{$starCount}d", $value); // Format count to match the star count
                                $finalOutput = str_replace(str_repeat('*', $starCount), $output, $format);


                                // Step 4: Output value
                                // return "Extracted value: " . $finalOutput;
                                // return $responseData;
                            } else {
                                return "x";
                            }
                            // dd($response);
                        } else {
                            if ($floors[0] == "" && $zonals[0] == "" && $sensors[0] == "") {
                                // foreach ($sensorDataLog as $log) {
                                //     if (in_array($log->color, $logic_to_calculate_color)) {
                                //         $SensorData[] = 
                                //     }
                                // }
                                $finalOutput = $display->display_format;
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
                                $uniqueCount = count(array_unique($SensorData));

                                // Count the number of '*'
                                $starCount = substr_count($display->display_format, '*');

                                // Dynamically format count based on the star count
                                if ($starCount > 0) {
                                    if($display->math != null || $display->math != ''){
                                        $uniqueCount = $uniqueCount + $display->math; // Add math logic if provided
                                    }
                                    $output = sprintf("%0{$starCount}d", $uniqueCount); // Format count to match the star count
                                    $finalOutput = str_replace(str_repeat('*', $starCount), $output, $display->display_format);
                                }
                            }
                        }

                        // $color = $colorMap[strtolower($display["color"])] ?? 'U';
                        $fontsize = $display->font_size . $display->font;
                        $formattedSensors[$index + 1] = [
                            // $formattedSensors[] = [
                            "I" => $data->intensity,
                            // "P" => $data->panels,
                            "C" => $display->coordinates,
                            "F" => $fontsize,
                            "T" => $display->color,
                            // "D" => $display->display_format,
                            "D" => $finalOutput,
                            // "color" => $logic_to_calculate_color,
                            // "D" => $jsonData,
                        ];
                    }
                    // $getDisplayData = display_info::all();
                    $count = count($formattedSensors);
                    $symbolData = symbol_on_display::where('display_id', $data->display_id)->get();
                    if (count($symbolData) > 0) {
                        foreach ($symbolData as $symbol) {

                            // $symbolColor = $colorMap[strtolower($symbol->color)] ?? 'U';

                            $formattedSensors[$count + 1] = [
                                // $formattedSensors[] = [
                                "I" => $data->intensity,
                                "C" => $symbol->coordinates,
                                "S" => $symbol->color,
                                "B" => symbolBinaryData($symbol->symbol_to_show),
                                "Z" => symbolSize($symbol->symbol_to_show),
                            ];

                            $count++;
                        }
                    }

                    // return response()->json($count)->header('Content-Type', 'application/json');
                    return response()->json($formattedSensors)->header('Content-Type', 'application/json');
                    // return response()->json(['S' => 1, 'C' => 200, 'D' => $formattedSensors])->header('Content-Type', 'application/json');
                } else {

                    //if display data not found
                    $symbolData = symbol_on_display::where('display_id', $data->display_id)->get();
                    if (count($symbolData) > 0) {
                        foreach ($symbolData as $index => $symbol) {

                            // $symbolColor = $colorMap[strtolower($symbol->color)] ?? 'U';

                            $formattedSensors[$index + 1] = [
                                // $formattedSensors[] = [
                                "I" => $data->intensity,
                                "C" => $symbol->coordinates,
                                "S" => $symbol->color,
                                "B" => symbolBinaryData($symbol->symbol_to_show),
                                "Z" => symbolSize($symbol->symbol_to_show),
                            ];
                        }
                        return response()->json($formattedSensors);
                        // return response()->json(['S' => 1, 'C' => 200, 'D' => $formattedSensors]);
                    } else {
                        return response()->json(['1' => 'None']);
                        // return response()->json(['S' => 0, 'C' => 2]);
                    }
                }
            } else {
                return response()->json(['1' => 'unr']);
                // return response()->json(['S' => 0, 'C' => 1]);
            }
        } else {
            return response()->json(['1' => "None"]);
            // return response()->json(['S' => 0, 'C' => 0]);
        }


        // return response()->json($getDisplayData);
        // return response()->json($request->all());
        // return response()->json(["data" => "F1 123"]);
    }

    public function getDisplay(Request $request)
    {
        if ($request->json('display_no') != null || $request->json('display_no') != '') {
            $data = display_info::where('display_unique_no', $request->json('display_no'))->first();
            if (empty($data)) {
                $emailLogZ = email_log::whereDate('created_at', Carbon::today())->where('sensor_id', $request->json('display_no'))->latest('created_at')->first();

                if ($emailLogZ) {
                    $diffInMinutesZ = Carbon::now()->diffInMinutes($emailLogZ->created_at);
                    // Now you can check like:
                    if ($diffInMinutesZ > 120) {
                        // $email = "ajay.ladkat@parkomate.com";
                        $email = "maheshyangandul@gmail.com";
                        $mailData = [
                            'from_email' => $email,
                            'from_name' => "Display not registered",
                            'subject' => "Display not registered",
                            'site_name' => 1,
                            'floor_name' => 1,
                            'zonal_name' => 1,
                            'sensor_unique_no' => 1,
                            'sensor_name' => 1,
                            'sensor_id' => $request->json('display_no'),
                            'status' => 1,
                            'date' => Carbon::now(),
                            'device' => 'display'
                        ];
                        SendSensorMail::dispatch($email, $mailData);
                    }
                } else {
                    // $email = "ajay.ladkat@parkomate.com";
                    $email = "maheshyangandul@gmail.com";
                    $mailData = [
                        'from_email' => $email,
                        'from_name' => "Display not registered",
                        'subject' => "Display not registered",
                        'site_name' => 1,
                        'floor_name' => 1,
                        'zonal_name' => 1,
                        'sensor_unique_no' => 1,
                        'sensor_name' => 1,
                        'sensor_id' => $request->json('display_no'),
                        'status' => 1,
                        'date' => Carbon::now(),
                        'device' => 'display'
                    ];
                    SendSensorMail::dispatch($email, $mailData);
                }
                return response()->json(['S' => 1, 'C' => 7]);
            }
            DB::table('display_infos')
                ->where('display_unique_no', $request->display_no)
                ->update(['updated_at' => now()]);

            if (isset($data)) {
                $id = $data->display_id;
                $displayData = displaydata::where('display_id', $id)->get();
                $formattedSensors = [];
                // $colorMap = [
                //     'red' => 'R',
                //     'green' => 'G',
                //     'blue' => 'B',
                //     'yellow' => 'Y',
                //     'cyan' => 'C',
                //     'magenta' => 'M',
                //     'white' => 'W',
                // ];
                if (count($displayData) > 0) {

                    foreach ($displayData as $index => $display) {
                        // Convert JSON to an associative array
                        $array = json_decode($display->floor_zonal_sensor_ids, true);

                        // Separate the values for Floor, Zonals, and Sensors
                        $floors = explode(',', $array['Floor']);  // ['2', '4']
                        $zonals = explode(',', $array['Zonals']); // ['4', '5']
                        $sensors = explode(',', $array['Sensors']); // ['4']
                        // "floors" => $floors[0] == "" ? 'empty' : 'no empty',
                        $logic_to_calculate_color = explode(',', $display->logic_calculate_number);

                        $sensorDataLog = sensor_data_logging::where('site_id', $display->site_id)->get();

                        $SensorData = [];
                        $output = "";
                        $finalOutput = "";
                        if ($floors[0] == "" && $zonals[0] == "" && $sensors[0] == "") {
                            // foreach ($sensorDataLog as $log) {
                            //     if (in_array($log->color, $logic_to_calculate_color)) {
                            //         $SensorData[] = 
                            //     }
                            // }
                            $finalOutput = $display->display_format;
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
                            $uniqueCount = count(array_unique($SensorData));

                            // Count the number of '*'
                            $starCount = substr_count($display->display_format, '*');

                            // Dynamically format count based on the star count
                            if ($starCount > 0) {
                                $output = sprintf("%0{$starCount}d", $uniqueCount); // Format count to match the star count
                                $finalOutput = str_replace(str_repeat('*', $starCount), $output, $display->display_format);
                            }
                        }

                        // $color = $colorMap[strtolower($display["color"])] ?? 'U';
                        $fontsize = $display->font_size . $display->font;
                        $formattedSensors[] = [
                            "I" => $data->intensity,
                            // "P" => $data->panels,
                            "C" => $display->coordinates,
                            "F" => $fontsize,
                            "T" => $display->color,
                            // "D" => $display->display_format,
                            "D" => $finalOutput,
                            // "color" => $logic_to_calculate_color,
                            // "D" => $jsonData,
                        ];
                    }
                    // $getDisplayData = display_info::all();
                    $count = count($formattedSensors);
                    $symbolData = symbol_on_display::where('display_id', $data->display_id)->get();
                    if (count($symbolData) > 0) {
                        foreach ($symbolData as $symbol) {

                            // $symbolColor = $colorMap[strtolower($symbol->color)] ?? 'U';

                            $formattedSensors[] = [
                                "I" => $data->intensity,
                                "C" => $symbol->coordinates,
                                "S" => $symbol->color,
                                "B" => symbolBinaryData($symbol->symbol_to_show),
                                "Z" => symbolSize($symbol->symbol_to_show),
                            ];

                            $count++;
                        }
                    }

                    return response()->json(['S' => 1, 'C' => 200, 'D' => $formattedSensors])->header('Content-Type', 'application/json');
                } else {

                    //if display data not found
                    $symbolData = symbol_on_display::where('display_id', $data->display_id)->get();
                    if (count($symbolData) > 0) {
                        foreach ($symbolData as $index => $symbol) {

                            // $symbolColor = $colorMap[strtolower($symbol->color)] ?? 'U';

                            $formattedSensors[] = [
                                "I" => $data->intensity,
                                "C" => $symbol->coordinates,
                                "S" => $symbol->color,
                                "B" => symbolBinaryData($symbol->symbol_to_show),
                                "Z" => symbolSize($symbol->symbol_to_show),
                            ];
                        }
                        return response()->json(['S' => 1, 'C' => 200, 'D' => $formattedSensors]);
                    } else {
                        return response()->json(['S' => 0, 'C' => 2]);
                    }
                }
            } else {
                return response()->json(['S' => 0, 'C' => 1]);
            }
        } else {
            return response()->json(['S' => 0, 'C' => 0]);
        }
    }

    public function getSensors(Request $request)
    {
        if ($request->json('zonal_unique_no') != null || $request->json('zonal_unique_no') != '') {
            // $data = zonal_info::where('zonal_unique_no', $request->json('zonal_unique_no'))->first();
            $zonalId = getZonalId($request->json('zonal_unique_no'));
            // return $zonalId;
            // exit;
            if (isset($zonalId)) {
                $getSensorData = sensor_info::where('zonal_id', $zonalId)->get();

                if (count($getSensorData) > 0) {

                    // Define color mappings
                    $colorMap = [
                        'red' => 'R',
                        'green' => 'G',
                        'blue' => 'B',
                        'yellow' => 'Y',
                        'cyan' => 'C',
                        'magenta' => 'M',
                        'white' => 'W',
                    ];

                    $roleMap = [
                        'loop' => 'L',
                        'off' => 'O',
                        'single' => 'S',
                    ];

                    // Convert the array to the desired format
                    $formattedSensors = [];

                    foreach ($getSensorData as $index => $sensor) {
                        $colorOccupied = $colorMap[strtolower($sensor["color_occupied"])] ?? 'U';
                        $colorAvailable = $colorMap[strtolower($sensor["color_available"])] ?? 'U';
                        $BarrierColor = $colorMap[strtolower($sensor["barrier_color"])] ?? 'Y';
                        $role = $roleMap[strtolower($sensor["role"])] ?? 'U';
                        // $formattedSensors[$index + 1] = [
                        $formattedSensors[] = [
                            "I" => $sensor["sensor_unique_no"],
                            "R" => (string) $sensor["sensor_range"],
                            "O" => $colorOccupied,
                            "A" => $colorAvailable,
                            "F" => $role,
                            'B' => $BarrierColor,
                            'C' => $sensor["barrier_id"] == null ? '' : $sensor["barrier_id"],
                        ];
                    }
                    // return response()->json($formattedSensors);
                    return response()->json(['S' => 1, 'C' => 200, 'D' => $formattedSensors]);
                } else {
                    return response()->json(['S' => 0, 'C' => 2]);
                }
            } else {
                return response()->json(['S' => 0, 'C' => 1]);
            }


            // if ($formattedSensors) {
            //     return response()->json($formattedSensors);
            // } else {
            //     return response()->json('Sensors not found');
            // }
        } else {

            return response()->json(['S' => 0, 'C' => 0]);
        }

        // return response()->json($getDisplayData);
    }

    public function getSensorsStatus(Request $request)
    {
        // $jsonData = $request->data;

        // $sensordata = sensor_data_logging::where('sensor', '1871760D')->latest('updated_at')->first();
        // return Carbon::parse($sensordata->updated_at)->diffInMinutes(now());
        // return $sensordata['site_id'];
        // exit;

        // $data = $request->json('data');
        // // $data = json_decode($data, true);
        // if (Str::isJson($data)) {
        //     return "json";
        // } else {
        //     return "not json";
        // }

        // Decode the JSON into a PHP array
        // $data = json_decode($jsonData, true);

        if ($request->input('data') != null && $request->input('data') != '' && $request->input('zonal_unique_no') != null && $request->input('zonal_unique_no') != '') {

            // $data = $request->input('data');
            if ($request->json('data')) {
                $data = $request->input('data');
            } else {
                $data = json_decode($request->input('data'), true);
            }

            // Iterate through the root keys (zonal_id)
            foreach ($data as $sensor_id => $status) {

                $filteredStatus = explode(',', $status);
                // return $filteredStatus[1];
                // Iterate through the sensor data for each zonal_id
                // foreach ($sensor_data as $sensor_id => $status) {

                // $checkSensorExist = sensor_data_logging::where('sensor', $sensor_id)->get();
                // if(count($checkSensorExist) > 0){
                //     // Update Data

                // } else {
                //     // Insert into database
                //     sensor_data_logging::create([
                //         // 'zonal_id' => $zonal_id,
                //         'sensor' => $sensor_id,
                //         'status' => $status,
                //         'date_time' => $DateTime
                //     ]);
                // }

                $sensordata = sensor_info::where('sensor_unique_no', $sensor_id)->first();
                $zonalId = getZonalId($request->input('zonal_unique_no'));
                // return $zonalId;
                // exit;
                if ($zonalId == 0) {
                    $emailLogZ = email_log::whereDate('created_at', Carbon::today())->where('sensor_id', $request->input('zonal_unique_no'))->latest('created_at')->first();

                    if ($emailLogZ) {
                        $diffInMinutesZ = Carbon::now()->diffInMinutes($emailLogZ->created_at);
                        // Now you can check like:
                        if ($diffInMinutesZ > 120) {
                            // $email = "ajay.ladkat@parkomate.com";
                            $email = "maheshyangandul@gmail.com";
                            $mailData = [
                                'from_email' => $email,
                                'from_name' => "Zonal not registered",
                                'subject' => "Zonal not registered",
                                'site_name' => 1,
                                'floor_name' => 1,
                                'zonal_name' => 1,
                                'sensor_unique_no' => 1,
                                'sensor_name' => 1,
                                'sensor_id' => $request->input('zonal_unique_no'),
                                'status' => 1,
                                'date' => Carbon::now(),
                                'device' => 'zonal'
                            ];
                            SendSensorMail::dispatch($email, $mailData);
                        }
                    } else {
                        // $email = "ajay.ladkat@parkomate.com";
                        $email = "maheshyangandul@gmail.com";
                        $mailData = [
                            'from_email' => $email,
                            'from_name' => "Zonal not registered",
                            'subject' => "Zonal not registered",
                            'site_name' => 1,
                            'floor_name' => 1,
                            'zonal_name' => 1,
                            'sensor_unique_no' => 1,
                            'sensor_name' => 1,
                            'sensor_id' => $request->input('zonal_unique_no'),
                            'status' => 1,
                            'date' => Carbon::now(),
                            'device' => 'zonal'
                        ];
                        SendSensorMail::dispatch($email, $mailData);
                    }
                    return response()->json(['S' => 1, 'C' => 5]);
                }
                if ($zonalId != $sensordata->zonal_id) {
                    return response()->json(['S' => 1, 'C' => 'zonal no not matched']);
                }
                $siteData = site_info::where('site_id', $sensordata['site_id'])->first();
                $color = null;
                if (!empty($sensordata)) {
                    if ($filteredStatus[0] == 1) {
                        $color = $sensordata['color_occupied'];
                    }
                    if ($filteredStatus[0] == 0) {
                        $existing = sensor_data_logging::where('sensor', $sensor_id)->first();
                        if ($existing) {
                            $relativePath = 'uploads/' . $existing->car_image; // e.g. 'uploads/car.png'
                            $oldImagePath = public_path($relativePath);
                            if (File::exists($oldImagePath)) {
                                File::delete($oldImagePath);
                            }
                        }
                        $color = $sensordata['color_available'];
                    }
                    // Get previous log if exists
                    $previousLog = sensor_data_logging::where('sensor', $sensor_id)->latest('updated_at')->first();
                    $shouldSendEmail = false;

                    if ($filteredStatus[0] == 1) {
                        $number = $filteredStatus[1] ?? null;
                        $carColor = $filteredStatus[2] ?? null;
                    } else {
                        $number = null;
                        $carColor = null;
                    }
                    $loggingData = [
                        'status' => $filteredStatus[0],
                        'date_time' => Carbon::now(),
                        'color' => $color,
                        'number' => $number,
                        'car_color' => $carColor,
                    ];

                    if ($previousLog) {

                        if ($filteredStatus[0] == 2 || $filteredStatus[0] == 4) {
                            // Check if previous log has same status within X minutes (e.g., 5 mins)
                            if (Carbon::parse($previousLog->updated_at)->diffInMinutes(now()) > 5) {
                                $shouldSendEmail = true;
                                $color = null;
                                // return $shouldSendEmail;
                            } else {
                                $color = $previousLog->color;
                            }
                        }
                        // return $color;


                        // Logging data
                        // $loggingData = [
                        //     'status' => $status,
                        //     'color' => $color,
                        //     'updated_at' => Carbon::now(),
                        // ];

                        $loggingData = [];
                        if ($filteredStatus[0] == 1 || $filteredStatus[0] == 0) {
                            $loggingData = [
                                'status' => $filteredStatus[0],
                                'date_time' => Carbon::now(),
                                'color' => $color,
                                'number' => $number,
                                'car_color' => $carColor,
                            ];
                            sensor_data_logging::updateOrCreate(
                                [
                                    'site_id' => $sensordata['site_id'] ?? null,
                                    'floor_id' => $sensordata['floor_id'] ?? null,
                                    'zonal_id' => $sensordata['zonal_id'] ?? null,
                                    'sensor' => $sensor_id
                                ],
                                $loggingData
                            );
                        }
                        if ($filteredStatus[0] == 2 || $filteredStatus[0] == 4) {

                            if ($previousLog->status != 4 && $previousLog->status != 2) {
                                $loggingData = [
                                    'status' => $filteredStatus[0],
                                    'date_time' => Carbon::now(),
                                    'color' => $color,
                                    'number' => $number,
                                    'car_color' => $carColor,
                                ];
                            } else {
                                $loggingData = [
                                    'color' => $color,
                                    'number' => $number,
                                    'car_color' => $carColor,
                                ];
                            }
                        }
                        if ($filteredStatus[0] != $previousLog->status) {
                            $loggingData = [
                                'status' => $filteredStatus[0],
                                'date_time' => Carbon::now(),
                                'color' => $color,
                                'number' => $number,
                                'car_color' => $carColor,
                            ];
                        }
                    }
                    // if ($status != 2 && $status != 4) {
                    //     $loggingData['color'] = $color;
                    // }

                    // return $loggingData;

                    sensor_data_logging::updateOrCreate(
                        [
                            'site_id' => $sensordata['site_id'] ?? null,
                            'floor_id' => $sensordata['floor_id'] ?? null,
                            'zonal_id' => $sensordata['zonal_id'] ?? null,
                            'sensor' => $sensor_id
                        ],
                        $loggingData
                    );

                    // ✅ Only send mail if needed
                    if ($shouldSendEmail) {
                        $emailLog = email_log::whereDate('created_at', Carbon::today())->where('sensor_id', $sensordata->sensor_id)->latest('created_at')->first();
                        $from_name = "Sensor Status Update - " . getSitename($sensordata->site_id);
                        if ($emailLog) {
                            $diffInMinutes = Carbon::now()->diffInMinutes($emailLog->created_at);
                            // Now you can check like:
                            if ($diffInMinutes > 120) {
                                $emails = $siteData->email;
                                $emailsArray = explode(',', $emails);
                                foreach ($emailsArray as $email) {
                                    $mailData = [
                                        'from_email' => $email,
                                        'from_name' => $from_name,
                                        'subject' => "Sensor Error",
                                        'site_name' => getSitename($sensordata->site_id),
                                        'floor_name' => getFloorname($sensordata->floor_id),
                                        'zonal_name' => getZonalname($sensordata->zonal_id),
                                        'sensor_unique_no' => $sensordata->sensor_unique_no,
                                        'sensor_name' => $sensordata->sensor_name,
                                        'sensor_id' => $sensordata->sensor_id,
                                        'status' => $filteredStatus[0],
                                        'date' => Carbon::now(),
                                        'device' => 'sensor',
                                    ];
                                    SendSensorMail::dispatch($email, $mailData);
                                }
                            }
                        } else {
                            $emails = $siteData->email;
                            $emailsArray = explode(',', $emails);

                            foreach ($emailsArray as $email) {
                                $mailData = [
                                    'from_email' => $email,
                                    'from_name' => $from_name,
                                    'subject' => "Sensor Error",
                                    'site_name' => getSitename($sensordata->site_id),
                                    'floor_name' => getFloorname($sensordata->floor_id),
                                    'zonal_name' => getZonalname($sensordata->zonal_id),
                                    'sensor_unique_no' => $sensordata->sensor_unique_no,
                                    'sensor_name' => $sensordata->sensor_name,
                                    'sensor_id' => $sensordata->sensor_id,
                                    'status' => $filteredStatus[0],
                                    'date' => Carbon::now(),
                                    'device' => 'sensor'
                                ];
                                SendSensorMail::dispatch($email, $mailData);
                            }
                        }
                    }
                }
                // }
            }

            if (!empty($request->allFiles())) {
                foreach ($request->allFiles() as $key => $file) {
                    $existing = sensor_data_logging::where('sensor', $key)->first();
                    if ($existing) {
                        $relativePath = 'uploads/' . $existing->car_image; // e.g. 'uploads/car.png'
                        $oldImagePath = public_path($relativePath);
                        if (File::exists($oldImagePath)) {
                            File::delete($oldImagePath);
                        }

                        $originalName = $file->getClientOriginalName(); // e.g. car.png
                        $filename = $originalName;        // prevent name collision
                        $file->move(public_path('uploads'), $filename);

                        $existing->update([
                            'car_image' => $filename,
                        ]);
                    }
                }
            }

            if ($request->input('zonal_unique_no')) {
                $BS = [];
                $zonal_id = getZonalIdWithNo($request->input('zonal_unique_no'));
                $sensor_res = sensor_reservation::where('is_blocked', 1)->where('zonal_id', $zonal_id)->get();
                foreach ($sensor_res as $d) {
                    $sensorNo = getSensorNoWithId($d->sensor_id);
                    array_push($BS, $sensorNo . "-" . $d->barrier_unique_no);
                }
                if (count($sensor_res) > 0) {
                    return response()->json(['S' => 1, 'C' => 200, 'BS' => $BS]);
                } else {
                    return response()->json(['S' => 1, 'C' => 200]);
                }
            } else {
                return response()->json(['S' => 0, 'C' => 1]);
            }
        } else {
            return response()->json(['S' => 0, 'C' => 0]);
        }
    }

    // public function getDisplaySymbol(Request $request)
    // {
    //     $data = display_info::where('display_unique_no', $request->json('display_no'))->first();
    //     $jsonData = [];

    //     if (isset($data)) {
    //         $symbolData = symbol_on_display::where('display_id', $data->display_id)->first();
    //         $jsonData = [
    //             "I" => $data->intensity,
    //             "C" => $symbolData->coordinates,
    //             "S" => $symbolData->color,
    //             "B" => symbolBinaryData($symbolData->symbol_to_show),
    //         ];
    //         return response()->json($jsonData);
    //     } else {
    //         return response()->json(['1' => 'unr']);
    //     }
    // }


    public function getCompleteSiteData(Request $request)
    {
        $response = null;
        $key = $request->query('key');
        $granularity = $request->query('granularity');
        $siteData = site_info::where('site_username', $request->query('property'))->first();


        if (!empty($siteData) && $siteData->api_key == $key) {

            $lastUpdatedTime = sensor_data_logging::where('site_id', $siteData->site_id)->latest('updated_at')->first();
            $readableDate = \Carbon\Carbon::parse($lastUpdatedTime->updated_at)->format('d-m-Y h:i:s');
            $sensorData = sensor_data_logging::where('site_id', $siteData->site_id)->get()->groupBy('floor_id');
            $total = 0;
            $available = 0;
            $occupied = 0;
            $sensorsStatus = [];
            if (!empty($sensorData)) {

                $floorWiseData = [];
                foreach ($sensorData as $floorId => $entries) {
                    $floorstotal = 0;
                    $floorsavailable = 0;
                    $floorsoccupied = 0;

                    foreach ($entries as $entry) {
                        $sensorsStatus[$entry->sensor] = $entry->status;
                        if ($entry->status == 1 || $entry->status == 2 || $entry->status == 4) {
                            $occupied++;
                            $floorsoccupied++;
                        }
                        if ($entry->status == 0) {
                            $available++;
                            $floorsavailable++;
                        }
                    }
                    $floorstotal = $floorsavailable + $floorsoccupied;
                    $floorWiseData[getFloorname($floorId)] = [
                        'floor_name' => getFloorname($floorId),
                        'total' => $floorstotal,
                        'available' => $floorsavailable,
                        'occupied' => $floorsoccupied,
                    ];
                }
            }
            $total = $available + $occupied;

            if ($granularity == 'sensor') {
                $response = [
                    'status' => 1,
                    'message' => '',
                    'property_name' => $siteData->site_name,
                    'available_slots' => $available,
                    'occupied_slots' => $occupied,
                    'total_slots' => $total,
                    'status_timestamp' => $readableDate,
                    'floors' => $floorWiseData,
                    'sensors' => $sensorsStatus
                ];
            } else {
                $response = [
                    'status' => 1,
                    'message' => '',
                    'property_name' => $siteData->site_name,
                    'available_slots' => $available,
                    'occupied_slots' => $occupied,
                    'total_slots' => $total,
                    'status_timestamp' => $readableDate,
                    'floors' => $floorWiseData,
                ];
            }

            return response()->json($response, 200);
        } else {
            return response()->json(['status' => 0, 'message' => 'ERROR 403: Unauthorized Access'], 403);
        }
    }

    public function uploadImage(Request $request)
    {
        // return response()->json(['message' => 'Reached uploadImage']);
        $sensor = $request->input('sensor');
        // if ($request->expectsJson()) {

        // First check if sensor is provided and not empty

        if (empty($sensor)) {
            return response()->json([
                'status' => 0,
                'message' => 'Sensor is required.'
            ], 400);
        }

        // $request->validate([
        //     'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        // ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $destinationPath = public_path('uploads/images');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $imageName);
            $relativePath = 'uploads/images/' . $imageName;

            // Check if sensor already exists
            $existing = upload_image::where('sensor', $sensor)->first();

            if ($existing) {
                // Delete old image file
                $oldImagePath = public_path($existing->filepath);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }

                // Update
                $existing->update([
                    'filename' => $imageName,
                    'filepath' => $relativePath,
                ]);
                $message = "Image updated for sensor";
            } else {
                // Create
                upload_image::create([
                    'sensor' => $sensor,
                    'filename' => $imageName,
                    'filepath' => $relativePath,
                ]);
                $message = "Image created for new sensor";
            }

            return response()->json([
                'status' => 1,
                'message' => $message,
                'data' => [
                    'sensor' => $sensor,
                    'filename' => $imageName,
                    'url' => asset($relativePath),
                ]
            ]);
        } else {
            $existing = upload_image::where('sensor', $sensor)->first();
            if ($existing) {
                $existing->update([
                    'filename' => null,
                    'filepath' => null,
                ]);
            } else {
                upload_image::create([
                    'sensor' => $sensor,
                    'filename' => null,
                    'filepath' => null,
                ]);
            }
            return response()->json(['status' => 1], 200);
        }
        return response()->json(['status' => 0, 'message' => 'No file uploaded'], 400);
        // } else {
        //     if (empty($sensor)) {
        //         return response()->json([
        //             'status' => 0,
        //             'message' => 'Sensor is required.'
        //         ], 400);
        //     }

        //     $request->validate([
        //         'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        //     ]);

        //     if ($request->hasFile('image')) {
        //         $image = $request->file('image');
        //         $imageName = time() . '_' . $image->getClientOriginalName();
        //         $destinationPath = public_path('uploads/images');

        //         if (!File::exists($destinationPath)) {
        //             File::makeDirectory($destinationPath, 0755, true);
        //         }

        //         $image->move($destinationPath, $imageName);
        //         $relativePath = 'uploads/images/' . $imageName;

        //         // Check if sensor already exists
        //         $existing = upload_image::where('sensor', $sensor)->first();

        //         if ($existing) {
        //             // Delete old image file
        //             $oldImagePath = public_path($existing->filepath);
        //             if (File::exists($oldImagePath)) {
        //                 File::delete($oldImagePath);
        //             }

        //             // Update
        //             $existing->update([
        //                 'filename' => $imageName,
        //                 'filepath' => $relativePath,
        //             ]);
        //             $message = "Image updated for sensor";
        //         } else {
        //             // Create
        //             upload_image::create([
        //                 'sensor' => $sensor,
        //                 'filename' => $imageName,
        //                 'filepath' => $relativePath,
        //             ]);
        //             $message = "Image created for new sensor";
        //         }

        //         return response()->json([
        //             'status' => 1,
        //             'message' => $message,
        //             'data' => [
        //                 'sensor' => $sensor,
        //                 'filename' => $imageName,
        //                 'url' => asset($relativePath),
        //             ]
        //         ]);
        //     } else {
        //         $existing = upload_image::where('sensor', $sensor)->first();
        //         if ($existing) {
        //             $existing->update([
        //                 'filename' => null,
        //                 'filepath' => null,
        //             ]);
        //         } else {
        //             upload_image::create([
        //                 'sensor' => $sensor,
        //                 'filename' => null,
        //                 'filepath' => null,
        //             ]);
        //         }
        //         return response()->json(['status' => 1], 200);
        //     }
        //     return response()->json(['status' => 0, 'message' => 'No file uploaded'], 400);
        // }
    }

    public function testApi(Request $request)
    {
        $allData = $request->all();
        // $allDataE = json_encode($request->all());
        // DB::table('test_api')->insert([
        //     'data' => $allDataE,
        //     'created_at' => now(),
        // ]);
        $id = $allData['deviceInfo']['devEui'] ?? null;
        $status = $allData['object']['Payload']['parkStatus'] ?? null;
        $sensorData = sensor_info::where('sensor_unique_no', $id)->first();
        $color = null;
        $newStatus = null;
        if ($sensorData) {
            if ($status) {
                $color = $sensorData->color_occupied;
                $newStatus = 1;
            } else {
                $color = $sensorData->color_available;
                $newStatus = 0;
            }
            $loggingData = [
                'status' => $newStatus,
                'date_time' => Carbon::now(),
                'color' => $color,
            ];
            sensor_data_logging::updateOrCreate(
                [
                    'site_id' => $sensorData->site_id ?? null,
                    'floor_id' => $sensorData->floor_id ?? null,
                    'zonal_id' => $sensorData->zonal_id ?? null,
                    'sensor' => $sensorData->sensor_unique_no
                ],
                $loggingData
            );

            return 1;
        } else {
            return 0;
        }

        // $allData = json_encode($request->all());
        // // return $allData;

        // $insert = DB::table('test_api')->insert([
        //     'data' => $allData,
        //     'created_at' => now(),
        // ]);

        // if($insert){
        //     return response()->json(["status" => 1]);
        // }else{
        //     return response()->json(["status" => 0]);

        // }
    }

    public function updateCount(Request $request)
    {
        $all = $request->all();
        foreach ($all as $key => $value) {
            // $key   = collect($all)->keys()->first();
            // $value = $request->input($key);

            $sensorData = eecs_sensor_info::where('sensor_number', $key)->first();
            if ($sensorData) {
                $eecsData = eecs_data::where('sensor_id', $sensorData->id)->first();
                if ($eecsData) {
                    $type = $eecsData->type;
                    $count = (int)$value;
                    $floorData = floor_info::where('floor_id', $eecsData->from)->first();
                    if ($floorData) {
                        $jsonData = json_decode($floorData->measured_count, true);
                        if (isset($jsonData[(string)$type])) {
                            $jsonData[(string)$type] -= $count;
                        }
                        $floorData->measured_count = $jsonData;
                        $floorData->save();
                        $data = [
                            'site_id' => $floorData->site_id,
                            'floor_id' => $floorData->floor_id,
                            'type' => $type,
                            'count' => -$count
                        ];
                        eece_data_logging_floor::create($data);
                    }
                    $floorData = floor_info::where('floor_id', $eecsData->to)->first();
                    if ($floorData) {
                        $jsonData = json_decode($floorData->measured_count, true);
                        if (isset($jsonData[(string)$type])) {
                            $jsonData[(string)$type] += $count;
                        }
                        $floorData->measured_count = $jsonData;
                        $floorData->save();
                        $data = [
                            'site_id' => $floorData->site_id,
                            'floor_id' => $floorData->floor_id,
                            'type' => $type,
                            'count' => $count
                        ];
                        eece_data_logging_floor::create($data);
                    }
                    if ($eecsData->from == 0) {
                        $siteData = [
                            'site_id' => $sensorData->site_id,
                            'type' => $type,
                            'count' => $count
                        ];
                        eece_data_logging_site::create($siteData);
                    }
                    if ($eecsData->to == 0) {
                        $siteData = [
                            'site_id' => $sensorData->site_id,
                            'type' => $type,
                            'count' => -$count
                        ];
                        eece_data_logging_site::create($siteData);
                    }
                    // return response()->json(['status' => 1, 'message' => 'Data Updated']);
                } else {
                    return response()->json(['status' => 0, 'message' => 'EECS data not found for this sensor']);
                }
            } else {
                return response()->json(['status' => 0, 'message' => 'EECS data not found for this sensor']);
            }
        }
        return response()->json(['status' => 1, 'message' => 'Data Updated']);
    }

    public function getDeviceData(Request $request)
    {
        $deviceData = eecs_device_info::where('device_id', $request->device_id)->first();
        $filteredData = [];
        if ($deviceData) {
            $detectionList = explode(',', $deviceData->detection_list);
            foreach ($detectionList as $value) {
                $sensors = [];
                $eecsSensorData = eecs_sensor_info::where('device_id', $deviceData->id)->where('detection_type', $value)->get();
                if (!empty($eecsSensorData)) {
                    foreach ($eecsSensorData as $sensor) {
                        $sensors[] =  $sensor->sensor_number;
                    }
                }
                // $filteredData[] = [
                //     getTypeName($value) => $sensors
                // ];
                $filteredData[getTypeName($value)] = !empty($sensors) ? implode(',', $sensors) : '';
            }
            return response()->json(['status' => 1, 'data' => $filteredData]);
            // dd($sensors);
        } else {
            $emailLogZ = email_log::whereDate('created_at', Carbon::today())->where('sensor_id', $request->device_id)->latest('created_at')->first();
            if ($emailLogZ) {
                $diffInMinutesZ = Carbon::now()->diffInMinutes($emailLogZ->created_at);
                // Now you can check like:
                if ($diffInMinutesZ > 120) {
                    // $email = "ajay.ladkat@parkomate.com";
                    $email = "maheshyangandul@gmail.com";
                    $mailData = [
                        'from_email' => $email,
                        'from_name' => "EECS Device Error",
                        'subject' => "EECS Device Error",
                        'site_name' => 1,
                        'floor_name' => 1,
                        'zonal_name' => 1,
                        'sensor_unique_no' => 1,
                        'sensor_name' => 1,
                        'sensor_id' => $request->device_id,
                        'status' => 1,
                        'date' => Carbon::now(),
                        'device' => 'deviceerror'
                    ];
                    SendSensorMail::dispatch($email, $mailData);
                }
            } else {
                // $email = "ajay.ladkat@parkomate.com";
                $email = "maheshyangandul@gmail.com";
                $mailData = [
                    'from_email' => $email,
                    'from_name' => "EECS Device Error",
                    'subject' => "EECS Device Error",
                    'site_name' => 1,
                    'floor_name' => 1,
                    'zonal_name' => 1,
                    'sensor_unique_no' => 1,
                    'sensor_name' => 1,
                    'sensor_id' => $request->device_id,
                    'status' => 1,
                    'date' => Carbon::now(),
                    'device' => 'deviceerror'
                ];
                SendSensorMail::dispatch($email, $mailData);
            }
            return response()->json(['status' => 0, 'message' => 'Device not found']);
        }
    }

    public function getReservationData(Request $request)
    {
        $id = getZonalId($request->zonal_unique_no);
        $reservationData = reservation_device_info::where('zonal_id', $id)->get();
        if ($reservationData->isNotEmpty()) {
            $data = [];

            foreach ($reservationData as $reservation) {
                $data[$reservation->reservation_number] = $reservation->status;
            }

            return response()->json(['status' => 1, 'data' => $data]);
        } else {
            return response()->json(['status' => 0, 'message' => 'Data not found']);
        }
    }
}
