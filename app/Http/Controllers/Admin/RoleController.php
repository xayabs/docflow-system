<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //$roles = Role::orderBy('name')->get();
        $roles = Role::orderBy('id', 'asc')->get();
        return view('admin.roles.index', compact('roles'));
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
            'name' => 'required|string|max:50|unique:roles,name',
        ]);

        Role::create($request->only('name'));

        return redirect()->route('admin.roles.index')->with('success', 'ເພີ່ມບັດບາດໃໝ່ສໍາເລັດ.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // (Optional) ອາດຈະຕ້ອງເພີ່ມ Logic ກວດສອບວ່າ Role ນີ້ມີຜູ້ໃຊ້ພົວພັນຢູ່ຫຼືບໍ່ກ່ອນທີ່ຈະລຶບ
        if ($role->users()->count() > 0) {
             return back()->with('error', 'ບໍ່ສາມາດລຶບບົດບາດນີ້ໄດ້ ເນື່ອງຈາກມີຜູ້ໃຊ້ພົວພັນຢູ່.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'ລຶບບົດບາດສໍາເລັດແລ້ວ.');
    }
}
