<?php

namespace App\Http\Controllers;

use App\Models\display_info;
use App\Models\symbol_info;
use App\Models\symbol_on_display;
use Illuminate\Http\Request;

class displaysymbolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $displaysymbolData = symbol_on_display::all();
        $displayData = display_info::all();
        $symbolData = symbol_info::all();
        return view('symbol.displaysymbol', compact('displaysymbolData', 'symbolData', 'displayData'));
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
            'display_id' => $request->input('display_id'),
            'symbol_to_show' => $request->input('symbol_id'),
            'coordinates' => $request->input('coordinates'),
            'color' => $request->input('color'),
        ];

        symbol_on_display::create($data);

        return redirect()->route('displaysymbol.index')->with('message', 'DisplaySymbol Created');
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
        $editDisplaySymbol = symbol_on_display::where('ondisplay_id', $id)->first();
        $symbolData = symbol_info::all();

        return view('symbol.displaysymbolupdate', compact('editDisplaySymbol', 'symbolData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());
        $data = [
            // 'display_id' => $request->input('display_id'),
            'symbol_to_show' => $request->input('symbol_id'),
            'coordinates' => $request->input('coordinates'),
            'color' => $request->input('color'),
        ];

        $updateData = symbol_on_display::where('ondisplay_id', $id)->update($data);
        return redirect()->route('displaysymbol.index')->with('message', 'Data Updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        symbol_on_display::where('ondisplay_id', $id)->delete();

        return redirect()->route('displaysymbol.index')->with('message', 'Data Deleted');
    }
}
