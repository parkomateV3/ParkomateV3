<?php

namespace App\Http\Controllers;

use App\Models\AccessPoint;
use App\Models\display_info;
use App\Models\displaydata;
use App\Models\floor_data_by_day;
use App\Models\floor_data_by_hour;
use App\Models\floor_data_by_minute;
use App\Models\floor_info;
use App\Models\sensor_data_logging;
use App\Models\sensor_info;
use App\Models\sensor_reservation;
use App\Models\site_info;
use App\Models\user_site_mapping;
use App\Models\zonal_info;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class floorController extends Controller
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
            $floorData = floor_info::all();
            return view('floor.floor', compact('floorData', 'siteData', 'can_edit'));
        }
        if ($role_id == 2) {
            $siteids = user_site_mapping::where('user_id', $id)->pluck('site_id')->implode(',');
            $array = explode(',', $siteids);
            $floorData = floor_info::whereIn('site_id', $array)->get();
            return view('floor.floor', compact('floorData', 'siteData', 'can_edit'));
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
        $request->validate([
            'floor_image' => 'mimes:jpeg,png,jpg,gif,svg|max:2048',
            'interconnect_location_symbol' => 'mimes:jpeg,png,jpg,gif,svg|max:2048',
            'destination_location_symbol' => 'mimes:jpeg,png,jpg,gif,svg|max:2048',
            'current_location_symbol' => 'mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);


        $checkData = floor_info::where('site_id', $request->input('site_id'))->where('floor_name', $request->input('floor_name'))->get();
        $count = count($checkData);
        if ($count > 0) {
            return redirect()->back()->withErrors(['error' => 'Data Already Exist With Same Values!']);
        }

        $data = [
            'site_id' => $request->input('site_id'),
            'floor_name' => $request->input('floor_name'),
            'piller_name' => $request->input('piller_name'),
            'piller_coordinates' => $request->input('piller_coordinates'),
            'symbol_size' => $request->input('symbol_size'),
            'floor_image_sensor_mapping_dimenssion' => $request->input('floor_image_sensor_mapping_dimenssion'),
            'car_scale' => $request->input('car_scale'),
            'floor_map_coordinate' => $request->input('floor_map_coordinate'),
            'label_properties' => $request->input('label_properties'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            if ($request->hasFile('floor_image')) {
                $floor_img = $request->file('floor_image')->getClientOriginalName();
                $request->file('floor_image')->move(public_path('floors'), $floor_img);
                $data['floor_image'] = $floor_img; // Add file path to data array
            } else {
                $data['floor_image'] = null;
            }

            if ($request->hasFile('current_location_symbol')) {
                $current_img = $request->file('current_location_symbol')->getClientOriginalName();
                $request->file('current_location_symbol')->move(public_path('symbols'), $current_img);
                $data['current_location_symbol'] = $current_img; // Add file path to data array
            } else {
                $data['current_location_symbol'] = "current_location.png";
            }

            if ($request->hasFile('destination_location_symbol')) {
                $destination_img = $request->file('destination_location_symbol')->getClientOriginalName();
                $request->file('destination_location_symbol')->move(public_path('symbols'), $destination_img);
                $data['destination_location_symbol'] = $destination_img; // Add file path to data array
            } else {
                $data['destination_location_symbol'] = "destination_location.png";
            }

            if ($request->hasFile('interconnect_location_symbol')) {
                $interconnect_img = $request->file('interconnect_location_symbol')->getClientOriginalName();
                $request->file('interconnect_location_symbol')->move(public_path('symbols'), $interconnect_img);
                $data['interconnect_location_symbol'] = $interconnect_img; // Add file path to data array
            } else {
                $data['interconnect_location_symbol'] = "interconnect_location.png";
            }

            floor_info::create($data);

            return redirect()->route('floor.index')->with('message', 'Floor Created');
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
            $data = DB::table('floor_infos')
                ->leftJoin('site_infos', 'floor_infos.site_id', '=', 'site_infos.site_id')
                ->where('floor_infos.site_id', '=', $id)
                ->where('floor_infos.deleted_at', null)
                ->select('*', 'floor_infos.site_id as floorsiteid', 'site_infos.site_id as siteid')
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

            $editFloor = floor_info::where('floor_id', $id)->first();
            $siteData = site_info::all();
            return view('floor.editfloor', compact('editFloor', 'siteData'));
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if ($request->input('floor_name') != $request->input('floor_name_old')) {
            $checkData = floor_info::where('site_id', $request->input('site_id'))->where('floor_name', $request->input('floor_name'))->get();
            if (count($checkData) > 0) {
                return redirect()->back()->withErrors(['error' => 'Data Already Exist With Same Values!']);
            }
        }
        $data = [
            'site_id' => $request->input('site_id'),
            'floor_name' => $request->input('floor_name'),
            'piller_name' => $request->input('piller_name'),
            'piller_coordinates' => $request->input('piller_coordinates'),
            'symbol_size' => $request->input('symbol_size'),
            'floor_image_sensor_mapping_dimenssion' => $request->input('floor_image_sensor_mapping_dimenssion'),
            'car_scale' => $request->input('car_scale'),
            'floor_map_coordinate' => $request->input('floor_map_coordinate'),
            'label_properties' => $request->input('label_properties'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            if ($request->hasFile('floor_image')) {
                // Delete the old floor_image file
                $oldFloorPath = public_path('floors/' . $request->input('old_floor_image'));
                if (File::exists($oldFloorPath)) {
                    File::delete($oldFloorPath);
                }
                $floor_image = $request->file('floor_image')->getClientOriginalName();
                $request->file('floor_image')->move(public_path('floors'), $floor_image);
                $data['floor_image'] = $floor_image; // Add file path to data array
            } else {
                $data['floor_image'] = $request->input('old_floor_image');
            }

            if ($request->hasFile('current_location_symbol')) {
                // Delete the old current_location_symbol file
                $oldCurrentPath = public_path('symbols/' . $request->input('old_current_location_symbol'));
                if (File::exists($oldCurrentPath)) {
                    File::delete($oldCurrentPath);
                }
                $current_img = $request->file('current_location_symbol')->getClientOriginalName();
                $request->file('current_location_symbol')->move(public_path('symbols'), $current_img);
                $data['current_location_symbol'] = $current_img; // Add file path to data array
            } else {
                $data['current_location_symbol'] = $request->input('old_current_location_symbol');
            }

            if ($request->hasFile('destination_location_symbol')) {
                // Delete the old destination_location_symbol file
                $oldDestinationPath = public_path('symbols/' . $request->input('old_destination_location_symbol'));
                if (File::exists($oldDestinationPath)) {
                    File::delete($oldDestinationPath);
                }
                $destination_img = $request->file('destination_location_symbol')->getClientOriginalName();
                $request->file('destination_location_symbol')->move(public_path('symbols'), $destination_img);
                $data['destination_location_symbol'] = $destination_img; // Add file path to data array
            } else {
                $data['destination_location_symbol'] = $request->input('old_destination_location_symbol');
            }

            if ($request->hasFile('interconnect_location_symbol')) {
                // Delete the old interconnect_location_symbol file
                $oldInterconnectPath = public_path('symbols/' . $request->input('old_interconnect_location_symbol'));
                if (File::exists($oldInterconnectPath)) {
                    File::delete($oldInterconnectPath);
                }
                $interconnect_img = $request->file('interconnect_location_symbol')->getClientOriginalName();
                $request->file('interconnect_location_symbol')->move(public_path('symbols'), $interconnect_img);
                $data['interconnect_location_symbol'] = $interconnect_img; // Add file path to data array
            } else {
                $data['interconnect_location_symbol'] = $request->input('old_interconnect_location_symbol');
            }

            $updateData = floor_info::where('floor_id', $id)->update($data);

            return redirect()->route('floor.index')->with('message', 'Data Updated');
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
            $floorData = floor_info::where('floor_id', $id)->first();
            // echo $floorData['site_id'];
            $displayData = displaydata::where('site_id', $floorData['site_id'])->get();

            foreach ($displayData as $display) {
                $names = $display['floor_zonal_sensor_names'];
                $ids = $display['floor_zonal_sensor_ids'];
                $updatedNames = $this->filterNames($names, $floorData['floor_name']);
                $updatedIds = $this->filterNames($ids, $floorData['floor_id']);

                displaydata::where('site_id', $floorData['site_id'])->update([
                    'floor_zonal_sensor_names' => $updatedNames,
                    'floor_zonal_sensor_ids' => $updatedIds
                ]);
            }
            // display_info::where('site_id', $floorData['site_id'])->delete();
            sensor_info::where('floor_id', $id)->delete();
            zonal_info::where('floor_id', $id)->delete();
            sensor_reservation::where('floor_id', $id)->delete();
            sensor_data_logging::where('floor_id', $id)->delete();
            floor_data_by_minute::where('floor_id', $id)->delete();
            floor_data_by_hour::where('floor_id', $id)->delete();
            floor_data_by_day::where('floor_id', $id)->delete();
            floor_info::where('floor_id', $id)->delete();

            return redirect()->route('floor.index')->with('message', 'Data Deleted');
        } else {
            return redirect('noaccess');
        }
    }

    public function filterNames($data, $floorData)
    {
        $inputData = json_decode($data, true);

        $floors = explode(',', $inputData['Floor']);

        $updatedFloorsArray = array_diff($floors, [$floorData]);

        $inputData['Floor'] = implode(',', $updatedFloorsArray);

        $updatedJsonString = json_encode($inputData);

        // print_r($updatedJsonString);
        return $updatedJsonString;
    }
}
