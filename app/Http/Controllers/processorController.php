<?php

namespace App\Http\Controllers;

use App\Models\camera_info;
use App\Models\processor_info;
use App\Models\site_info;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class processorController extends Controller
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
            $processorData = processor_info::all();
            return view('processor.processor', compact('processorData', 'can_edit', 'siteData'));
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
        $request->validate([
            'processor_id' => 'required|unique:processor_infos,processor_id',
        ]);

        $data = [
            'site_id' => $request->input('site_id'),
            'processor_id' => $request->input('processor_id'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            processor_info::create($data);

            return redirect()->route('processor.index')->with('message', 'Processor Created');
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
            $data = processor_info::where('site_id', $id)->get();
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
            camera_info::where('processor_id', $id)->delete();
            processor_info::destroy($id);

            return redirect()->route('processor.index')->with('message', 'Data Deleted');
        } else {
            return redirect('noaccess');
        }
    }
}
