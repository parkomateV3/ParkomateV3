<?php

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
                                    if ($display->math != null || $display->math != '') {
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


    ?>