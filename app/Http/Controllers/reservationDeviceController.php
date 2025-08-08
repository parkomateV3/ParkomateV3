<?php

namespace App\Http\Controllers;

use App\Models\floor_info;
use App\Models\reservation_device_info;
use App\Models\site_info;
use App\Models\user_site_mapping;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class reservationDeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $id = Auth::id();
        $role_id = Auth::user()->role_id;
        $can_edit = Auth::user()->can_edit;
        $siteData = site_info::where('site_type_of_product', 'slot_reservation')->get();

        if ($role_id == 1) {
            $reservationData = reservation_device_info::all();
            return view('slot.slot_reservation', compact('reservationData', 'siteData', 'can_edit'));
        }
        if ($role_id == 2) {
            $siteids = user_site_mapping::where('user_id', $id)->pluck('site_id')->implode(',');
            $array = explode(',', $siteids);
            $reservationData = reservation_device_info::whereIn('site_id', $array)->get();
            return view('slot.slot_reservation', compact('reservationData', 'siteData', 'can_edit'));
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
            'reservation_number' => 'required|unique:reservation_device_infos,reservation_number',
        ]);

        $data = [
            'site_id' => $request->input('site_id'),
            'floor_id' => $request->input('floor_id'),
            'zonal_id' => $request->input('zonal_id'),
            'reservation_number' => $request->input('reservation_number'),
            'reservation_name' => $request->input('reservation_name'),
            'status' => $request->input('status'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            reservation_device_info::create($data);

            return redirect()->route('reservation_info.index')->with('message', 'Reservation Created');
        } else {
            return redirect('noaccess');
        }
    }

    public function reservationStatusUpdate(Request $request, $id)
    {
        $data = reservation_device_info::find($id);
        $data->status = $request->input('status');
        // $data->update();
        if ($data->update()) {
            return response()->json(['s' => '1', 'data' => 'Updated.']);
        } else {
            return response()->json(['s' => '0', 'data' => 'Data Not Updated.']);
        }
    }

    public function getReservationFloorData($floorId)
    {
        $slots_ids = explode(',', Auth::user()->slots_ids);
        $floor_name = getFloorname($floorId);
        $floorData = reservation_device_info::where('floor_id', $floorId)->whereIn('id', $slots_ids)->get();
        // dd($floorData);
        $total = 0;
        $available = 0;
        $reserved = 0;
        $data = null;
        if ($floorData->isNotEmpty()) {
            foreach ($floorData as $f) {
                if ($f->status == 1 || $f->status == 2 || $f->status == 4) {
                    $reserved++;
                }
                if ($f->status == 0) {
                    $available++;
                }
            }
            $total = $available + $reserved;
            // $percentage = ($occupied / $total) * 100;
            // $percentage = $total > 0 ? ($occupied / $total) * 100 : 0;
            // $percentage = number_format($percentage, 2);
        } else {
            $data = [
                'data' => 0,
            ];
        }

        $data = [
            'floor_name' => $floor_name,
            'total' => $total,
            'available' => $available,
            'reserved' => $reserved,
            'data' => 1,
        ];

        return $data;
    }

    public function getReservationData()
    {
        $slots_ids = explode(',', Auth::user()->slots_ids);
        $site_id = Auth::user()->site_id;
        // $floor_name = getFloorname($floorId);
        $reservationData = reservation_device_info::where('site_id', $site_id)->whereIn('id', $slots_ids)->get()->groupBy('floor_id');
        $floorData = [];
        if ($reservationData->isNotEmpty()) {
            foreach ($reservationData as $key => $value) {
                $floor_name = getFloorname($key);
                $total = 0;
                $available = 0;
                $reserved = 0;
                foreach ($value as $f) {
                    if ($f->status == 1 || $f->status == 2 || $f->status == 4) {
                        $reserved++;
                    }
                    if ($f->status == 0) {
                        $available++;
                    }
                }
                $total = $available + $reserved;
                $percentage = ($reserved / $total) * 100;
                // dd($total);
                $floorData[] = [
                    'name' => $floor_name,
                    'total' => $total,
                    'available' => $available,
                    'reserved' => $reserved,
                    'percentage' => number_format($percentage, 2),
                ];
            }
            // here calculate total, available, reserved for all floors
            $total = 0;
            $available = 0;
            $reserved = 0;
            $percentage = 0;
            foreach ($floorData as $f) {
                $total += $f['total'];
                $available += $f['available'];
                $reserved += $f['reserved'];
            }
            if ($total > 0) {
                $percentage = ($reserved / $total) * 100;
            }
            $floorData[] = [
                'name' => getSitename($site_id),
                'total' => $total,
                'available' => $available,
                'reserved' => $reserved,
                'percentage' => number_format($percentage, 2),
            ];
            // reverese the array to have the latest floor data at the end
            $floorData = array_reverse($floorData);
            $count = count($floorData);


            $latestDateTime = reservation_device_info::where('site_id', $site_id)->latest('updated_at')->first();
            $timeAgo = "Last Update: ";
            $seconds = 0;
            if ($latestDateTime) {
                $updatedTime = $latestDateTime->updated_at;
                $currentTime = now();
                $diffInSeconds = $currentTime->diffInSeconds($updatedTime);
                $seconds = $diffInSeconds;

                if ($diffInSeconds >= 5) {
                    // Convert to a Carbon instance for human-readable output
                    $timeAgo = Carbon::now()->subSeconds($diffInSeconds)->diffForHumans([
                        'parts' => 1, // Show only the most significant unit
                    ]);

                    $timeAgo = "Last Update: " . $timeAgo;
                } else {
                    $timeAgo = "Last Update: Just now";
                }
            }
            $minutes = CarbonInterval::seconds($seconds)->totalMinutes;
            return response()->json(['data' => $floorData, 'lastUpdated' => $timeAgo, 'count' => $count, 'minutes' => $minutes]);
        } else {
            return response()->json(['data' => 0]);
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
            $editReservation = reservation_device_info::find($id);
            return view('slot.editslot_reservation', compact('editReservation'));
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if ($request->input('reservation_number_old') != $request->input('reservation_number')) {
            $checkData = reservation_device_info::where('reservation_number', $request->input('reservation_number'))->first();
            if ($checkData) {
                return redirect()->back()->withErrors(['error' => 'Reservation number should be unique.']);
            }
        }

        $data = [
            'site_id' => $request->input('site_id'),
            'floor_id' => $request->input('floor_id'),
            'zonal_id' => $request->input('zonal_id'),
            'reservation_number' => $request->input('reservation_number'),
            'reservation_name' => $request->input('reservation_name'),
            'status' => $request->input('status'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            $updateData = reservation_device_info::where('id', $id)->update($data);

            return redirect()->route('reservation_info.index')->with('message', 'Data Updated');
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

            reservation_device_info::where('id', $id)->delete();

            return redirect()->route('reservation_info.index')->with('message', 'Data Deleted');
        } else {
            return redirect('noaccess');
        }
    }
}
