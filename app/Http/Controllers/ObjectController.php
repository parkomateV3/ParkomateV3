<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Objects; // Eloquent model for objects table
use Illuminate\Support\Facades\Storage;

class ObjectController extends Controller
{
    public function index()
    {
        $objects = Objects::all();
        return view('objects.index', compact('objects'));
    }

    public function create()
    {
        return view('objects.create');
    }

    public function store(Request $request)
    {
        // 🔒 Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'obj_file' => 'required|file',
            'mtl_file' => 'nullable|file',
            'jpg_file' => 'nullable|file',
        ]);

        // 🧼 Clean file base name
        $baseName = preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($request->input('name')));
        $folderPath = public_path('vehicles');

        // 📁 Create directory if not exists
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0775, true);
        }

        // 📦 Save .obj file
        $objFile = $request->file('obj_file');
        $objFilename = $baseName . '.obj';
        $objFile->move($folderPath, $objFilename);
        $objPath = 'vehicles/' . $objFilename;

        // 📦 Save .mtl file
        $mtlPath = null;
        if ($request->hasFile('mtl_file')) {
            $mtlFile = $request->file('mtl_file');
            $mtlFilename = $baseName . '.mtl';
            $mtlFile->move($folderPath, $mtlFilename);
            $mtlPath = 'vehicles/' . $mtlFilename;
        }

        // 🖼 Save .jpg file
        $jpgPath = null;
        if ($request->hasFile('jpg_file')) {
            $jpgFile = $request->file('jpg_file');
            $jpgFilename = $baseName . '.jpg';
            $jpgFile->move($folderPath, $jpgFilename);
            $jpgPath = 'vehicles/' . $jpgFilename;
        }

        // 💾 Save to DB
        $object = new Objects();
        $object->name = $baseName;
        $object->obj_file = $objPath;
        $object->mtl_file = $mtlPath;
        $object->jpg_file = $jpgPath;
        $object->save();

        return redirect()->route('objects.index')->with('success', 'Object uploaded successfully!');
    }


    public function destroy($id)
    {
        $object = Objects::findOrFail($id);

        // Delete files if needed
        Storage::disk('public')->delete([$object->obj_file, $object->mtl_file, $object->jpg_file]);

        $object->delete();
        return redirect()->route('objects.index')->with('success', 'Object deleted!');
    }
}
