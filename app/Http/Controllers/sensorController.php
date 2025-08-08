<?php

namespace App\Http\Controllers;

use App\Models\AccessPoint;
use App\Models\display_info;
use App\Models\displaydata;
use App\Models\sensor_data_logging;
use App\Models\sensor_info;
use App\Models\sensor_reservation;
use App\Models\site_info;
use App\Models\user_site_mapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class sensorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $id = Auth::id();
        $role_id = Auth::user()->role_id;
        $can_edit = Auth::user()->can_edit;
        $siteData = site_info::all();

        if ($role_id == 1) {
            $sensorData = sensor_info::all();
            return view('sensor.sensor', compact('sensorData', 'siteData', 'can_edit'));
        }
        if ($role_id == 2) {
            $siteids = user_site_mapping::where('user_id', $id)->pluck('site_id')->implode(',');
            $array = explode(',', $siteids);
            $sensorData = sensor_info::whereIn('site_id', $array)->get();
            return view('sensor.sensor', compact('sensorData', 'siteData', 'can_edit'));
        }
        if ($role_id == 3) {
            return redirect()->route('login');
        }

        // $id = Auth::id();
        // $access = AccessPoint::where('admin_id', $id)->first();
        // $array = explode(',', $access->site_id);
        // if ($access->superadmin == 1) {

        //     $sensorData = sensor_info::all();
        // } else {
        //     if ($access->site_id == '*') {
        //         $sensorData = sensor_info::all();
        //     } else {
        //         $sensorData = sensor_info::whereIn('site_id', $array)->get();
        //     }
        // }
        // $siteData = site_info::all();
        // return view('sensor.sensor', compact('sensorData', 'siteData', 'access'));
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
        // Validate the input, including checking if sensor_id is unique
        $request->validate([
            'sensor_no' => 'required|unique:sensor_infos,sensor_unique_no', // Check for unique sensor_no
            'floor_id' => 'required', // Check for unique sensor_no
            'zonal_id' => 'required', // Check for unique sensor_no
        ]);

        $checkData = sensor_info::where('site_id', $request->input('site_id'))->where('floor_id', $request->input('floor_id'))->where('zonal_id', $request->input('zonal_id'))->where('sensor_name', $request->input('sensor_name'))->get();

        $count = count($checkData);
        if ($count > 0) {
            return redirect()->back()->withErrors(['error' => 'Data Already Exist With Same Values!']);
        }

        if ($request->input('barrier_id') == null || $request->input('barrier_id') == "") {
            $barrier_color = null;
        } else {
            $barrier_color = $request->input('barrier_color');
        }

        $data = [
            'site_id' => $request->input('site_id'),
            'floor_id' => $request->input('floor_id'),
            'zonal_id' => $request->input('zonal_id'),
            'sensor_unique_no' => $request->input('sensor_no'),
            'sensor_name' => $request->input('sensor_name'),
            'sensor_range' => $request->input('sensor_range'),
            'color_occupied' => $request->input('color_occupied'),
            'color_available' => $request->input('color_available'),
            'role' => $request->input('role'),
            'barrier_id' => $request->input('barrier_id'),
            'barrier_color' => $barrier_color,
            'near_piller' => $request->input('near_piller'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            sensor_info::create($data);

            return redirect()->route('sensor.index')->with('message', 'Sensor Created');
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            $data = sensor_info::where('zonal_id', $id)->whereNotNull('barrier_id')->get();

            return response()->json([
                'data' => $data,
            ]);
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            $editSensor = sensor_info::where('sensor_id', $id)->first();
            $pillerData = getPillerData2($editSensor->floor_id);
            return view('sensor.editsensor', compact('editSensor', 'pillerData'));
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if ($request->input('sensor_no') != $request->input('sensor_no_old')) {
            $checkData = sensor_info::where('sensor_unique_no', $request->input('sensor_no'))->first();
            if ($checkData) {
                return redirect()->back()->withErrors(['error' => 'Sensor number should be unique.']);
            }
        }

        if ($request->input('barrier_id') == null || $request->input('barrier_id') == "") {
            $barrier_color = null;
        } else {
            $barrier_color = $request->input('barrier_color');
        }

        $data = [
            'site_id' => $request->input('site_id'),
            'floor_id' => $request->input('floor_id'),
            'zonal_id' => $request->input('zonal_id'),
            'sensor_name' => $request->input('sensor_name'),
            'sensor_unique_no' => $request->input('sensor_no'),
            'sensor_range' => $request->input('sensor_range'),
            'color_occupied' => $request->input('color_occupied'),
            'color_available' => $request->input('color_available'),
            'role' => $request->input('role'),
            'barrier_id' => $request->input('barrier_id'),
            'barrier_color' => $barrier_color,
            'near_piller' => $request->input('near_piller'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            $barrierData = sensor_reservation::where('sensor_id', $id)->first();
            if ($barrierData) {
                $barrierData->update(['barrier_unique_no' => $request->input('barrier_id')]);
            }

            $updateData = sensor_info::where('sensor_id', $id)->update($data);
            $datalogging = sensor_data_logging::where('sensor', $request->input('sensor_no_old'))->first();
            if (!empty($datalogging)) {
                $datalogging->sensor = $request->input('sensor_no');
                $datalogging->update();
            }

            return redirect()->route('sensor.index')->with('message', 'Data Updated');
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
            $sensorData = sensor_info::where('sensor_id', $id)->first();
            // echo $sensorData['site_id'];
            $displayData = displaydata::where('site_id', $sensorData['site_id'])->get();

            foreach ($displayData as $display) {
                $names = $display['floor_zonal_sensor_names'];
                $ids = $display['floor_zonal_sensor_ids'];
                $updatedNames = $this->filterNames($names, $sensorData['sensor_name']);
                $updatedIds = $this->filterNames($ids, $sensorData['sensor_id']);

                displaydata::where('site_id', $sensorData['site_id'])->update([
                    'floor_zonal_sensor_names' => $updatedNames,
                    'floor_zonal_sensor_ids' => $updatedIds
                ]);
            }
            $datalogging = sensor_data_logging::where('sensor', $sensorData->sensor_unique_no)->first();
            if (!empty($datalogging)) {
                $datalogging->delete();
            }

            // display_info::where('site_id', $sensorData['site_id'])->delete();
            sensor_reservation::where('sensor_id', $id)->delete();
            sensor_info::where('sensor_id', $id)->delete();

            return redirect()->route('sensor.index')->with('message', 'Data Deleted');
        } else {
            return redirect('noaccess');
        }
    }

    public function filterNames($data, $sensorData)
    {
        $inputData = json_decode($data, true);

        $sensors = explode(',', $inputData['Sensors']);

        $updatedSensorsArray = array_diff($sensors, [$sensorData]);

        $inputData['Sensors'] = implode(',', $updatedSensorsArray);

        $updatedJsonString = json_encode($inputData);

        // print_r($updatedJsonString);
        return $updatedJsonString;
    }
}
