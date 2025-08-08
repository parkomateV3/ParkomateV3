<?php

namespace App\Http\Controllers;

use App\Models\display_info;
use App\Models\displaydata;
use App\Models\floor_info;
use App\Models\sensor_info;
use App\Models\site_info;
use App\Models\user_site_mapping;
use App\Models\zonal_info;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class displaydataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $id = Auth::id();
        $role_id = Auth::user()->role_id;
        $can_edit = Auth::user()->can_edit;
        $displaysData = display_info::all();

        if ($role_id == 1) {
            $displayData = displaydata::all();
            return view('display.displaydata', compact('displaysData', 'displayData', 'can_edit'));
        }
        if ($role_id == 2) {
            $siteids = user_site_mapping::where('user_id', $id)->pluck('site_id')->implode(',');
            $array = explode(',', $siteids);
            $displayData = displaydata::whereIn('site_id', $array)->get();
            return view('display.displaydata', compact('displaysData', 'displayData', 'can_edit'));
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
        // dd($request->all());
        // exit;

        // Validate the input, including checking if display_id is unique
        // $request->validate([
        //     'valueNames' => 'required',
        //     // 'display_no' => 'required|unique:display_infos,display_unique_no', // Check for unique display_no
        //     'logic_no' => 'required',
        // ], [
        //     'valueNames.required' => 'The floor_zonal_sensor values is required.',
        //     'logic_no.required' => 'The logic_to_calculate_numbers field is required.',
        // ]);

        $site = display_info::where('display_id', $request->input('display_id'))->first();

        $data = [
            'site_id' => $site->site_id,
            'display_id' => $request->input('display_id'),
            'floor_zonal_sensor_ids' => $request->input('values'),
            'floor_zonal_sensor_names' => $request->input('valueNames'),
            'logic_calculate_number' => $request->input('logic_no'),
            'display_format' => $request->input('display_format'),
            'math' => $request->input('math'),
            'font' => $request->input('font'),
            'font_size' => $request->input('font_size'),
            'color' => $request->input('color'),
            'coordinates' => $request->input('coordinates'),
        ];
        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            displaydata::create($data);

            return redirect()->route('displaydata.index')->with('message', 'Display Created');
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
            $data = display_info::where('display_id', $id)->first();
            $floorData = floor_info::where('site_id', $data->site_id)->get();
            $zonalData = zonal_info::where('site_id', $data->site_id)->get();
            $sensorData = sensor_info::where('site_id', $data->site_id)->get();
            return response()->json([
                'floor' => $floorData,
                'zonal' => $zonalData,
                'sensor' => $sensorData,
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
        // dd($id);
        // exit;

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            $editDisplay = displaydata::where('data_id', $id)->first();
            $floorData = floor_info::where('site_id', $editDisplay['site_id'])->get();
            $zonalData = zonal_info::where('site_id', $editDisplay['site_id'])->get();
            $sensorData = sensor_info::where('site_id', $editDisplay['site_id'])->get();

            $inputJson = $editDisplay['floor_zonal_sensor_names'];
            $inputData = json_decode($inputJson, true);

            // Split the comma-separated values into arrays
            $floors = explode(',', $inputData['Floor']);
            $zonals = explode(',', $inputData['Zonals']);
            $sensors = explode(',', $inputData['Sensors']);

            $logic_no = explode(',', $editDisplay['logic_calculate_number']);

            // dd($sensors);
            // exit;

            return view('display.editdisplaydata', compact('editDisplay', 'floorData', 'zonalData', 'sensorData', 'floors', 'zonals', 'sensors', 'logic_no'));
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());

        $data = [
            'floor_zonal_sensor_ids' => $request->input('values'),
            'floor_zonal_sensor_names' => $request->input('valueNames'),
            'logic_calculate_number' => $request->input('logic_no'),
            'display_format' => $request->input('display_format'),
            'math' => $request->input('math'),
            'font' => $request->input('font'),
            'font_size' => $request->input('font_size'),
            'color' => $request->input('color'),
            'coordinates' => $request->input('coordinates'),
        ];
        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            $updateData = displaydata::where('data_id', $id)->update($data);

            return redirect()->route('displaydata.index')->with('message', 'Data Updated');
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
            displaydata::where('data_id', $id)->delete();
            return redirect()->route('displaydata.index')->with('message', 'Data Deleted');
        } else {
            return redirect('noaccess');
        }
    }
}
