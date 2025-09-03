<?php

namespace App\Http\Controllers;

use App\Models\detection_type;
use App\Models\detection_type_site;
use App\Models\eecs_data;
use App\Models\eecs_device_info;
use App\Models\eecs_sensor_info;
use App\Models\floor_info;
use App\Models\site_info;
use App\Models\user_site_mapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class eecsDeviceController extends Controller
{
    public function index()
    {
        $id = Auth::id();
        $role_id = Auth::user()->role_id;
        $can_edit = Auth::user()->can_edit;
        $siteData = site_info::where('site_type_of_product', 'eecs')->get();
        $types = detection_type::all();

        if ($role_id == 1) {
            $eecsData = eecs_device_info::all();
            return view('eecsdevice.eecsdevice', compact('eecsData', 'siteData', 'can_edit', 'types'));
        }
        if ($role_id == 2) {
            $siteids = user_site_mapping::where('user_id', $id)->pluck('site_id')->implode(',');
            $array = explode(',', $siteids);
            $eecsData = eecs_device_info::whereIn('site_id', $array)->get();
            return view('eecsdevice.eecsdevice', compact('eecsData', 'siteData', 'can_edit', 'types'));
        }
        if ($role_id == 3) {
            return redirect()->route('login');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|unique:eecs_device_infos,device_id', // Check for unique device_id
            'detection_list' => 'required', // Check for unique device_id
        ]);

        $data = [
            'site_id' => $request->input('site_id'),
            'device_id' => $request->input('device_id'),
            'device_name' => $request->input('device_name'),
            'detection_list' => $request->input('detection_list'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            $ids = explode(',', $data['detection_list']);
            // Create associative array with default value 100
            $measuredCount = [];
            $maxCount = [];
            foreach ($ids as $id) {
                $measuredCount[$id] = 0;
                $maxCount[$id] = 100;
            }

            // Convert to JSON
            $measured = json_encode($measuredCount);
            $max = json_encode($maxCount);

            $device = eecs_device_info::create($data);

            $updateData = [
                'max_count' => $max,
                'measured_count' => $measured,
            ];

            floor_info::where('site_id', $data['site_id'])->update($updateData);

            $detectionList = explode(',', $device->detection_list);
            foreach ($detectionList as $detection) {
                $sensorNumber = $device->site_id . '_' . $device->id . '_' . $detection . '_Up';
                $sensorName = $device->site_id . '_' . $device->id . '_' . getTypeName($detection) . '_Up';
                $insertDataUp = [
                    'site_id' => $device->site_id,
                    'device_id' => $device->id,
                    'sensor_number' => $sensorNumber,
                    'sensor_name' => $sensorName,
                    'detection_type' => $detection,
                ];
                eecs_sensor_info::create($insertDataUp);

                $sensorNumber = $device->site_id . '_' . $device->id . '_' . $detection . '_Down';
                $sensorName = $device->site_id . '_' . $device->id . '_' . getTypeName($detection) . '_Down';
                $insertDataDown = [
                    'site_id' => $device->site_id,
                    'device_id' => $device->id,
                    'sensor_number' => $sensorNumber,
                    'sensor_name' => $sensorName,
                    'detection_type' => $detection,
                ];
                eecs_sensor_info::create($insertDataDown);

                $insertDetectionSite = [
                    'site_id' => $device->site_id,
                    'type_id' => $detection,
                    'name' => getTypeName($detection)
                ];
                detection_type_site::create($insertDetectionSite);
            }

            return redirect()->route('eecsdevice.index')->with('message', 'EECS Device Created Successfully');
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $getDevices = eecs_device_info::where('site_id', $id)->get();
        return response()->json([
            'data' => $getDevices,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            $types = detection_type::all();
            $editDevice = eecs_device_info::where('id', $id)->first();
            $detectionList = explode(',', $editDevice->detection_list);
            return view('eecsdevice.editeecsdevice', compact('editDevice', 'types', 'detectionList'));
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = [
            'detection_list' => $request->input('detection_list'),
            'device_name' => $request->input('device_name')
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            $deviceData = eecs_device_info::where('id', $id)->first();
            $siteId = $deviceData->site_id;
            $oldList = explode(',', $deviceData->detection_list);

            $updateData = eecs_device_info::where('id', $id)->update($data);
            $newList = explode(',', $data['detection_list']);
            $newSensors = array_diff($newList, $oldList);
            $oldSensors = array_diff($oldList, $newList);

            // Fetch all floor_info rows where site_id matches
            $floors = floor_info::where('site_id', $siteId)->get();

            foreach ($floors as $floor) {
                // Decode existing measured_count and max_count JSON
                $measuredCount = json_decode($floor->measured_count, true) ?? [];
                $maxCount = json_decode($floor->max_count, true) ?? [];

                // Add new sensors
                foreach ($newSensors as $sensorId) {
                    $measuredCount[$sensorId] = 0;
                    $maxCount[$sensorId] = 100;
                }

                // Remove old sensors
                foreach ($oldSensors as $sensorId) {
                    unset($measuredCount[$sensorId]);
                    unset($maxCount[$sensorId]);
                }

                // Save updated data back to DB
                $floor->measured_count = json_encode($measuredCount);
                $floor->max_count = json_encode($maxCount);
                $floor->save();
            }


            if ($oldSensors) {
                foreach ($oldSensors as $detection) {
                    $sensorNumberUp = $deviceData->site_id . '_' . $deviceData->id . '_' . $detection . '_Up';
                    $deletedRecords = eecs_sensor_info::where('sensor_number', $sensorNumberUp)->first();
                    eecs_data::where('sensor_id', $deletedRecords->id)->delete();
                    eecs_sensor_info::where('sensor_number', $sensorNumberUp)->delete();

                    $sensorNumberDown = $deviceData->site_id . '_' . $deviceData->id . '_' . $detection . '_Down';
                    $deletedRecords = eecs_sensor_info::where('sensor_number', $sensorNumberDown)->first();
                    eecs_data::where('sensor_id', $deletedRecords->id)->delete();
                    eecs_sensor_info::where('sensor_number', $sensorNumberDown)->delete();

                    detection_type_site::where('site_id', $deviceData->site_id)->where('type_id', $detection)->delete();
                }
            }
            if ($newSensors) {
                foreach ($newSensors as $detection) {
                    $sensorNumber = $deviceData->site_id . '_' . $deviceData->id . '_' . $detection . '_Up';
                    $sensorName = $deviceData->site_id . '_' . $deviceData->id . '_' . getTypeName($detection) . '_Up';
                    $insertDataUp = [
                        'site_id' => $deviceData->site_id,
                        'device_id' => $deviceData->id,
                        'sensor_number' => $sensorNumber,
                        'sensor_name' => $sensorName,
                        'detection_type' => $detection,
                    ];
                    eecs_sensor_info::create($insertDataUp);

                    $sensorNumber = $deviceData->site_id . '_' . $deviceData->id . '_' . $detection . '_Down';
                    $sensorName = $deviceData->site_id . '_' . $deviceData->id . '_' . getTypeName($detection) . '_Down';
                    $insertDataDown = [
                        'site_id' => $deviceData->site_id,
                        'device_id' => $deviceData->id,
                        'sensor_number' => $sensorNumber,
                        'sensor_name' => $sensorName,
                        'detection_type' => $detection,
                    ];
                    eecs_sensor_info::create($insertDataDown);

                    $insertDetectionSite = [
                        'site_id' => $deviceData->site_id,
                        'type_id' => $detection,
                        'name' => getTypeName($detection)
                    ];
                    detection_type_site::create($insertDetectionSite);
                }
            }

            return redirect()->route('eecsdevice.index')->with('message', 'Data Updated');
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            eecs_data::where('device_id', $id)->delete();
            eecs_sensor_info::where('device_id', $id)->delete();

            $eecsDevice = eecs_device_info::find($id);
            $floors = floor_info::where('site_id', $eecsDevice->site_id)->get();

            foreach ($floors as $floor) {
                $floor->measured_count = null;
                $floor->max_count = null;
                $floor->save();
            }

            detection_type_site::where('site_id', $eecsDevice->site_id)->delete();

            eecs_device_info::where('id', $id)->delete();

            return redirect()->route('eecsdevice.index')->with('message', 'Data Deleted');
        } else {
            return redirect('noaccess');
        }
    }
}
