<?php

namespace App\Http\Controllers;

use App\Models\site_info;
use App\Models\table_entry;
use App\Models\table_info;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class tableController extends Controller
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
            $tableData = table_info::all();
            return view('table.table', compact('tableData', 'can_edit', 'siteData'));
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
        $checkData = table_info::where('site_id', $request->input('site_id'))->where('table_name', $request->input('table_name'))->get();
        $count = count($checkData);
        if ($count > 0) {
            return redirect()->back()->withErrors(['error' => 'Data Already Exist With Same Values!']);
        }

        $data = [
            'site_id' => $request->input('site_id'),
            'table_name' => $request->input('table_name'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {
            table_info::create($data);

            return redirect()->route('table.index')->with('message', 'Table Created');
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

            $editTable = table_info::where('table_id', $id)->first();
            $siteData = site_info::all();
            return view('table.edittable', compact('editTable', 'siteData'));
        } else {
            return redirect('noaccess');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if ($request->input('table_name') != $request->input('table_name_old')) {
            $checkData = table_info::where('site_id', $request->input('site_id'))->where('table_name', $request->input('table_name'))->get();
            if (count($checkData) > 0) {
                return redirect()->back()->withErrors(['error' => 'Data Already Exist With Same Values!']);
            }
        }
        $data = [
            'site_id' => $request->input('site_id'),
            'table_name' => $request->input('table_name'),
        ];

        $can_edit = Auth::user()->can_edit;

        if ($can_edit == 1) {

            $updateData = table_info::where('table_id', $id)->update($data);

            return redirect()->route('table.index')->with('message', 'Data Updated');
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
            table_entry::where('table_id', $id)->delete();
            table_info::where('table_id', $id)->delete();
            return redirect()->route('table.index')->with('message', 'Data Deleted');
        } else {
            return redirect('noaccess');
        }
    }
}
