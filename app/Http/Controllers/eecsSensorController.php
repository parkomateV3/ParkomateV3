<?php

namespace App\Http\Controllers;

use App\Models\detection_type;
use App\Models\eecs_data;
use App\Models\eecs_sensor_info;
use App\Models\floor_info;
use App\Models\site_info;
use App\Models\user_site_mapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class eecsSensorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $id = Auth::id();
        $role_id = Auth::user()->role_id;
        $can_edit = Auth::user()->can_edit;
        $siteData = site_info::where('site_type_of_product', 'eecs')->get();
        $types = detection_type::all();

        if ($role_id == 1) {
            $eecsData = eecs_sensor_info::all();
            return view('eecssensor.eecssensor', compact('eecsData', 'siteData', 'can_edit', 'types'));
        }
        if ($role_id == 2) {
            $siteids = user_site_mapping::where('user_id', $id)->pluck('site_id')->implode(',');
            $array = explode(',', $siteids);
            $eecsData = eecs_sensor_info::whereIn('site_id', $array)->get();
            return view('eecssensor.eecssensor', compact('eecsData', 'siteData', 'can_edit', 'types'));
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
            'sensor_number' => 'required|unique:eecs_sensor_infos,sensor_number', // Check for unique sensor_no
        ]);

        $data = [
            'site_id' => $request->input('site_id'),
            'device_id' => $request->input('device_id'),
            'sensor_number' => $request->input('sensor_number'),
            'sensor_name' => $request->input('sensor_name'),
            'detection_type' => $request->input('detection_type'),
        ];

        $can_edit = Auth::user()->can_edit;
        // Check if the user has permission to edit 
        if ($can_edit == 1) {

            eecs_sensor_info::create($data);

            return redirect()->route('eecssensor.index')->with('message', 'EECS Sensor Created Successfully');
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $getSensors = eecs_sensor_info::where('device_id', $id)->get();
        return response()->json([
            'data' => $getSensors,
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
            $editSensor = eecs_sensor_info::where('id', $id)->first();
            return view('eecssensor.editeecssensor', compact('editSensor', 'types'));
        } else {
            return redirect('noaccess');
        }
    }

    public function getSensors($id)
    {
        $getSensors = eecs_sensor_info::where('site_id', $id)->get();
        return response()->json([
            'data' => $getSensors,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = [
            'detection_type' => $request->input('detection_type'),
            'sensor_name' => $request->input('sensor_name'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            $updateData = eecs_sensor_info::where('id', $id)->update($data);

            return redirect()->route('eecssensor.index')->with('message', 'Data Updated');
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
            eecs_data::where('sensor_id', $id)->delete(); // Delete related data from eecs_data
            eecs_sensor_info::where('id', $id)->delete();

            return redirect()->route('eecssensor.index')->with('message', 'Data Deleted');
        } else {
            return redirect('noaccess');
        }
    }
}
