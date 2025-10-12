<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    
    /**
     * ສະແດງລາຍການ Departments ທັງໝົດ ແລະ ຟອມສຳລັບສ້າງອັນໃໝ່.
     */
    public function index()
    {
        //$departments = Department::orderBy('name')->get(); // <-- ປ່ຽນຊື່ຕົວແປ
        $departments = Department::orderBy('id', 'asc')->get();
        return view('admin.departments.index', compact('departments')); // <-- ປ່ຽນ path ຂອງ view
    }

    /**
     * ບັນທຶກ Department ໃໝ່.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name', // <-- ປ່ຽນຊື່ຕາຕະລາງ
        ]);

        Department::create($request->only('name')); // <-- ປ່ຽນ Model

        return redirect()->route('admin.departments.index')->with('success', 'ເພີ່ມພາກວິຊາ/ພະແນກໃໝ່ສຳເລັດແລ້ວ.'); // <-- ປ່ຽນ route ແລະ ຂໍ້ຄວາມ
    }

    /**
     * ລຶບ Department.
     */
    public function destroy(Department $department) // <-- ປ່ຽນ Type Hint
    {
        // (Optional) ກວດສອບວ່າ Department ນີ້ມີຜູ້ໃຊ້ຜູກຢູ່ບໍ່ກ່ອນທີ່ຈະລຶບ
        if ($department->users()->count() > 0) {
            return back()->with('error', 'ບໍ່ສາມາດລຶບພາກວິຊາ/ພະແນກນີ້ໄດ້ ເພາະມີຜູ້ໃຊ້ຜູກຢູ່.');
        }

        $department->delete();

        return redirect()->route('admin.departments.index')->with('success', 'ລຶບພາກວິຊາ/ພະແນກສຳເລັດແລ້ວ.'); // <-- ປ່ຽນ route ແລະ ຂໍ້ຄວາມ
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        //
    }
}
