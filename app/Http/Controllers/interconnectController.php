<?php

namespace App\Http\Controllers;

use App\Models\floor_interconnection;
use App\Models\site_info;
use App\Models\user_site_mapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class interconnectController extends Controller
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
            $interconnect = floor_interconnection::all();
            return view('floor.interconnect', compact('interconnect', 'siteData', 'can_edit'));
        }
        if ($role_id == 2) {
            $siteids = user_site_mapping::where('user_id', $id)->pluck('site_id')->implode(',');
            $array = explode(',', $siteids);
            $interconnect = floor_interconnection::whereIn('site_id', $array)->get();
            return view('floor.interconnect', compact('interconnect', 'siteData', 'can_edit'));
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
            'site_id' => 'required|unique:floor_interconnections,site_id',
        ]);

        $data = [
            'site_id' => $request->input('site_id'),
            'floor_info' => $request->input('floor_info'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            floor_interconnection::create($data);

            return redirect()->route('interconnect.index')->with('message', 'Floor Interconnection Created');
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

            $editInterconnect = floor_interconnection::where('floor_interconnection_id', $id)->first();
            $siteData = site_info::all();
            return view('floor.editinterconnect', compact('editInterconnect', 'siteData'));
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
            'floor_info' => $request->input('floor_info'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            $updateData = floor_interconnection::where('floor_interconnection_id', $id)->update($data);

            return redirect()->route('interconnect.index')->with('message', 'Data Updated');
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
            floor_interconnection::where('floor_interconnection_id', $id)->delete();

            return redirect()->route('interconnect.index')->with('message', 'Data Deleted');
        } else {
            return redirect('noaccess');
        }
    }
}
