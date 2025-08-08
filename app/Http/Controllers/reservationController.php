<?php

namespace App\Http\Controllers;

use App\Models\sensor_info;
use App\Models\sensor_reservation;
use App\Models\site_info;
use App\Models\user_site_mapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class reservationController extends Controller
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
            $reservationData = sensor_reservation::all();
            return view('reservation.reservation', compact('reservationData', 'siteData', 'can_edit'));
        }
        if ($role_id == 2) {
            $siteids = user_site_mapping::where('user_id', $id)->pluck('site_id')->implode(',');
            $array = explode(',', $siteids);
            $reservationData = sensor_reservation::whereIn('site_id', $array)->get();
            return view('reservation.reservation', compact('reservationData', 'siteData', 'can_edit'));
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
        // Validate the input, including checking if sensor_id is unique
        $request->validate([
            'sensor_id' => 'required|unique:sensor_reservations,sensor_id', // Check for unique sensor_no
        ]);

        // dd($request->all());
        $sensor = sensor_info::where('sensor_id', $request->input('sensor_id'))->first();
        
        $data = [
            'site_id' => $request->input('site_id'),
            'floor_id' => $request->input('floor_id'),
            'zonal_id' => $request->input('zonal_id'),
            'sensor_id' => $request->input('sensor_id'),
            'barrier_unique_no' => $sensor->barrier_id,
            'is_blocked' => $request->input('is_blocked'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            sensor_reservation::create($data);

            return redirect()->route('reservation.index')->with('message', 'Sensor Reservation Created');
        } else {
            return redirect('noaccess');
        }
    }

    public function barrierStatusUpdate(Request $request, $id)
    {
        $data = sensor_reservation::find($id);
        $data->is_blocked = $request->input('is_blocked');
        // $data->update();
        if ($data->update()) {
            return response()->json(['s' => '1', 'data' => 'Updated.']);
        } else {
            return response()->json(['s' => '0', 'data' => 'Data Not Updated.']);
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
