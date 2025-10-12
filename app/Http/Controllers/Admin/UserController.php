<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // <-- 1. Import User Model
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 1. ເລີ່ມສ້າງ Query Builder
        $query = User::query();

        // 2. ກວດສອບວ່າມີການຄົ້ນຫາ (Search) ຫຼືບໍ່
        if ($request->has('search') && $request->input('search') != '') {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }

        // 3. ກວດສອບວ່າມີການກັ່ນຕອງ (Filter) ຕາມ Role ຫຼືບໍ່
        if ($request->has('role_id') && $request->input('role_id') != '') {
            $query->where('role_id', $request->input('role_id'));
        }

        // 4. ດຶງຂໍ້ມູນ Roles ທັງໝົດມາເພື່ອສ້າງ Dropdown
        $roles = Role::orderBy('name')->get();

        // 5. ດຳເນີນການ Query ແລະ ແບ່ງໜ້າ
        $users = $query->with('role', 'department')->latest()->paginate(10)->withQueryString();

        // 6. ສົ່ງຂໍ້ມູນໄປທີ່ View
        return view('admin.users.index', compact('users', 'roles'));
        /*
        // 2. ດືງຂໍ້ມູນຜູ້ໃຊ້ທັງໝົດຈາກຖານຂໍ້ມູນ ພ້ອມກັບຂໍ້ມູນ Role ແລະ Department
        $users = User::with('role', 'department')->latest()->paginate(10);

        // 3. ສົ່ງຂໍ້ມູນຜູ້ໃຊ້ໄປຫາ View
        return view('admin.users.index', compact('users'));*/
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // 1. ດືງຂໍ້ມູນ Roles ແລະ Departments ທັງໝົດເພື່ອໄປສ້າງ Dropdown
        $roles = Role::all();
        $departments = Department::all();

        // 2. ສະແດງໜ້າຟອມ ພ້ອມກັບສົ່ງຂໍ້ມູນ roles ແລະ departments ໄປພ້ອມ
        return view('admin.users.create', compact('roles', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the incoming request data
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        // 2. Create the new user
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'department_id' => $request->department_id,
        ]);

        // 3. Redirect to the user index page with a success message
        return redirect()->route('admin.users.index')->with('success', 'ເພີ່ມຜູ້ໃຊ້ໃໝ່ສຳເລັດແລ້ວ.');
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
    public function edit(User $user) // <-- ປ່ຽນຈາກ string $id ເປັນ User $user
    {
        // 1. ດືງຂໍ້ມູນ Roles ແລະ Departments ທັງໝົດເພື່ອໄປສ້າງ Dropdown
        $roles = Role::all();
        $departments = Department::all();

        // 2. ສະແດງໜ໊າຟອມແກ້ໄຂ ພ້ອມກັບສະແດງຂໍ້ມູນ user, roles, ແລະ departments ໄປພ້ອມ
        return view('admin.users.edit', compact('user', 'roles', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // 1. Validate the incoming request data
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            // ກວດສອບອີເມວທີ່ບໍ່ຊ້ຳກັບຄົນອື່ນ, ຍົກເວັ້ນອີເມວຂອງຜູ້ໃຊ້ຄົນນີ້ເອງ
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            // Password ບໍ່ຈຳເປັນຕ້ອງໃສ່, ແຕ່ຖ້າໃສ່ຕ້ອງຖືກຕ້ອງຕາມກົດ
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        // 2. Update the user's main details
        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'department_id' => $request->department_id,
        ]);

        // 3. Update the password ONLY if a new one was provided
        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // 4. Redirect to the user index page with a success message
        return redirect()->route('admin.users.index')->with('success', 'ອັບເດດຂໍ້ມູນຜູ້ໃຊ້ສຳເລັດແລ້ວ.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // ປ້ອງກັນບໍ່ໃຫ້ລຶບບັນຊີ Admin ຫຼັກ (ສົມມຸດ id = 1)
        if ($user->id === 1) {
            return redirect()->route('admin.users.index')->with('error', 'ບໍ່ສາມາດລຶບບັນຊີຜູ້ບໍລິຫານຫຼັກໄດ້.');
        }

        // (Optional) ປ້ອງກັນບໍ່ໃຫ້ລຶບບັນຊີຕົວເອງ
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'ບໍ່ສາມາດລຶບບັນຊີຂອງຕົນເອງໄດ້.');
        }
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'ລຶບຜູ້ໃຊ້ສຳເລັດແລ້ວ.');
    }
}
