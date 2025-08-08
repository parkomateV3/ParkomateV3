<?php

namespace App\Http\Controllers;

use App\Models\floor_info;
use App\Models\floor_interconnection;
use App\Models\sensor_data_logging;
use App\Models\sensor_info;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use ZipArchive;

class QrMapController extends Controller
{
    public function getCategoryData($site_id, $selectedCategory)
    {
        // return $selectedCategory;
        $data = floor_info::where('site_id', $site_id)->get();
        $allLocations = [];
        foreach ($data as $d) {
            $pillers = $d->piller_name;
            if ($pillers != null) {
                $pillers = json_decode($pillers, true);
                // ✅ Check if selected category exists
                if (isset($pillers[$selectedCategory])) {
                    $locations = collect($pillers[$selectedCategory])
                        ->flatMap(function ($group) {
                            return explode(',', reset($group));
                        })
                        ->values()
                        ->toArray();

                    $allLocations = array_merge($allLocations, $locations);
                }
            }
        }
        $allLocations = array_unique($allLocations);
        sort($allLocations);
        return $allLocations;
    }

    public function getPillerData($floor_id)
    {
        // return $selectedCategory;
        $data = floor_info::where('floor_id', $floor_id)->first();
        $allLocations = [];
        if ($data) {
            $pillers = $data->piller_name;
            if ($pillers != null) {
                $pillers = json_decode($pillers, true);
                // ✅ Check if selected category exists
                if (isset($pillers['Parking Pillers'])) {
                    $locations = collect($pillers['Parking Pillers'])
                        ->flatMap(function ($group) {
                            return explode(',', reset($group));
                        })
                        ->values()
                        ->toArray();

                    $allLocations = array_merge($allLocations, $locations);
                }
            }
            $allLocations = array_unique($allLocations);
            sort($allLocations);
        }
        return $allLocations;
    }

    public function testQR()
    {
        // dd(getSiteAllCategories(6));
        $data = floor_info::where('floor_id', 6)->first();
        $filters = json_decode($data->piller_name, true);

        // dd($filters);

        $selectedCategory = 'Parking Pillers';

        // Get and flatten the locations under the selected category
        $locations = collect($filters[$selectedCategory])
            ->flatMap(function ($group) {
                return explode(',', reset($group));
            })
            ->values()
            ->toArray();

        $allGroups = collect($filters)
            ->flatMap(function ($category) {
                return collect($category)->mapWithKeys(function ($item) {
                    $key = key($item);
                    $value = explode(',', $item[$key]);
                    return [$key => $value];
                });
            })
            ->toArray();

        $allLocations = collect($filters)
            ->flatMap(function ($category) {
                return collect($category)->flatMap(function ($item) {
                    return explode(',', reset($item));
                });
            })
            ->values()
            ->toArray();


        dd($allGroups);

        $categories = array_keys($filters);

        dd($categories);

        $categoryData = [];

        foreach ($filters as $category => $items) {
            foreach ($items as $name => $locations) {
                $categoryData[] = [
                    'category' => $category,
                    'name' => $name,
                    'locations' => $locations
                ];
            }
        }

        dd($categoryData);
    }

    public function downloadQr($site_id, $floor_id)
    {
        $fileName = getSitename($site_id) . "_" . getFloorname($floor_id);
        $url = url('/');
        $finalURL = $url . "/get_map/" . $site_id . "/" . $floor_id . "/";
        // dd($finalURL);
        // exit;
        $piller_names = getSitePillerNamesFloor($floor_id); // Example: ['P1', 'P2']

        if (empty($piller_names)) {
            // It's empty
            return "No pillar data found";
        }

        $zip = new \ZipArchive();
        $tempZipPath = sys_get_temp_dir() . '/qr_' . uniqid() . '.zip';

        if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            return response()->json(['error' => 'Could not create ZIP archive'], 500);
        }

        foreach ($piller_names as $name) {
            $pillerURL = $finalURL . $name;
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($pillerURL)
                ->size(300)
                ->margin(10)
                ->build();

            $qrImage = imagecreatefromstring($result->getString());

            $qrWidth = imagesx($qrImage);
            $qrHeight = imagesy($qrImage);
            $fontSize = 32; // Increase size
            $extraPadding = 20;

            // Create taller canvas
            $finalHeight = $qrHeight + $fontSize + $extraPadding;
            $finalImage = imagecreatetruecolor($qrWidth, $finalHeight);

            // White background
            $white = imagecolorallocate($finalImage, 255, 255, 255);
            imagefill($finalImage, 0, 0, $white);

            // Copy QR
            imagecopy($finalImage, $qrImage, 0, 0, 0, 0, $qrWidth, $qrHeight);

            // Draw text
            $black = imagecolorallocate($finalImage, 0, 0, 0);
            $fontPath = public_path('fonts/OpenSans_SemiCondensed-Bold.ttf'); // 👈 Make sure this path is correct

            if (file_exists($fontPath)) {
                // Centered text position
                $textBox = imagettfbbox($fontSize, 0, $fontPath, $name);
                $textWidth = $textBox[2] - $textBox[0];
                $x = ($qrWidth - $textWidth) / 2;
                $y = $qrHeight + $fontSize + 5;

                imagettftext($finalImage, $fontSize, 0, $x, $y, $black, $fontPath, $name);
            } else {
                // Fallback to default GD font (not centered)
                imagestring($finalImage, 5, 10, $qrHeight + 5, $name, $black);
            }

            // Save PNG to memory
            ob_start();
            imagepng($finalImage);
            $finalPng = ob_get_clean();

            $zip->addFromString($name . '.png', $finalPng);

            imagedestroy($qrImage);
            imagedestroy($finalImage);
        }


        $zip->close();

        $zipContent = file_get_contents($tempZipPath);
        unlink($tempZipPath); // Clean up

        return response($zipContent)
            ->header('Content-Type', 'application/zip')
            ->header('Content-Disposition', 'attachment; filename="qr_codes_' . $fileName . '.zip"')
            ->header('Content-Length', strlen($zipContent));
    }

    public function getMapData($site_id, $floor_id, $location)
    {
        $data = floor_info::where('floor_id', $floor_id)->first();
        if ($data == null || $data == '' || $data->floor_map_coordinate == null || $data->floor_map_coordinate == '') {
            return "Data not available";
        } else {

            $dimension = explode(',', $data->floor_image_sensor_mapping_dimenssion);
            $car_scale = explode(',', $data->car_scale);
            $floor_image = $data->floor_image;
            $label_size = $data->label_properties;
            // dd($dimension);
            $carcoordinates = json_decode($data->floor_map_coordinate, true);
            $status = null;
            foreach ($carcoordinates as &$c) {
                $status = getSensorStatus($c['sensor_id']);
                $number = getSensorNumber($c['sensor_id']);
                if ($number != null && $number != '' && $number != 'NA') {
                    $c['label'] = $c['label'] . " - " . $number;
                }
                $c['status'] = $status;
            }
            unset($c);
            // return view('floormap.floormap', compact('coordinates', 'car_scale', 'dimension', 'floor_image', 'floor_id', 'label_size'));
        }
        // echo "Site ID: " . $site_id . "<br>";
        // echo "Floor ID: " . $floor_id . "<br>";
        // echo "Location: " . $location . "<br>";
        $categories = getSiteAllCategories($site_id);
        if (empty($categories)) {
            // It's empty
            return "No pillar data found";
        }
        $floorData = floor_info::where('floor_id', $floor_id)->first();
        if (empty($floorData)) {
            // It's empty
            return "No pillar data found";
        }
        $piller = $floorData->piller_name;
        $piller = json_decode($piller, true);
        $piller = collect($piller)
            ->flatMap(function ($category) {
                return collect($category)->mapWithKeys(function ($item) {
                    $key = key($item);
                    $value = explode(',', $item[$key]);
                    return [$key => $value];
                });
            })
            ->toArray();
        // dd($piller);
        // foreach ($piller as $key => $value) {
        //     $piller[$key] = explode(',', $value);
        // }
        // dd($piller);
        $keyFound = null; // Variable to store matched key
        foreach ($piller as $key => $value) {
            // $categories = array_merge($categories, $value);
            if (in_array($location, $value)) {
                $keyFound = $key;
                break;
            }
        }

        $coordinates = $floorData->piller_coordinates;
        $coordinates = json_decode($coordinates, true);
        $coordinate = null;

        foreach ($coordinates as $key => $value) {
            if ($key == $keyFound) {
                $coordinate = explode(',', $value);
                break;
            }
        }
        // dd($coordinate);
        if ($coordinate == null) {
            return "Location not found!";
        }

        // Get dynamic coordinates
        $symbol_size = explode(',', $floorData->symbol_size);
        $width = $symbol_size[0];
        $height = $symbol_size[1];
        $x = (int) ($coordinate[0] - $width / 2);
        $y = (int) ($coordinate[1] - $height / 2);
        // $x = 50;
        // $y = 50;


        $processedImg = $floorData->floor_image;
        // $backgroundImage = "floors/" . $floorData->floor_image;
        // $currentImage = "symbols/" . $floorData->current_location_symbol;

        // // Paths to images
        // $backgroundPath = public_path($backgroundImage);
        // $overlayPath = public_path($currentImage);

        // dd($backgroundPath);

        // // Load images
        // $background = Image::make($backgroundPath)->orientate();
        // $overlay = Image::make($overlayPath)->resize($width, $height)->trim('transparent');
        // // $overlay = Image::make($overlayPath)->trim('transparent');

        // dd($y);
        $locationData = [$x, $y];
        // dd($locationData);

        // // Insert overlay
        // // $background->insert($overlay, null, intval($x), intval($y));

        // // Convert to base64
        // $imageData = (string) $background->encode('jpg');
        // $processedImg = 'data:image/jpeg;base64,' . base64_encode($imageData);

        $destinationLocation = "";
        $flag = 1;
        // return $overlay->response('jpg');
        // return view('map.map', compact('site_id', 'floor_id', 'location', 'processedImg', 'categories', 'destinationLocation', 'flag'));

        return view('map.map_test', compact('site_id', 'floor_id', 'location', 'processedImg', 'categories', 'destinationLocation', 'flag', 'carcoordinates', 'car_scale', 'dimension', 'floor_image', 'label_size', 'locationData'));
    }
    public function getMapDataPost(Request $request)
    {
        if ($request->piller_name == $request->location) {
            return back();
        }
        // $background = Image::make($request->image)->orientate();
        // return $background->response('jpg');
        $site_id = $request->site_id;
        $floor_id = $request->floor_id;

        $data = floor_info::where('floor_id', $floor_id)->first();
        if ($data == null || $data == '' || $data->floor_map_coordinate == null || $data->floor_map_coordinate == '') {
            return "Data not available";
        } else {

            $dimension = explode(',', $data->floor_image_sensor_mapping_dimenssion);
            $car_scale = explode(',', $data->car_scale);
            $floor_image = $data->floor_image;
            $label_size = $data->label_properties;
            // dd($dimension);
            $carcoordinates = json_decode($data->floor_map_coordinate, true);
            $status = null;
            foreach ($carcoordinates as &$c) {
                $status = getSensorStatus($c['sensor_id']);
                $number = getSensorNumber($c['sensor_id']);
                if ($number != null && $number != '' && $number != 'NA') {
                    $c['label'] = $c['label'] . " - " . $number;
                }
                $c['status'] = $status;
            }
            unset($c);
            // return view('floormap.floormap', compact('coordinates', 'car_scale', 'dimension', 'floor_image', 'floor_id', 'label_size'));
        }


        $location = $request->location;
        $destinationLocation = $request->piller_name;
        // $piller_names = getSitePillerNames($site_id);
        $categories = getSiteAllCategories($site_id);
        if (empty($categories)) {
            // It's empty
            return "No pillar data found";
        }


        $floorData = floor_info::where('floor_id', $floor_id)->first();
        $piller = $floorData->piller_name;
        $piller = json_decode($piller, true);
        $piller = collect($piller)
            ->flatMap(function ($category) {
                return collect($category)->mapWithKeys(function ($item) {
                    $key = key($item);
                    $value = explode(',', $item[$key]);
                    return [$key => $value];
                });
            })
            ->toArray();
        $destinationKey = null; // Variable to store matched key
        $currentKey = null; // Variable to store matched key
        foreach ($piller as $key => $value) {
            if (in_array($destinationLocation, $value)) {
                $destinationKey = $key;
                // break;
            }
            if (in_array($location, $value)) {
                $currentKey = $key;
                // break;
            }
        }
        $coordinates = $floorData->piller_coordinates;
        $coordinates = json_decode($coordinates, true);
        // dd($coordinates);
        $destinationCoordinate = null;
        if ($destinationKey != null) {

            foreach ($coordinates as $key => $value) {
                if ($key == $destinationKey) {
                    $destinationCoordinate = explode(',', $value);
                    break;
                }
            }
            foreach ($coordinates as $key => $value) {
                if ($key == $currentKey) {
                    $currentCoordinate = explode(',', $value);
                    break;
                }
            }
            if ($destinationCoordinate == null) {
                return "Destination Coordinates Not Found";
            }
            // dd($destinationCoordinate);

            // Get dynamic coordinates
            $symbol_size = explode(',', $floorData->symbol_size);
            $width = $symbol_size[0];
            $height = $symbol_size[1];
            $radius = $symbol_size[2];
            $spacing = $symbol_size[3];
            $max_distance = $symbol_size[4];
            $currentX = (int) ($currentCoordinate[0] - $width / 2);
            $currentY = (int) ($currentCoordinate[1] - $height / 2);
            $destinationX = (int) ($destinationCoordinate[0] - $width / 2);
            $destinationY = (int) ($destinationCoordinate[1] - $height / 2);
            // $x = 50;
            // $y = 50;

            // Convert coordinates to integers
            $startX = (int)$currentCoordinate[0];
            $startY = (int)$currentCoordinate[1];
            $endX = (int)$destinationCoordinate[0];
            $endY = (int)$destinationCoordinate[1];

            // $backgroundImage = "symbols/" . $floorData->floor_image;
            $backgroundImage = "floors/" . $floorData->floor_image;
            $currentImage = "symbols/" . $floorData->current_location_symbol;
            $destinationImage = "symbols/" . $floorData->destination_location_symbol;

            // Paths to images
            // $backgroundPath = public_path($backgroundImage);
            // $currentPath = public_path($currentImage);
            // $destinationPath = public_path($destinationImage);

            // Load images
            $background = Image::make($backgroundImage)->orientate();

            // $points = [];
            // foreach ($pathCoordinates as $pointStr) {
            //     $points[] = array_map('intval', explode(',', $pointStr));
            // }

            // circle dots start
            $path = $this->calculateRoute($currentKey, $destinationKey, $coordinates, $max_distance);
            $pathCoordinates = [];
            foreach ($path['path'] as $p) {
                foreach ($coordinates as $key => $value) {
                    if ($key == $p) {
                        $coo = explode(',', $value);
                        $pathCoordinates[] = $coo;
                    }
                }
            }
            // $radius = 5; // Radius of small circles
            // $spacing = 20; // Distance between circles
            $color = '#0000FF'; // Circle color

            for ($i = 0; $i < count($pathCoordinates) - 1; $i++) {
                $start = $pathCoordinates[$i];     // Current point [x, y]
                $end = $pathCoordinates[$i + 1];   // Next point [x, y]

                // Calculate direction
                $dx = $end[0] - $start[0];
                $dy = $end[1] - $start[1];
                $distance = sqrt($dx * $dx + $dy * $dy);

                // Normalize direction vector
                $dx /= $distance;
                $dy /= $distance;

                // Place circles along the line segment
                for ($j = 0; $j <= $distance; $j += $spacing) {
                    $x = $start[0] + $dx * $j;
                    $y = $start[1] + $dy * $j;

                    // Draw a small filled circle
                    $background->ellipse(
                        $radius * 2, // Width
                        $radius * 2, // Height
                        $x, // X Position
                        $y, // Y Position
                        function ($draw) use ($color) {
                            $draw->background($color); // Set circle color
                        }
                    );
                }
            }
            // circle dots end


            // Draw a thick line by stacking multiple lines (simulate thickness)
            // $lineThickness = 10; // Adjust thickness here
            // $color = '#0000FF'; // Red color

            // for ($i = 0; $i < count($pathCoordinates) - 1; $i++) {
            //     $start = $pathCoordinates[$i];
            //     $end = $pathCoordinates[$i + 1];

            //     // Simulate thickness by drawing multiple offset lines
            //     for ($j = 0; $j < $lineThickness; $j++) {
            //         $offset = $j - floor($lineThickness / 2);
            //         // $offset = $j - floor($lineThickness / 2);

            //         $background->line(
            //             $start[0] + $offset,
            //             $start[1] + $offset,
            //             $end[0] + $offset,
            //             $end[1] + $offset,
            //             function ($draw) use ($color) {
            //                 $draw->color($color);
            //             }
            //         );
            //     }
            // }

            // for ($i = 0; $i < $lineThickness; $i++) {
            //     // Offset each line slightly to create a "thick" appearance
            //     $offsetX = $i - floor($lineThickness / 2);
            //     $offsetY = $i - floor($lineThickness / 2);

            //     $background->line(
            //         $startX + $offsetX,
            //         $startY + $offsetY,
            //         $endX + $offsetX,
            //         $endY + $offsetY,
            //         function ($draw) use ($color) {
            //             $draw->color($color);
            //         }
            //     );
            // }

            $locationData = [$currentX, $currentY, $destinationX, $destinationY];

            // $currentOverlay = Image::make($currentPath)->resize($width, $height)->trim('transparent');
            // $destinationOverlay = Image::make($destinationPath)->resize($width, $height)->trim('transparent');
            // $overlay = Image::make($overlayPath)->trim('transparent');

            // Insert overlay
            // $background->insert($currentOverlay, null, intval($currentX), intval($currentY));
            // $background->insert($destinationOverlay, null, intval($destinationX), intval($destinationY));

            // return $background->response('jpg');

            // Convert to base64
            $imageData = (string) $background->encode('jpg');
            $processedImg = 'data:image/jpeg;base64,' . base64_encode($imageData);

            $flag = 0;
            // return view('map.map', compact('site_id', 'floor_id', 'location', 'processedImg', 'categories', 'destinationLocation', 'flag')); 

            return view('map.map_test', compact('site_id', 'floor_id', 'location', 'processedImg', 'categories', 'destinationLocation', 'flag', 'carcoordinates', 'car_scale', 'dimension', 'floor_image', 'label_size', 'locationData'));
        } else {

            foreach ($coordinates as $key => $value) {
                if ($key == $currentKey) {
                    $currentCoordinate = explode(',', $value);
                    break;
                }
            }


            $interCoordinate = null;
            $interconnectionData = floor_interconnection::where('site_id', $site_id)->first();
            $iCoordinatesData = $interconnectionData->floor_info;
            $iCoordinatesData = json_decode($iCoordinatesData, true);
            foreach ($iCoordinatesData as $key => $value) {
                $value = explode(',', $value);
                if (in_array($currentKey, $value)) {
                    $interCoordinate = $key;
                    break;
                }
            }
            // dd($iCoordinatesData);
            // dd($interCoordinate);
            $iCoordinates = null;
            foreach ($coordinates as $key => $value) {
                if ($key == $interCoordinate) {
                    $iCoordinates = explode(',', $value);
                    break;
                }
            }
            $getFloorData = getFloorData($site_id, $destinationLocation, $interCoordinate);
            // dd($getFloorData);

            $ifloorcoordinates = json_decode($getFloorData[0]['piller_coordinates'], true);
            $idestinationkey = $getFloorData[0]['destinationKey'];
            // dd($getFloorData);
            $interFloorId = $getFloorData[0]['floor_id'];

            $data2 = floor_info::where('floor_id', $interFloorId)->first();
            if ($data2 == null || $data2 == '' || $data2->floor_map_coordinate == null || $data2->floor_map_coordinate == '') {
                return "Data not available";
            } else {

                $dimension2 = explode(',', $data2->floor_image_sensor_mapping_dimenssion);
                $car_scale2 = explode(',', $data2->car_scale);
                $floor_image2 = $data2->floor_image;
                $label_size2 = $data2->label_properties;
                // dd($dimension);
                $carcoordinates2 = json_decode($data2->floor_map_coordinate, true);
                $status2 = null;
                foreach ($carcoordinates2 as &$c) {
                    $status2 = getSensorStatus($c['sensor_id']);
                    $number2 = getSensorNumber($c['sensor_id']);
                    if ($number2 != null && $number2 != '' && $number2 != 'NA') {
                        $c['label'] = $c['label'] . " - " . $number2;
                    }
                    $c['status'] = $status2;
                }
                unset($c);
                // return view('floormap.floormap', compact('coordinates', 'car_scale', 'dimension', 'floor_image', 'floor_id', 'label_size'));
            }

            // dd($coordinate);

            //interconnect
            $width1 = $getFloorData[0]['width'];
            $height1 = $getFloorData[0]['height'];
            $radius1 = $getFloorData[0]['radius'];
            $spacing1 = $getFloorData[0]['spacing'];
            $max_distance1 = $getFloorData[0]['max_distance'];
            $interX1 = (int) ($getFloorData[0]['icoordinate'][0] - $width1 / 2);
            $interY1 = (int) ($getFloorData[0]['icoordinate'][1] - $height1 / 2);
            $destinationX1 = (int) ($getFloorData[0]['coordinate'][0] - $width1 / 2);
            $destinationY1 = (int) ($getFloorData[0]['coordinate'][1] - $height1 / 2);

            // Get dynamic coordinates
            $symbol_size = explode(',', $floorData->symbol_size);
            $width = $symbol_size[0];
            $height = $symbol_size[1];
            $radius = $symbol_size[2];
            $spacing = $symbol_size[3];
            $max_distance = $symbol_size[4];
            $currentX = (int) ($currentCoordinate[0] - $width / 2);
            $currentY = (int) ($currentCoordinate[1] - $height / 2);
            $interX = (int) ($iCoordinates[0] - $width / 2);
            $interY = (int) ($iCoordinates[1] - $height / 2);
            // $x = 50;
            // $y = 50;


            // $backgroundImage = "symbols/" . $floorData->floor_image;
            $backgroundImage = "floors/" . $floorData->floor_image;
            $currentImage = "symbols/" . $floorData->current_location_symbol;
            // $destinationImage = "symbols/" . $floorData->destination_location_symbol;
            $interImage = "symbols/" . $floorData->interconnect_location_symbol;

            $backgroundImage1 = "floors/" . $getFloorData[0]['floor_image'];
            $destinationImage1 = "symbols/" . $getFloorData[0]['destination_location_symbol'];
            $interImage1 = "symbols/" . $getFloorData[0]['interconnect_location_symbol'];

            // Paths to images
            $backgroundPath = public_path($backgroundImage);
            $currentPath = public_path($currentImage);
            // $destinationPath = public_path($destinationImage);
            $interPath = public_path($interImage);

            $backgroundPath1 = public_path($backgroundImage1);
            $destinationPath1 = public_path($destinationImage1);
            $interPath1 = public_path($interImage1);

            // Load images
            $background = Image::make($backgroundImage)->orientate();

            // circle dots start
            // dd($coordinates);
            $path = $this->calculateRoute($currentKey, $interCoordinate, $coordinates, $max_distance);
            // dd($coordinates);
            $pathCoordinates = [];
            foreach ($path['path'] as $p) {
                foreach ($coordinates as $key => $value) {
                    if ($key == $p) {
                        $coo = explode(',', $value);
                        $pathCoordinates[] = $coo;
                    }
                }
            }

            // $radius = 5; // Radius of small circles
            // $spacing = 20; // Distance between circles
            $color = '#0000FF'; // Circle color

            for ($i = 0; $i < count($pathCoordinates) - 1; $i++) {
                $start = $pathCoordinates[$i];     // Current point [x, y]
                $end = $pathCoordinates[$i + 1];   // Next point [x, y]

                // Calculate direction
                $dx = $end[0] - $start[0];
                $dy = $end[1] - $start[1];
                $distance = sqrt($dx * $dx + $dy * $dy);

                // Normalize direction vector
                $dx /= $distance;
                $dy /= $distance;

                // Place circles along the line segment
                for ($j = 0; $j <= $distance; $j += $spacing) {
                    $x = $start[0] + $dx * $j;
                    $y = $start[1] + $dy * $j;

                    // Draw a small filled circle
                    $background->ellipse(
                        $radius * 2, // Width
                        $radius * 2, // Height
                        $x, // X Position
                        $y, // Y Position
                        function ($draw) use ($color) {
                            $draw->background($color); // Set circle color
                        }
                    );
                }
            }
            // circle dots end

            $currentOverlay = Image::make($currentPath)->resize($width, $height)->trim('transparent');
            // $destinationOverlay = Image::make($destinationPath)->resize($width, $height)->trim('transparent');
            $interOverlay = Image::make($interPath)->resize($width, $height)->trim('transparent');
            // $overlay = Image::make($overlayPath)->trim('transparent');

            $background1 = Image::make($backgroundImage1)->orientate();

            // circle dots start
            $path1 = $this->calculateRoute($interCoordinate, $idestinationkey, $ifloorcoordinates, $max_distance1);
            // dd($path1);
            $pathCoordinates1 = [];
            foreach ($path1['path'] as $p) {
                foreach ($ifloorcoordinates as $key => $value) {
                    if ($key == $p) {
                        $coo = explode(',', $value);
                        $pathCoordinates1[] = $coo;
                    }
                }
            }
            for ($i = 0; $i < count($pathCoordinates1) - 1; $i++) {
                $start = $pathCoordinates1[$i];     // Current point [x, y]
                $end = $pathCoordinates1[$i + 1];   // Next point [x, y]

                // Calculate direction
                $dx = $end[0] - $start[0];
                $dy = $end[1] - $start[1];
                $distance = sqrt($dx * $dx + $dy * $dy);

                // Normalize direction vector
                $dx /= $distance;
                $dy /= $distance;

                // Place circles along the line segment
                for ($j = 0; $j <= $distance; $j += $spacing1) {
                    $x = $start[0] + $dx * $j;
                    $y = $start[1] + $dy * $j;

                    // Draw a small filled circle
                    $background1->ellipse(
                        $radius1 * 2, // Width
                        $radius1 * 2, // Height
                        $x, // X Position
                        $y, // Y Position
                        function ($draw) use ($color) {
                            $draw->background($color); // Set circle color
                        }
                    );
                }
            }
            // circle dots end


            $destinationOverlay1 = Image::make($destinationPath1)->resize($width1, $height1)->trim('transparent');
            $interOverlay1 = Image::make($interPath1)->resize($width1, $height1)->trim('transparent');

            $locationData = [$currentX, $currentY, $interX, $interY, $destinationX1, $destinationY1, $interX1, $interY1];
            // Insert overlay
            $background->insert($currentOverlay, null, intval($currentX), intval($currentY));
            // $background->insert($destinationOverlay, null, intval($destinationX), intval($destinationY));
            $background->insert($interOverlay, null, intval($interX), intval($interY));

            $background1->insert($destinationOverlay1, null, intval($destinationX1), intval($destinationY1));
            $background1->insert($interOverlay1, null, intval($interX1), intval($interY1));

            // return $background->response('jpg');

            // Convert to base64
            $imageData = (string) $background->encode('jpg');
            $processedImg = 'data:image/jpeg;base64,' . base64_encode($imageData);

            $imageData1 = (string) $background1->encode('jpg');
            $processedImg1 = 'data:image/jpeg;base64,' . base64_encode($imageData1);

            // return view('map.interconnection_map', compact('site_id', 'floor_id', 'location', 'categories', 'destinationLocation', 'processedImg', 'processedImg1', 'interFloorId', 'interCoordinate'));

            return view('map.interconnection_map_test', compact('site_id', 'floor_id', 'location', 'categories', 'destinationLocation', 'processedImg', 'processedImg1', 'interFloorId', 'interCoordinate', 'carcoordinates', 'car_scale', 'dimension', 'floor_image', 'label_size', 'carcoordinates2', 'car_scale2', 'dimension2', 'floor_image2', 'label_size2', 'locationData'));
        }
    }
    public function getMapDataPost2($site_id, $floor_id, $location, $sensor)
    {

        $sensorData = sensor_info::where('sensor_unique_no', $sensor)->first();
        if ($sensorData->near_piller == null || $sensorData->near_piller == '') {
            return "Near location not found.";
        } else {
            $piller_name = $sensorData->near_piller;
        }
        if ($piller_name == $location) {
            return back();
        }
        $data = floor_info::where('floor_id', $floor_id)->first();
        if ($data == null || $data == '' || $data->floor_map_coordinate == null || $data->floor_map_coordinate == '') {
            return "Data not available";
        } else {

            $dimension = explode(',', $data->floor_image_sensor_mapping_dimenssion);
            $car_scale = explode(',', $data->car_scale);
            $floor_image = $data->floor_image;
            $label_size = $data->label_properties;
            // dd($dimension);
            $carcoordinates = json_decode($data->floor_map_coordinate, true);
            $status = null;
            foreach ($carcoordinates as &$c) {
                $status = getSensorStatus($c['sensor_id']);
                $number = getSensorNumber($c['sensor_id']);
                if ($number != null && $number != '' && $number != 'NA') {
                    $c['label'] = $c['label'] . " - " . $number;
                }
                $c['status'] = $status;
            }
            unset($c);
            // return view('floormap.floormap', compact('coordinates', 'car_scale', 'dimension', 'floor_image', 'floor_id', 'label_size'));
        }


        $destinationLocation = $piller_name;
        // $piller_names = getSitePillerNames($site_id);
        $categories = getSiteAllCategories($site_id);
        if (empty($categories)) {
            // It's empty
            return "No pillar data found";
        }


        $floorData = floor_info::where('floor_id', $floor_id)->first();
        $piller = $floorData->piller_name;
        $piller = json_decode($piller, true);
        $piller = collect($piller)
            ->flatMap(function ($category) {
                return collect($category)->mapWithKeys(function ($item) {
                    $key = key($item);
                    $value = explode(',', $item[$key]);
                    return [$key => $value];
                });
            })
            ->toArray();
        $destinationKey = null; // Variable to store matched key
        $currentKey = null; // Variable to store matched key
        foreach ($piller as $key => $value) {
            if (in_array($destinationLocation, $value)) {
                $destinationKey = $key;
                // break;
            }
            if (in_array($location, $value)) {
                $currentKey = $key;
                // break;
            }
        }
        $coordinates = $floorData->piller_coordinates;
        $coordinates = json_decode($coordinates, true);
        // dd($coordinates);
        $destinationCoordinate = null;
        if ($destinationKey != null) {

            foreach ($coordinates as $key => $value) {
                if ($key == $destinationKey) {
                    $destinationCoordinate = explode(',', $value);
                    break;
                }
            }
            foreach ($coordinates as $key => $value) {
                if ($key == $currentKey) {
                    $currentCoordinate = explode(',', $value);
                    break;
                }
            }
            if ($destinationCoordinate == null) {
                return "Destination Coordinates Not Found";
            }
            // dd($destinationCoordinate);

            // Get dynamic coordinates
            $symbol_size = explode(',', $floorData->symbol_size);
            $width = $symbol_size[0];
            $height = $symbol_size[1];
            $radius = $symbol_size[2];
            $spacing = $symbol_size[3];
            $max_distance = $symbol_size[4];
            $currentX = (int) ($currentCoordinate[0] - $width / 2);
            $currentY = (int) ($currentCoordinate[1] - $height / 2);
            $destinationX = (int) ($destinationCoordinate[0] - $width / 2);
            $destinationY = (int) ($destinationCoordinate[1] - $height / 2);
            // $x = 50;
            // $y = 50;

            // Convert coordinates to integers
            $startX = (int)$currentCoordinate[0];
            $startY = (int)$currentCoordinate[1];
            $endX = (int)$destinationCoordinate[0];
            $endY = (int)$destinationCoordinate[1];

            // $backgroundImage = "symbols/" . $floorData->floor_image;
            $backgroundImage = "floors/" . $floorData->floor_image;
            $currentImage = "symbols/" . $floorData->current_location_symbol;
            $destinationImage = "symbols/" . $floorData->destination_location_symbol;

            // Paths to images
            // $backgroundPath = public_path($backgroundImage);
            // $currentPath = public_path($currentImage);
            // $destinationPath = public_path($destinationImage);

            // Load images
            $background = Image::make($backgroundImage)->orientate();

            // $points = [];
            // foreach ($pathCoordinates as $pointStr) {
            //     $points[] = array_map('intval', explode(',', $pointStr));
            // }

            // circle dots start
            $path = $this->calculateRoute($currentKey, $destinationKey, $coordinates, $max_distance);
            $pathCoordinates = [];
            foreach ($path['path'] as $p) {
                foreach ($coordinates as $key => $value) {
                    if ($key == $p) {
                        $coo = explode(',', $value);
                        $pathCoordinates[] = $coo;
                    }
                }
            }
            // $radius = 5; // Radius of small circles
            // $spacing = 20; // Distance between circles
            $color = '#0000FF'; // Circle color

            for ($i = 0; $i < count($pathCoordinates) - 1; $i++) {
                $start = $pathCoordinates[$i];     // Current point [x, y]
                $end = $pathCoordinates[$i + 1];   // Next point [x, y]

                // Calculate direction
                $dx = $end[0] - $start[0];
                $dy = $end[1] - $start[1];
                $distance = sqrt($dx * $dx + $dy * $dy);

                // Normalize direction vector
                $dx /= $distance;
                $dy /= $distance;

                // Place circles along the line segment
                for ($j = 0; $j <= $distance; $j += $spacing) {
                    $x = $start[0] + $dx * $j;
                    $y = $start[1] + $dy * $j;

                    // Draw a small filled circle
                    $background->ellipse(
                        $radius * 2, // Width
                        $radius * 2, // Height
                        $x, // X Position
                        $y, // Y Position
                        function ($draw) use ($color) {
                            $draw->background($color); // Set circle color
                        }
                    );
                }
            }
            // circle dots end


            // Draw a thick line by stacking multiple lines (simulate thickness)
            // $lineThickness = 10; // Adjust thickness here
            // $color = '#0000FF'; // Red color

            // for ($i = 0; $i < count($pathCoordinates) - 1; $i++) {
            //     $start = $pathCoordinates[$i];
            //     $end = $pathCoordinates[$i + 1];

            //     // Simulate thickness by drawing multiple offset lines
            //     for ($j = 0; $j < $lineThickness; $j++) {
            //         $offset = $j - floor($lineThickness / 2);
            //         // $offset = $j - floor($lineThickness / 2);

            //         $background->line(
            //             $start[0] + $offset,
            //             $start[1] + $offset,
            //             $end[0] + $offset,
            //             $end[1] + $offset,
            //             function ($draw) use ($color) {
            //                 $draw->color($color);
            //             }
            //         );
            //     }
            // }

            // for ($i = 0; $i < $lineThickness; $i++) {
            //     // Offset each line slightly to create a "thick" appearance
            //     $offsetX = $i - floor($lineThickness / 2);
            //     $offsetY = $i - floor($lineThickness / 2);

            //     $background->line(
            //         $startX + $offsetX,
            //         $startY + $offsetY,
            //         $endX + $offsetX,
            //         $endY + $offsetY,
            //         function ($draw) use ($color) {
            //             $draw->color($color);
            //         }
            //     );
            // }

            $locationData = [$currentX, $currentY, $destinationX, $destinationY];

            // $currentOverlay = Image::make($currentPath)->resize($width, $height)->trim('transparent');
            // $destinationOverlay = Image::make($destinationPath)->resize($width, $height)->trim('transparent');
            // $overlay = Image::make($overlayPath)->trim('transparent');

            // Insert overlay
            // $background->insert($currentOverlay, null, intval($currentX), intval($currentY));
            // $background->insert($destinationOverlay, null, intval($destinationX), intval($destinationY));

            // return $background->response('jpg');

            // Convert to base64
            $imageData = (string) $background->encode('jpg');
            $processedImg = 'data:image/jpeg;base64,' . base64_encode($imageData);

            $flag = 0;
            // return view('map.map', compact('site_id', 'floor_id', 'location', 'processedImg', 'categories', 'destinationLocation', 'flag')); 

            return view('map.map_test', compact('site_id', 'floor_id', 'location', 'processedImg', 'categories', 'destinationLocation', 'flag', 'carcoordinates', 'car_scale', 'dimension', 'floor_image', 'label_size', 'locationData'));
        } else {

            foreach ($coordinates as $key => $value) {
                if ($key == $currentKey) {
                    $currentCoordinate = explode(',', $value);
                    break;
                }
            }


            $interCoordinate = null;
            $interconnectionData = floor_interconnection::where('site_id', $site_id)->first();
            $iCoordinatesData = $interconnectionData->floor_info;
            $iCoordinatesData = json_decode($iCoordinatesData, true);
            foreach ($iCoordinatesData as $key => $value) {
                $value = explode(',', $value);
                if (in_array($currentKey, $value)) {
                    $interCoordinate = $key;
                    break;
                }
            }
            // dd($interCoordinate);
            $iCoordinates = null;
            foreach ($coordinates as $key => $value) {
                if ($key == $interCoordinate) {
                    $iCoordinates = explode(',', $value);
                    break;
                }
            }
            // dd($interCoordinate);
            $getFloorData = getFloorData($site_id, $destinationLocation, $interCoordinate);
            $ifloorcoordinates = json_decode($getFloorData[0]['piller_coordinates'], true);
            $idestinationkey = $getFloorData[0]['destinationKey'];
            // dd($getFloorData);
            $interFloorId = $getFloorData[0]['floor_id'];

            $data2 = floor_info::where('floor_id', $interFloorId)->first();
            if ($data2 == null || $data2 == '' || $data2->floor_map_coordinate == null || $data2->floor_map_coordinate == '') {
                return "Data not available";
            } else {

                $dimension2 = explode(',', $data2->floor_image_sensor_mapping_dimenssion);
                $car_scale2 = explode(',', $data2->car_scale);
                $floor_image2 = $data2->floor_image;
                $label_size2 = $data2->label_properties;
                // dd($dimension);
                $carcoordinates2 = json_decode($data2->floor_map_coordinate, true);
                $status2 = null;
                foreach ($carcoordinates2 as &$c) {
                    $status2 = getSensorStatus($c['sensor_id']);
                    $number2 = getSensorNumber($c['sensor_id']);
                    if ($number2 != null && $number2 != '' && $number2 != 'NA') {
                        $c['label'] = $c['label'] . " - " . $number2;
                    }
                    $c['status'] = $status2;
                }
                unset($c);
                // return view('floormap.floormap', compact('coordinates', 'car_scale', 'dimension', 'floor_image', 'floor_id', 'label_size'));
            }

            // dd($coordinate);

            //interconnect
            $width1 = $getFloorData[0]['width'];
            $height1 = $getFloorData[0]['height'];
            $radius1 = $getFloorData[0]['radius'];
            $spacing1 = $getFloorData[0]['spacing'];
            $max_distance1 = $getFloorData[0]['max_distance'];
            $interX1 = (int) ($getFloorData[0]['icoordinate'][0] - $width1 / 2);
            $interY1 = (int) ($getFloorData[0]['icoordinate'][1] - $height1 / 2);
            $destinationX1 = (int) ($getFloorData[0]['coordinate'][0] - $width1 / 2);
            $destinationY1 = (int) ($getFloorData[0]['coordinate'][1] - $height1 / 2);

            // Get dynamic coordinates
            $symbol_size = explode(',', $floorData->symbol_size);
            $width = $symbol_size[0];
            $height = $symbol_size[1];
            $radius = $symbol_size[2];
            $spacing = $symbol_size[3];
            $max_distance = $symbol_size[4];
            $currentX = (int) ($currentCoordinate[0] - $width / 2);
            $currentY = (int) ($currentCoordinate[1] - $height / 2);
            $interX = (int) ($iCoordinates[0] - $width / 2);
            $interY = (int) ($iCoordinates[1] - $height / 2);
            // $x = 50;
            // $y = 50;


            // $backgroundImage = "symbols/" . $floorData->floor_image;
            $backgroundImage = "floors/" . $floorData->floor_image;
            $currentImage = "symbols/" . $floorData->current_location_symbol;
            // $destinationImage = "symbols/" . $floorData->destination_location_symbol;
            $interImage = "symbols/" . $floorData->interconnect_location_symbol;

            $backgroundImage1 = "floors/" . $getFloorData[0]['floor_image'];
            $destinationImage1 = "symbols/" . $getFloorData[0]['destination_location_symbol'];
            $interImage1 = "symbols/" . $getFloorData[0]['interconnect_location_symbol'];

            // Paths to images
            $backgroundPath = public_path($backgroundImage);
            $currentPath = public_path($currentImage);
            // $destinationPath = public_path($destinationImage);
            $interPath = public_path($interImage);

            $backgroundPath1 = public_path($backgroundImage1);
            $destinationPath1 = public_path($destinationImage1);
            $interPath1 = public_path($interImage1);

            // Load images
            $background = Image::make($backgroundImage)->orientate();

            // circle dots start
            // dd($coordinates);
            $path = $this->calculateRoute($currentKey, $interCoordinate, $coordinates, $max_distance);
            // dd($coordinates);
            $pathCoordinates = [];
            foreach ($path['path'] as $p) {
                foreach ($coordinates as $key => $value) {
                    if ($key == $p) {
                        $coo = explode(',', $value);
                        $pathCoordinates[] = $coo;
                    }
                }
            }

            // $radius = 5; // Radius of small circles
            // $spacing = 20; // Distance between circles
            $color = '#0000FF'; // Circle color

            for ($i = 0; $i < count($pathCoordinates) - 1; $i++) {
                $start = $pathCoordinates[$i];     // Current point [x, y]
                $end = $pathCoordinates[$i + 1];   // Next point [x, y]

                // Calculate direction
                $dx = $end[0] - $start[0];
                $dy = $end[1] - $start[1];
                $distance = sqrt($dx * $dx + $dy * $dy);

                // Normalize direction vector
                $dx /= $distance;
                $dy /= $distance;

                // Place circles along the line segment
                for ($j = 0; $j <= $distance; $j += $spacing) {
                    $x = $start[0] + $dx * $j;
                    $y = $start[1] + $dy * $j;

                    // Draw a small filled circle
                    $background->ellipse(
                        $radius * 2, // Width
                        $radius * 2, // Height
                        $x, // X Position
                        $y, // Y Position
                        function ($draw) use ($color) {
                            $draw->background($color); // Set circle color
                        }
                    );
                }
            }
            // circle dots end

            $currentOverlay = Image::make($currentPath)->resize($width, $height)->trim('transparent');
            // $destinationOverlay = Image::make($destinationPath)->resize($width, $height)->trim('transparent');
            $interOverlay = Image::make($interPath)->resize($width, $height)->trim('transparent');
            // $overlay = Image::make($overlayPath)->trim('transparent');

            $background1 = Image::make($backgroundImage1)->orientate();

            // circle dots start
            $path1 = $this->calculateRoute($interCoordinate, $idestinationkey, $ifloorcoordinates, $max_distance1);
            // dd($path1);
            $pathCoordinates1 = [];
            foreach ($path1['path'] as $p) {
                foreach ($ifloorcoordinates as $key => $value) {
                    if ($key == $p) {
                        $coo = explode(',', $value);
                        $pathCoordinates1[] = $coo;
                    }
                }
            }
            for ($i = 0; $i < count($pathCoordinates1) - 1; $i++) {
                $start = $pathCoordinates1[$i];     // Current point [x, y]
                $end = $pathCoordinates1[$i + 1];   // Next point [x, y]

                // Calculate direction
                $dx = $end[0] - $start[0];
                $dy = $end[1] - $start[1];
                $distance = sqrt($dx * $dx + $dy * $dy);

                // Normalize direction vector
                $dx /= $distance;
                $dy /= $distance;

                // Place circles along the line segment
                for ($j = 0; $j <= $distance; $j += $spacing1) {
                    $x = $start[0] + $dx * $j;
                    $y = $start[1] + $dy * $j;

                    // Draw a small filled circle
                    $background1->ellipse(
                        $radius1 * 2, // Width
                        $radius1 * 2, // Height
                        $x, // X Position
                        $y, // Y Position
                        function ($draw) use ($color) {
                            $draw->background($color); // Set circle color
                        }
                    );
                }
            }
            // circle dots end


            $destinationOverlay1 = Image::make($destinationPath1)->resize($width1, $height1)->trim('transparent');
            $interOverlay1 = Image::make($interPath1)->resize($width1, $height1)->trim('transparent');

            $locationData = [$currentX, $currentY, $interX, $interY, $destinationX1, $destinationY1, $interX1, $interY1];
            // Insert overlay
            $background->insert($currentOverlay, null, intval($currentX), intval($currentY));
            // $background->insert($destinationOverlay, null, intval($destinationX), intval($destinationY));
            $background->insert($interOverlay, null, intval($interX), intval($interY));

            $background1->insert($destinationOverlay1, null, intval($destinationX1), intval($destinationY1));
            $background1->insert($interOverlay1, null, intval($interX1), intval($interY1));

            // return $background->response('jpg');

            // Convert to base64
            $imageData = (string) $background->encode('jpg');
            $processedImg = 'data:image/jpeg;base64,' . base64_encode($imageData);

            $imageData1 = (string) $background1->encode('jpg');
            $processedImg1 = 'data:image/jpeg;base64,' . base64_encode($imageData1);

            // return view('map.interconnection_map', compact('site_id', 'floor_id', 'location', 'categories', 'destinationLocation', 'processedImg', 'processedImg1', 'interFloorId', 'interCoordinate'));

            return view('map.interconnection_map_test', compact('site_id', 'floor_id', 'location', 'categories', 'destinationLocation', 'processedImg', 'processedImg1', 'interFloorId', 'interCoordinate', 'carcoordinates', 'car_scale', 'dimension', 'floor_image', 'label_size', 'carcoordinates2', 'car_scale2', 'dimension2', 'floor_image2', 'label_size2', 'locationData'));
        }
    }

    public function showMap()
    {
        return view('map.map');
    }

    private const COORDINATES = [
        "LL1" => [103, 144],
        "LJ1" => [340, 144],
        "LIFT_A" => [285, 634],
        "LIFT_B" => [1110, 639],
        "LH1" => [615, 145],
        "LL3" => [112, 349],
        "LJ3" => [343, 349],
        "LH3" => [620, 349],
        "LL5" => [112, 556],
        "LJ5" => [344, 555],
        "LH5" => [617, 555],
        "LF3" => [954, 353],
        "LD3" => [1169, 354],
        "LB3" => [1368, 351],
        "LF5" => [967, 556],
        "LD5" => [1168, 554],
        "LB5" => [1371, 558]
    ];


    // Function to calculate Euclidean distance
    private function euclideanDistance($p1, $p2)
    {
        return sqrt(pow($p2[0] - $p1[0], 2) + pow($p2[1] - $p1[1], 2));
    }

    private function calculateBoundingBox($start, $end, $final)
    {
        $startCoords = $final[$start];
        $endCoords = $final[$end];
        // $startCoords = self::COORDINATES[$start];
        // $endCoords = self::COORDINATES[$end];
        return [
            'min_x' => min($startCoords[0], $endCoords[0]),
            'max_x' => max($startCoords[0], $endCoords[0]),
            'min_y' => min($startCoords[1], $endCoords[1]),
            'max_y' => max($startCoords[1], $endCoords[1]),
        ];
    }

    private function filterPointsInBox($boundingBox, $final)
    {
        $filtered = [];
        foreach ($final as $place => $coords) {
            if (
                $coords[0] >= $boundingBox['min_x'] && $coords[0] <= $boundingBox['max_x'] &&
                $coords[1] >= $boundingBox['min_y'] && $coords[1] <= $boundingBox['max_y']
            ) {
                $filtered[$place] = $coords;
            }
        }
        return $filtered;
    }

    private function dijkstra($start, $end, $points, $max_distance)
    {
        $graph = [];
        foreach ($points as $place1 => $coords1) {
            foreach ($points as $place2 => $coords2) {
                if ($place1 !== $place2) {
                    $dist = $this->euclideanDistance($coords1, $coords2);
                    if ($dist <= $max_distance) {
                        $graph[$place1][$place2] = $dist;
                    }
                }
            }
        }

        $distances = array_fill_keys(array_keys($points), INF);
        $distances[$start] = 0;
        $pq = new \SplPriorityQueue();
        $pq->insert($start, 0);
        $prev = [];

        while (!$pq->isEmpty()) {
            $current = $pq->extract();
            if (!isset($graph[$current])) continue;

            foreach ($graph[$current] as $neighbor => $weight) {
                $alt = $distances[$current] + $weight;
                if ($alt < $distances[$neighbor]) {
                    $distances[$neighbor] = $alt;
                    $prev[$neighbor] = $current;
                    $pq->insert($neighbor, -$alt);
                }
            }
        }

        if (!isset($prev[$end])) return [];

        $path = [];
        for ($node = $end; $node !== null; $node = $prev[$node] ?? null) {
            array_unshift($path, $node);
        }

        return $path;
    }

    // public function calculateRoute($start, $end)
    public function calculateRoute($start, $end, $coordinate, $max_distance)
    {
        // $data = floor_info::where('floor_id', 16)->first();
        // $final = json_decode($coordinate);
        // dd($final);
        $convertedData = null;

        foreach ($coordinate as $key => $value) {
            $coords = explode(',', $value);

            // Validate if the split resulted in exactly two values
            if (count($coords) === 2) {
                $convertedData[$key] = [intval($coords[0]), intval($coords[1])];
            } else {
                echo "Error: Invalid format for key $key with value $value\n";
            }
        }

        // $formattedArray .= "];";

        // echo "<pre>$formattedArray</pre>";
        // dd($convertedData);
        // exit;

        // if (!isset(self::COORDINATES[$start]) || !isset(self::COORDINATES[$end])) {
        if (!isset($convertedData[$start]) || !isset($convertedData[$end])) {
            return response()->json(["error" => "Invalid start or end point"]);
        }

        // $boundingBox = $this->calculateBoundingBox($start, $end);
        $boundingBox = $this->calculateBoundingBox($start, $end, $convertedData);
        // $filteredPoints = $this->filterPointsInBox($boundingBox);
        $filteredPoints = $this->filterPointsInBox($boundingBox, $convertedData);
        // $max_distance = 400;

        if (!isset($filteredPoints[$start]) || !isset($filteredPoints[$end])) {
            return response()->json(["error" => "No valid path within bounding box"]);
        }

        $path = $this->dijkstra($start, $end, $filteredPoints, $max_distance);
        if (empty($path)) {
            return response()->json(["error" => "No path found"]);
        }

        $total_distance = 0;
        for ($i = 0; $i < count($path) - 1; $i++) {
            // $total_distance += $this->euclideanDistance(self::COORDINATES[$path[$i]], self::COORDINATES[$path[$i + 1]]);
            $total_distance += $this->euclideanDistance($convertedData[$path[$i]], $convertedData[$path[$i + 1]]);
        }
        // return $path;
        return [
            // "path" => implode(" -> ", $path),
            "path" => $path,
            "total_distance" => round($total_distance, 2),
        ];
    }

    public function showProcessedImage(Request $request)
    {
        // Get dynamic coordinates
        $x = $request->input('x', 75 - 20); // -w/2 con
        $y = $request->input('y', 70 - 20);  // -h/2 con

        // Paths to images
        $backgroundPath = public_path('symbols/WhatsApp Image 2025-03-14 at 17.29.57_cd638951.jpg');
        $overlayPath = public_path('symbols/current_location.png');

        // Load images
        $background = Image::make($backgroundPath)->orientate();
        $overlay = Image::make($overlayPath)->resize(40, 40)->trim('transparent');

        // Insert overlay
        $background->insert($overlay, null, intval($x), intval($y));

        // Convert to base64
        $imageData = (string) $background->encode('jpg');
        $base64 = 'data:image/jpeg;base64,' . base64_encode($imageData);

        return $background->response('jpg');

        // Return view with the base64 image
        // return view('map.index', compact('site_id', 'floor_id', 'location', 'floorData', 'base64'));
    }

    public function findMyCar()
    {
        $site_id = Auth::user()->site_id;
        $siteType = getSiteType($site_id);
        if ($siteType == "eecs" || $siteType == "slot_reservation" || $siteType == "sspi") {
            return redirect('dashboard/404');
        }
        $flag = 0;
        $active = "findmycar";
        return view('map.findmycar', compact('active', 'flag'));
    }

    public function findMyCarSelect(Request $request)
    {
        $site_id = Auth::user()->site_id;
        $siteType = getSiteType($site_id);
        if ($siteType == "eecs" || $siteType == "slot_reservation") {
            return redirect('dashboard/404');
        }
        $active = "findmycar";
        // $number = $request->number;
        $number = $request->number;
        $flag = 3; // default: not found

        // Step 1: Try partial match
        $sensorData = sensor_data_logging::where('site_id', $site_id)->where('number', 'LIKE', "%$number%")->get();
        // dd($sensorData);

        if ($sensorData->isEmpty()) {
            // Step 2: Fuzzy matching
            $allCars = sensor_data_logging::where('site_id', $site_id)->get(); // Consider limiting this for performance
            $similarCars = [];

            if (ctype_digit($number)) {
                // $number contains only digits
                foreach ($allCars as $car) {
                    // $digitsOnly = preg_replace('/\D/', '', $car->number);
                    $last4 = substr($car->number, -4);
                    similar_text($number, $last4, $percent);
                    if ($percent >= 70) { // You can adjust this threshold
                        $similarCars[] = $car;
                    }
                }
            } else {
                // $number contains letters or special characters
                foreach ($allCars as $car) {
                    similar_text($number, $car->number, $percent);
                    if ($percent >= 70) { // You can adjust this threshold
                        $similarCars[] = $car;
                    }
                }
            }

            if (!empty($similarCars)) {
                $sensorData = collect($similarCars);
                $flag = 21; // found similar
                return view('map.findmycar', compact('flag', 'active', 'sensorData', 'number'));
            } else {

                $message = "Car not found. Please check the car number.";
                return view('map.findmycar', compact('flag', 'active', 'message', 'number'));
            }
        } else {
            $flag = 20;
            return view('map.findmycar', compact('flag', 'active', 'sensorData', 'number'));
        }
    }

    public function findMyCarSearch($site_id, $number)
    {
        $active = "findmycar";
        // $number = $request->number;
        // $number = $request->number;
        $flag = 3; // default: not found

        // Step 1: Try partial match
        $sensorData = sensor_data_logging::where('site_id', $site_id)->where('number', 'LIKE', "%$number%")->get();

        if ($sensorData->isEmpty()) {
            // Step 2: Fuzzy matching
            $allCars = sensor_data_logging::where('site_id', $site_id)->get(); // Consider limiting this for performance
            $similarCars = [];

            if (ctype_digit($number)) {
                // $number contains only digits
                foreach ($allCars as $car) {
                    // $digitsOnly = preg_replace('/\D/', '', $car->number);
                    $last4 = substr($car->number, -4);
                    similar_text($number, $last4, $percent);
                    if ($percent >= 70) { // You can adjust this threshold
                        $similarCars[] = $car;
                    }
                }
            } else {
                // $number contains letters or special characters
                foreach ($allCars as $car) {
                    similar_text($number, $car->number, $percent);
                    if ($percent >= 70) { // You can adjust this threshold
                        $similarCars[] = $car;
                    }
                }
            }

            if (!empty($similarCars)) {
                $sensorData = collect($similarCars);
                $flag = 21; // found similar
                // return view('map.findmycar', compact('flag', 'active', 'sensorData', 'number'));
                return response()->json(['flag' => $flag, 'active' => $active, 'sensorData' => $sensorData, 'number' => $number]);
            } else {
                $message = "Car not found. Please check the car number.";
                // return view('map.findmycar', compact('flag', 'active', 'message', 'number'));
                return response()->json(['flag' => $flag, 'active' => $active, 'message' => $message, 'number' => $number]);
            }
        } else {
            $flag = 20;
            // return view('map.findmycar', compact('flag', 'active', 'sensorData', 'number'));
            return response()->json(['flag' => $flag, 'active' => $active, 'sensorData' => $sensorData, 'number' => $number]);
        }
    }

    public function findMyCarPost($sensor)
    {
        $site_id = Auth::user()->site_id;
        $siteType = getSiteType($site_id);
        if ($siteType == "eecs" || $siteType == "slot_reservation" || $siteType == "sspi") {
            return redirect('dashboard/404');
        }
        $active = "findmycar";
        $flag = 1;
        $sensorData = sensor_info::where('sensor_unique_no', $sensor)->first();
        if ($sensor) {
            $floorname = getFloorname($sensorData->floor_id);
            $sensorname = $sensorData->sensor_name;
            $sensorlocation = $sensorData->near_piller;
            if ($sensorlocation) {
                $message = "Your car is located on " . $floorname . " at " . $sensorname . " (near " . $sensorlocation . ").";
            } else {
                $message = "Your car is located on " . $floorname . " at " . $sensorname . ".";
            }
        } else {
            $message = "Car not found. Please check the car number.";
        }
        return view('map.findmycar', compact('flag', 'active', 'message'));
    }
}
