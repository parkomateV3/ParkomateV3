<?php

namespace App\Http\Controllers;

use App\Models\display_info;
use App\Models\displaydata;
use App\Models\floor_data_by_day;
use App\Models\floor_data_by_hour;
use App\Models\floor_data_by_minute;
use App\Models\floor_info;
use App\Models\floor_interconnection;
use App\Models\sensor_data_logging;
use App\Models\sensor_info;
use App\Models\sensor_reservation;
use App\Models\site_data_by_day;
use App\Models\site_data_by_hour;
use App\Models\site_data_by_minute;
use App\Models\site_info;
use App\Models\symbol_on_display;
use App\Models\table_entry;
use App\Models\table_info;
use App\Models\user_site_mapping;
use App\Models\zonal_info;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class siteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $id = Auth::id();
        $role_id = Auth::user()->role_id;
        $can_edit = Auth::user()->can_edit;
        if ($role_id == 1) {
            $siteData = site_info::all();
            return view('site.site', compact('siteData', 'can_edit'));
        }
        if ($role_id == 2) {
            $siteids = user_site_mapping::where('user_id', $id)->pluck('site_id')->implode(',');
            $array = explode(',', $siteids);
            $siteData = site_info::whereIn('site_id', $array)->get();
            return view('site.site', compact('siteData', 'can_edit'));
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
            'username' => 'required|unique:site_infos,site_username',
            'logo' => 'mimes:jpeg,png,jpg,gif,svg|max:2048',
            'ad_image' => 'mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $key = Str::random(32);
        $data = [
            'site_name' => $request->input('site_name'),
            'site_username' => $request->input('username'),
            'site_city' => $request->input('city'),
            'site_state' => $request->input('state'),
            'site_country' => $request->input('country'),
            'site_location' => $request->input('location'),
            'site_status' => $request->input('status'),
            'site_type_of_product' => $request->input('typeofproduct'),
            'number_of_floors' => $request->input('floors'),
            'number_of_zonals' => $request->input('zonals'),
            'number_of_sensors' => $request->input('sensors'),
            'number_of_displays' => $request->input('displays'),
            'email' => $request->input('email'),
            'report_frequency' => $request->input('report'),
            'financial_model' => $request->input('financial_model'),
            'overtime_hours' => $request->input('overtime_hours') == null ? 24 : $request->input('overtime_hours'),
            'api_key' => $key,
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            if ($request->hasFile('logo')) {
                $logoname = $request->file('logo')->getClientOriginalName();
                $request->file('logo')->move(public_path('logos'), $logoname);
                $data['site_logo'] = $logoname; // Add file path to data array
            } else {
                $data['site_logo'] = null;
            }

            if ($request->hasFile('ad_image')) {
                $logoname1 = $request->file('ad_image')->getClientOriginalName();
                $request->file('ad_image')->move(public_path('logos'), $logoname1);
                $data['ad_image'] = $logoname1; // Add file path to data array
            } else {
                $data['ad_image'] = null;
            }

            site_info::create($data);

            return redirect()->route('site.index')->with('message', 'Site Created');
        } else {
            return redirect('noaccess');
        }
        // dd($request->all());
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
            $siteData = site_info::where('site_id', $id)->first();
            return view('site.siteupdate', compact('siteData'));
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // $request->validate([
        //     'username' => 'required|unique:site_infos,site_username',
        //     'email' => 'required|unique:site_infos,email',
        //     'logo' => 'mimes:jpeg,png,jpg,gif,svg|max:2048',
        // ]);
        // dd($request->all());
        // exit;

        if ($request->input('site_name') != $request->input('site_name_old')) {
            $checkData = site_info::where('site_name', $request->input('site_name'))->get();
            if (count($checkData) > 0) {
                return redirect()->back()->withErrors(['error' => 'Site name already exist!']);
            }
        }

        $data = [
            'site_name' => $request->input('site_name'),
            'site_city' => $request->input('city'),
            'site_state' => $request->input('state'),
            'site_country' => $request->input('country'),
            'site_location' => $request->input('location'),
            'site_status' => $request->input('status'),
            'site_type_of_product' => $request->input('typeofproduct'),
            'number_of_floors' => $request->input('floors'),
            'number_of_zonals' => $request->input('zonals'),
            'number_of_sensors' => $request->input('sensors'),
            'number_of_displays' => $request->input('displays'),
            'email' => $request->input('email'),
            'report_frequency' => $request->input('report'),
            'financial_model' => $request->input('financial_model'),
            'overtime_hours' => $request->input('overtime_hours'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            if ($request->hasFile('logo')) {
                // Delete the old logo file
                $oldLogoPath = public_path('logos/' . $request->input('old_logo'));
                if (File::exists($oldLogoPath)) {
                    File::delete($oldLogoPath);
                }
                $logoname = $request->file('logo')->getClientOriginalName();
                $request->file('logo')->move(public_path('logos'), $logoname);
                $data['site_logo'] = $logoname; // Add file path to data array
            } else {
                $data['site_logo'] = $request->input('old_logo');
            }

            if ($request->hasFile('ad_image')) {
                // Delete the old ad_image file
                $oldLogoPath1 = public_path('logos/' . $request->input('old_ad_image'));
                if (File::exists($oldLogoPath1)) {
                    File::delete($oldLogoPath1);
                }
                $logoname1 = $request->file('ad_image')->getClientOriginalName();
                $request->file('ad_image')->move(public_path('logos'), $logoname1);
                $data['ad_image'] = $logoname1; // Add file path to data array
            } else {
                $data['ad_image'] = $request->input('old_ad_image');
            }

            $updateData = site_info::where('site_id', $id)->update($data);

            return redirect()->route('site.index')->with('message', 'Data Updated');
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
            $displayIds = display_info::where('site_id', $id)->get();
            if (count($displayIds) > 0) {

                foreach ($displayIds as $display) {
                    $displayId = $display->display_id;
                    symbol_on_display::where('display_id', $displayId)->delete();
                }
            }
            displaydata::where('site_id', $id)->delete();
            display_info::where('site_id', $id)->delete();
            sensor_info::where('site_id', $id)->delete();
            zonal_info::where('site_id', $id)->delete();
            floor_info::where('site_id', $id)->delete();
            sensor_reservation::where('site_id', $id)->delete();
            sensor_data_logging::where('site_id', $id)->delete();
            site_data_by_minute::where('site_id', $id)->delete();
            site_data_by_hour::where('site_id', $id)->delete();
            site_data_by_day::where('site_id', $id)->delete();
            floor_data_by_minute::where('site_id', $id)->delete();
            floor_data_by_hour::where('site_id', $id)->delete();
            floor_data_by_day::where('site_id', $id)->delete();
            table_entry::where('site_id', $id)->delete();
            table_info::where('site_id', $id)->delete();
            floor_interconnection::where('site_id', $id)->delete();
            site_info::where('site_id', $id)->delete();

            return redirect()->route('site.index')->with('message', 'Data Deleted');
        } else {
            return redirect('noaccess');
        }
    }

    public function test()
    {
        // $siteData = site_info::get();
        // $floorData = floor_info::get();
        // $datetime = Carbon::now()->format('Y-m-d H:i:s');

        // foreach ($siteData as $site) {

        //     $sensorData = [];
        //     $occupied = 0;
        //     $available = 0;
        //     $sensorinfo = sensor_data_logging::where('site_id', $site['site_id'])->get();
        //     foreach ($sensorinfo as $sensor) {
        //         $sensorData[$sensor->sensor] = $sensor->status;
        //         if ($sensor->status == 1) {
        //             $occupied++;
        //         }
        //         if ($sensor->status == 0) {
        //             $available++;
        //         }
        //     }
        //     $data = json_encode($sensorData);
        //     $dataStore = [
        //         'site_id' => $site['site_id'],
        //         'data' => $data,
        //         'date_time' => $datetime,
        //         'total_occupied' => $occupied,
        //         'total_available' => $available
        //     ];
        //     site_data_by_minute::create($dataStore);
        // }
        // foreach ($floorData as $floor) {

        //     $sensorData = [];
        //     $occupied = 0;
        //     $available = 0;
        //     $floorinfo = sensor_data_logging::where('floor_id', $floor['floor_id'])->get();
        //     foreach ($floorinfo as $floor) {
        //         $sensorData[$floor->sensor] = $floor->status;
        //         if ($floor->status == 1) {
        //             $occupied++;
        //         }
        //         if ($floor->status == 0) {
        //             $available++;
        //         }
        //     }
        //     $data = json_encode($sensorData);
        //     $dataStore = [
        //         'floor_id' => $floor['floor_id'],
        //         'data' => $data,
        //         'date_time' => $datetime,
        //         'total_occupied' => $occupied,
        //         'total_available' => $available
        //     ];
        //     floor_data_by_minute::create($dataStore);
        // }
        // // $data = json_encode($sensorData);
        // echo "success";
    }
}
