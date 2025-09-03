<?php

namespace App\Http\Controllers;

use App\Models\camera_info;
use App\Models\site_info;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class cameraController extends Controller
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
        if ($role_id != 3) {
            $cameraData = camera_info::all();
            return view('camera.camera', compact('cameraData', 'can_edit', 'siteData'));
        } else {
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
        $data = [
            'site_id' => $request->input('site_id'),
            'processor_id' => $request->input('processor_id'),
            'local_ip_address' => $request->input('local_ip_address'),
            'camera_access_link' => $request->input('camera_access_link'),
            'camera_identifier' => $request->input('camera_identifier'),
            'parking_slot_details' => $request->input('parking_slot_details'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            camera_info::create($data);

            return redirect()->route('camerainfo.index')->with('message', 'Camera Created');
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
        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            camera_info::destroy($id);

            return redirect()->route('camerainfo.index')->with('message', 'Data Deleted');
        } else {
            return redirect('noaccess');
        }
    }
}
