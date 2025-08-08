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

class displayController extends Controller
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
            $displayData = display_info::all();
            return view('display.display', compact('siteData', 'displayData', 'can_edit'));
        }
        if ($role_id == 2) {
            $siteids = user_site_mapping::where('user_id', $id)->pluck('site_id')->implode(',');
            $array = explode(',', $siteids);
            $displayData = display_info::whereIn('site_id', $array)->get();
            return view('display.display', compact('siteData', 'displayData', 'can_edit'));
        }
        if ($role_id == 3) {
            return redirect()->route('login');
        }

        // $siteData = site_info::all();
        // $displayData = display_info::all();
        // return view('display.display', compact('siteData', 'displayData', 'can_edit'));
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
        // Validate the input, including checking if display_id is unique
        $request->validate([
            'display_no' => 'required|unique:display_infos,display_unique_no', // Check for unique display_no
        ]);

        $data = [
            'site_id' => $request->input('site_id'),
            'display_unique_no' => $request->input('display_no'),
            'location_of_the_display_on_site' => $request->input('display_location'),
            'intensity' => $request->input('intensity'),
            'panels' => $request->input('panels'),
        ];
        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            display_info::create($data);

            return redirect()->route('display.index')->with('message', 'Display Created');
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            $editDisplay = display_info::where('display_id', $id)->first();

            return view('display.editdisplay', compact('editDisplay'));
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if ($request->input('display_no') != $request->input('display_no_old')) {
            $checkData = display_info::where('display_unique_no', $request->input('display_no'))->first();
            if ($checkData) {
                return redirect()->back()->withErrors(['error' => 'Display No Already Exist!']);
            }
        }

        $data = [
            'display_unique_no' => $request->input('display_no'),
            'location_of_the_display_on_site' => $request->input('display_location'),
            'intensity' => $request->input('intensity'),
            'panels' => $request->input('panels'),
        ];
        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            $updateData = display_info::where('display_id', $id)->update($data);

            return redirect()->route('display.index')->with('message', 'Data Updated');
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
            displaydata::where('display_id', $id)->delete();
            display_info::where('display_id', $id)->delete();
            return redirect()->route('display.index')->with('message', 'Data Deleted');
        } else {
            return redirect('noaccess');
        }
    }
}
