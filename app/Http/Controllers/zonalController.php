<?php

namespace App\Http\Controllers;

use App\Models\AccessPoint;
use App\Models\display_info;
use App\Models\displaydata;
use App\Models\floor_info;
use App\Models\sensor_data_logging;
use App\Models\sensor_info;
use App\Models\sensor_reservation;
use App\Models\site_info;
use App\Models\user_site_mapping;
use App\Models\zonal_info;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class zonalController extends Controller
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
            $zonalData = zonal_info::all();
            return view('zonal.zonal', compact('zonalData', 'siteData', 'can_edit'));
        }
        if ($role_id == 2) {
            $siteids = user_site_mapping::where('user_id', $id)->pluck('site_id')->implode(',');
            $array = explode(',', $siteids);
            $zonalData = zonal_info::whereIn('site_id', $array)->get();
            return view('zonal.zonal', compact('zonalData', 'siteData', 'can_edit'));
        }
        if ($role_id == 3) {
            return redirect()->route('login');
        }

        // $id = Auth::id();
        // $access = AccessPoint::where('admin_id', $id)->first();
        // $array = explode(',', $access->site_id);
        // if ($access->superadmin == 1) {
        //     $zonalData = zonal_info::all();
        // } else {
        //     if ($access->site_id == '*') {
        //         $zonalData = zonal_info::all();
        //     } else {
        //         $zonalData = zonal_info::whereIn('site_id', $array)->get();
        //     }
        // }
        // $siteData = site_info::all();

        // return view('zonal.zonal', compact('zonalData', 'siteData', 'access'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            $siteData = site_info::all();
            return view('zonal.updatezonal', compact('siteData'));
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the input, including checking if zonal_id is unique
        $request->validate([
            'zonal_no' => 'required|unique:zonal_infos,zonal_unique_no', // Check for unique zonal_no
            'floor_id' => 'required', // Check for unique zonal_no
            'site_id' => 'required', // Check for unique zonal_no
        ]);

        $checkData = zonal_info::where('site_id', $request->input('site_id'))->where('floor_id', $request->input('floor_id'))->where('zonal_name', $request->input('zonal_name'))->get();

        $count = count($checkData);
        if ($count > 0) {
            return redirect()->back()->withErrors(['error' => 'Data Already Exist With Same Values!']);
        }

        $data = [
            'site_id' => $request->input('site_id'),
            'floor_id' => $request->input('floor_id'),
            'zonal_unique_no' => $request->input('zonal_no'),
            'zonal_name' => $request->input('zonal_name'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            zonal_info::create($data);
            return redirect()->route('zonal.index')->with('message', 'Zonal Created');
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
            // $data = floor_info::where('site_id', $id)->get()->orderBy('floor_name', 'ASC');
            $data = DB::table('zonal_infos')
                ->leftJoin('floor_infos', 'floor_infos.floor_id', '=', 'zonal_infos.floor_id')
                ->where('zonal_infos.floor_id', '=', $id)
                ->select('*', 'zonal_infos.floor_id as zonalfloorid')
                ->get();
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
            $editZonal = zonal_info::where('zonal_id', $id)->first();
            return view('zonal.editzonal', compact('editZonal'));
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if ($request->input('zonal_no') != $request->input('zonal_no_old')) {
            $checkData = zonal_info::where('zonal_unique_no', $request->input('zonal_no'))->first();
            if ($checkData) {
                return redirect()->back()->withErrors(['error' => 'Zonal number should be unique.']);
            }
        }

        $data = [
            'site_id' => $request->input('site_id'),
            'floor_id' => $request->input('floor_id'),
            'zonal_name' => $request->input('zonal_name'),
            'zonal_unique_no' => $request->input('zonal_no'),
        ];
        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            $updateData = zonal_info::where('zonal_id', $id)->update($data);

            return redirect()->route('zonal.index')->with('message', 'Data Updated');
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
            $zonalData = zonal_info::where('zonal_id', $id)->first();
            // echo $zonalData['site_id'];
            $displayData = displaydata::where('site_id', $zonalData['site_id'])->get();

            foreach ($displayData as $display) {
                $names = $display['floor_zonal_sensor_names'];
                $ids = $display['floor_zonal_sensor_ids'];
                $updatedNames = $this->filterNames($names, $zonalData['zonal_name']);
                $updatedIds = $this->filterNames($ids, $zonalData['zonal_id']);

                displaydata::where('site_id', $zonalData['site_id'])->update([
                    'floor_zonal_sensor_names' => $updatedNames,
                    'floor_zonal_sensor_ids' => $updatedIds
                ]);
            }
            // display_info::where('site_id', $zonalData['site_id'])->delete();
            sensor_info::where('zonal_id', $id)->delete();
            sensor_reservation::where('zonal_id', $id)->delete();
            sensor_data_logging::where('zonal_id', $id)->delete();
            zonal_info::where('zonal_id', $id)->delete();

            return redirect()->route('zonal.index')->with('message', 'Data Deleted');
        } else {
            return redirect('noaccess');
        }
    }

    public function filterNames($data, $zonalData)
    {
        $inputData = json_decode($data, true);

        $zonals = explode(',', $inputData['Zonals']);

        $updatedZonalsArray = array_diff($zonals, [$zonalData]);

        $inputData['Zonals'] = implode(',', $updatedZonalsArray);

        $updatedJsonString = json_encode($inputData);

        // print_r($updatedJsonString);
        return $updatedJsonString;
    }
}
