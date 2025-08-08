<?php

namespace App\Http\Controllers;

use App\Models\floor_info;
use App\Models\sensor_info;
use App\Models\site_info;
use App\Models\table_entry;
use App\Models\table_info;
use App\Models\user_site_mapping;
use App\Models\zonal_info;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class tableEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $id = Auth::id();
        $role_id = Auth::user()->role_id;
        $can_edit = Auth::user()->can_edit;
        $tableData = table_info::all();
        if ($role_id == 1) {
            $tableEntry = table_entry::all();
            return view('table.tableentry', compact('tableEntry', 'tableData', 'can_edit'));
        }
        if ($role_id == 2) {
            $siteids = user_site_mapping::where('user_id', $id)->pluck('site_id')->implode(',');
            $array = explode(',', $siteids);
            $tableEntry = table_entry::whereIn('site_id', $array)->get();
            return view('floor.floor', compact('tableEntry', 'tableData', 'can_edit'));
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
        $tableData = table_info::where('table_id', $request->input('table_id'))->first();

        $data = [
            'table_id' => $request->input('table_id'),
            'site_id' => $tableData->site_id,
            'entry_name' => $request->input('entry_name'),
            'floor_zonal_sensor_ids' => $request->input('values'),
            'floor_zonal_sensor_names' => $request->input('valueNames'),
            'logic_to_calculate_numbers' => $request->input('logic_no'),
        ];
        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            table_entry::create($data);

            return redirect()->route('entries.index')->with('message', 'Entry Created');
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
            $data = table_info::where('table_id', $id)->first();
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
        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            $editTableEntry = table_entry::where('entry_id', $id)->first();
            $floorData = floor_info::where('site_id', $editTableEntry['site_id'])->get();
            $zonalData = zonal_info::where('site_id', $editTableEntry['site_id'])->get();
            $sensorData = sensor_info::where('site_id', $editTableEntry['site_id'])->get();

            $inputJson = $editTableEntry['floor_zonal_sensor_names'];
            $inputData = json_decode($inputJson, true);

            // Split the comma-separated values into arrays
            $floors = explode(',', $inputData['Floor']);
            $zonals = explode(',', $inputData['Zonals']);
            $sensors = explode(',', $inputData['Sensors']);

            $logic_no = explode(',', $editTableEntry['logic_to_calculate_numbers']);

            // dd($sensors);
            // exit;

            return view('table.edittableentry', compact('editTableEntry', 'floorData', 'zonalData', 'sensorData', 'floors', 'zonals', 'sensors', 'logic_no'));
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
            'entry_name' => $request->input('entry_name'),
            'floor_zonal_sensor_ids' => $request->input('values'),
            'floor_zonal_sensor_names' => $request->input('valueNames'),
            'logic_to_calculate_numbers' => $request->input('logic_no'),
        ];
        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            $updateData = table_entry::where('entry_id', $id)->update($data);

            return redirect()->route('entries.index')->with('message', 'Entry Updated');
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
            table_entry::where('entry_id', $id)->delete();
            return redirect()->route('entries.index')->with('message', 'Data Deleted');
        } else {
            return redirect('noaccess');
        }
    }
}
