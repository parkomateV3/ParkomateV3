<?php

namespace App\Http\Controllers;

use App\Models\symbol_info;
use App\Models\symbol_on_display;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class symbolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $symbolData = symbol_info::all();
        return view('symbol.symbol', compact('symbolData'));
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
            'symbol_name' => $request->input('symbol_name'),
            'binary_data' => $request->input('binary_data'),
            'symbol_size' => $request->input('symbol_size'),
        ];

        if ($request->hasFile('symbol_img')) {
            $symbolname = $request->file('symbol_img')->getClientOriginalName();
            $request->file('symbol_img')->move(public_path('symbols'), $symbolname);
            $data['symbol_img'] = $symbolname; // Add file path to data array
        } else {
            $data['symbol_img'] = null;
        }

        symbol_info::create($data);

        return redirect()->route('symbol.index')->with('message', 'Symbol Created');
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
        // dd($id);
        $editSymbol = symbol_info::where('symbol_id', $id)->first();

        return view('symbol.symbolupdate', compact('editSymbol'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = [
            'symbol_name' => $request->input('symbol_name'),
            'binary_data' => $request->input('binary_data'),
            'symbol_size' => $request->input('symbol_size'),
        ];

        if ($request->hasFile('symbol_img')) {
            // Delete the old symbol_img file
            $oldsymbolPath = public_path('symbols/' . $request->input('old_symbol_img'));
            if (File::exists($oldsymbolPath)) {
                File::delete($oldsymbolPath);
            }
            $symbolname = $request->file('symbol_img')->getClientOriginalName();
            $request->file('symbol_img')->move(public_path('symbols'), $symbolname);
            $data['symbol_img'] = $symbolname; // Add file path to data array
        } else {
            $data['symbol_img'] = $request->input('old_symbol_img');
        }

        $updateData = symbol_info::where('symbol_id', $id)->update($data);

        return redirect()->route('symbol.index')->with('message', 'Data Updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        symbol_on_display::where('symbol_to_show', $id)->delete();
        symbol_info::where('symbol_id', $id)->delete();

        return redirect()->route('symbol.index')->with('message', 'Data Deleted');
    }

}
