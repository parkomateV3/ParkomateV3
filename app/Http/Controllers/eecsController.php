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
use Illuminate\Support\Facades\DB;

class eecsController extends Controller
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
            $eecsData = eecs_data::all();
            return view('eecs.eecs', compact('eecsData', 'siteData', 'can_edit', 'types'));
        }
        if ($role_id == 2) {
            $siteids = user_site_mapping::where('user_id', $id)->pluck('site_id')->implode(',');
            $array = explode(',', $siteids);
            $eecsData = eecs_data::whereIn('site_id', $array)->get();
            return view('eecs.eecs', compact('eecsData', 'siteData', 'can_edit', 'types'));
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
            'sensor_id' => 'required|unique:eecs_datas,sensor_id', // Check for unique sensor_no
        ]);

        $type = eecs_sensor_info::find($request->input('sensor_id'));
        $data = [
            'site_id' => $request->input('site_id'),
            'device_id' => $request->input('device_id'),
            'sensor_id' => $request->input('sensor_id'),
            'type' => $type->detection_type,
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            eecs_data::create($data);

            return redirect()->route('eecs.index')->with('message', 'EECS Data Created Successfully');
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            $types = detection_type::all();
            $editSensor = eecs_data::where('id', $id)->first();
            $floorData = floor_info::where('site_id', $editSensor->site_id)->get();
            return view('eecs.editeecs', compact('editSensor', 'floorData', 'types'));
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
            'site_id' => $request->input('site_id'),
            'device_id' => $request->input('device_id'),
            'sensor_id' => $request->input('sensor_id'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            $updateData = eecs_data::where('id', $id)->update($data);

            return redirect()->route('eecs.index')->with('message', 'Data Updated');
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
            eecs_data::where('id', $id)->delete();

            return redirect()->route('eecs.index')->with('message', 'Data Deleted');
        } else {
            return redirect('noaccess');
        }
    }
}
