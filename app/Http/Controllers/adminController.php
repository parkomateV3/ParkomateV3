<?php

namespace App\Http\Controllers;

use App\Models\AccessPoint;
use App\Models\reservation_device_info;
use App\Models\role_master;
use App\Models\site_info;
use App\Models\User;
use App\Models\user_site_mapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class adminController extends Controller
{
    public function index()
    {
        $role_id = Auth::user()->role_id;
        if ($role_id == 1) {
            $users = User::get();
            $sites = site_info::get();
            $roles = role_master::get();
            return view('admin.index', compact('users', 'sites', 'roles'));
        } else {
            return redirect('noaccess');
        }
    }

    public function show($id)
    {
        // $data = user_site_mapping::where('user_id', $id)->get();
        // $siteIds = $data->pluck('site_id')->toArray();
        // dd($siteIds); 
        $role_id = Auth::user()->role_id;
        $sites = site_info::all();

        if ($role_id == 1) {
            $user = User::find($id);
            if ($user->role_id == 1) {
                return view('admin.edit', compact('user', 'role_id'));
            }
            if ($user->role_id == 2) {
                $data = user_site_mapping::where('user_id', $id)->get();
                $siteIds = $data->pluck('site_id')->toArray();
                $siteids = implode(',', $siteIds);
                return view('admin.edit', compact('user', 'role_id', 'sites', 'siteIds', 'siteids'));
            }
            if ($user->role_id == 3) {
                $access = explode(',', $user->access);
                return view('admin.edit', compact('user', 'role_id', 'sites', 'access'));
            }
            // $sites = site_info::get();
            // $roles = role_master::all();
            // $userSites = user_site_mapping::all();
            // return view('admin.edit', compact('user', 'sites', 'roles', 'userSites'));
        } else {
            return redirect('noaccess');
        }
    }

    public function checkReservationSite(Request $request)
    {
        $siteData = site_info::where('site_id', $request->site_id)->first();
        if ($siteData) {
            $type = $siteData->site_type_of_product;

            return response()->json(['data' => $type]);
        }
        return response()->json(['data' => null]);
    }

    public function getSlotsData(Request $request)
    {
        $siteId = $request->site_id;

        // $reservationData = reservation_device_info::where('site_id', $siteId)->get()->groupBy('floor_id');
        $reservationData = reservation_device_info::where('site_id', $siteId)->get()->groupBy('floor_id');
        $slots = [];

        if ($reservationData->isNotEmpty()) {

            foreach ($reservationData as $floorId => $devices) {
                $floorName = getFloorname($floorId);
                $slots[$floorName] = [];

                foreach ($devices as $device) {
                    // $slots[$floorName][] = [
                    //     'device_id'           => $device->device_id,
                    //     'slot_number'         => $device->slot_number,
                    //     'status'              => $device->status,
                    //     'reservation_status'  => $device->reservation_status,
                    // ];
                    $slots[$floorName][] = $device;
                }
            }

            // proceed with $slots, e.g., return or view
        }

        // dd($slots);

        return response()->json($slots);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $role_id = Auth::user()->role_id;
        if ($role_id == 1) {
            if ($request->role == 1) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role_id' => $request->role,
                    'can_edit' => 1,
                    'status' => $request->status,
                    'access' => $request->access,
                ]);
            }

            if ($request->role == 2) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role_id' => $request->role,
                    'can_edit' => $request->canedit,
                    'status' => $request->status,
                    'access' => $request->access,
                ]);
                $sites = explode(',', $request->sites);
                foreach ($sites as $site) {
                    user_site_mapping::create([
                        'user_id' => $user->id,
                        'site_id' => $site,
                        'can_edit' => $request->canedit,
                    ]);
                }
            }

            if ($request->role == 3) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role_id' => $request->role,
                    'site_id' => $request->site,
                    'can_edit' => $request->canedit,
                    'status' => $request->status,
                    'access' => $request->access,
                    'slots_ids' => $request->values,
                    'slots_names' => $request->valueNames
                ]);
            }

            return redirect('admins')->with('message', 'User Created');
        } else {
            return redirect('noaccess');
        }


        // $id = Auth::id();
        // $access = AccessPoint::where('admin_id', $id)->first();
        // if ($access->superadmin == 1) {
        //     $user = User::create([
        //         'name' => $request->name,
        //         'email' => $request->email,
        //         'password' => Hash::make($request->password),
        //     ]);

        //     $access = AccessPoint::create([
        //         'admin_id' => $user->id,
        //         'superadmin' => 0,
        //         'site_id' => isset($request->sites) ? $request->sites : 0,
        //         'add_data' => isset($request->adddata) ? 1 : 0,
        //         'view_data' => isset($request->viewdata) ? 1 : 0,
        //         'edit_data' => isset($request->editdata) ? 1 : 0,
        //         'delete_data' => isset($request->deletedata) ? 1 : 0,
        //         'status' => $request->status,
        //     ]);

        //     return redirect('admins');
        // } else {
        //     return redirect('noaccess');
        // }
    }

    public function update(Request $request)
    {
        // dd($request->all());
        // echo $request->id;
        // exit;
        $role_id = Auth::user()->role_id;
        if ($role_id == 1) {
            if ($request->role == 1) {
                $data = [
                    'status' => $request->status,
                ];
                User::where('id', $request->id)->update($data);
            }
            if ($request->role == 2) {
                user_site_mapping::where('user_id', $request->id)->delete();
                $sites = explode(',', $request->sites);
                $data = [
                    'can_edit' => $request->canedit,
                ];
                User::where('id', $request->id)->update($data);
                foreach ($sites as $site) {
                    user_site_mapping::create([
                        'user_id' => $request->id,
                        'site_id' => $site,
                        'can_edit' => $request->canedit,
                        'status' => $request->status
                    ]);
                }
            }
            if ($request->role == 3) {
                $data = [
                    'site_id' => $request->site,
                    'can_edit' => $request->canedit,
                    'access' => $request->access,
                    'status' => $request->status,
                    'slots_ids' => $request->values,
                    'slots_names' => $request->valueNames
                ];
                User::where('id', $request->id)->update($data);
            }



            return redirect('admins');
        } else {
            return redirect('noaccess');
        }
    }

    public function changep($id)
    {
        $role_id = Auth::user()->role_id;
        if ($role_id == 1) {

            return view('admin.change-password', compact('id'));
        } else {
            return redirect('noaccess');
        }
    }

    public function changePassword(Request $request)
    {
        // dd($request->all());
        // exit;
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $role_id = Auth::user()->role_id;
        if ($role_id == 1) {

            $user = User::find($request->id);

            if ($user) {
                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            return redirect('admins');
        } else {
            return redirect('noaccess');
        }
    }

    public function getInfoData()
    {
        echo "dsdss";
    }
}
