<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\LeaveBalances;
use App\Models\LeaveTypes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function index()
    {
        $divisions = Divisi::get();
        return view('admin.users.index', ['divisions' => $divisions]);
    }

    public function getDataJSON(Request $request)
    {
        $query = User::with('division');

        // Filter berdasarkan pencarian (jika ada)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhereHas('division', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Paginate data
        $data = $query->paginate(5); // Ganti 5 dengan jumlah data per halaman

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'role' => 'required',
            'gender' => 'required',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|min:8|confirmed', // Password minimal 8 karakter, harus cocok dengan konfirmasi
            'password_confirmation' => 'required', // Konfirmasi password wajib diisi
            'divisi_id' => 'required',
            'status_karyawan' => 'required',
            'tgl_masuk' => 'required',
        ]);

        $latestEmployee = User::latest('id')->first();
        $newId = $latestEmployee ? $latestEmployee->id + 1 : 1;

        $npp = 'NP' . str_pad($newId, 6, '0', STR_PAD_LEFT);

        $data = User::create([
            'first_name' => $validatedData['first_name'],
            'middle_name' => $validatedData['middle_name'],
            'last_name' => $validatedData['last_name'],
            'role' => $validatedData['role'],
            'email' => $validatedData['email'],
            'npp' => $npp,
            'gender' => $validatedData['gender'],
            'password' => Hash::make($validatedData['password']),
            'divisi_id' => $validatedData['divisi_id'],
            'employee_status' => $validatedData['status_karyawan'],
            'join_date' => $validatedData['tgl_masuk'],
        ]);

        // Mendapatkan role berdasarkan nama
        $role = Role::firstOrCreate(['name' => $validatedData['role']]);

        // Assign role ke user
        $data->assignRole($role);

        $allPermissions = [
            'tambah_pengajuan_cuti',
            'detail_pengajuan_cuti',
            'approve_pengajuan_cuti',
            'reject_pengajuan_cuti',
            'tambah_divisi',
            'edit_divisi',
            'update_divisi',
            'destroy_divisi',
            'tambah_user',
            'edit_user',
            'update_user',
            'destroy_user',
            'tambah_program_cuti',
            'edit_program_cuti',
            'update_program_cuti',
            'destroy_program_cuti',
            'view_dashboard',
            'manage_users',
            'manage_division',
            'manage_program_cuti',
            'manage_pengajuan_cuti',
            'manage_pengajuan_izin',
        ];

        $rolePermissions = [
            'HRD' => $allPermissions,
            'Manager' => [
                'tambah_pengajuan_cuti',
                'detail_pengajuan_cuti',
                'approve_pengajuan_cuti',
                'reject_pengajuan_cuti',
                'view_dashboard',
            ],
            'Supervisor' => [
                'tambah_pengajuan_cuti',
                'detail_pengajuan_cuti',
                'approve_pengajuan_cuti',
                'view_dashboard',
            ],
            'Staff' => [
                'tambah_pengajuan_cuti',
                'detail_pengajuan_cuti',
                'view_dashboard',
            ],
        ];

        // Menambahkan permissions ke masing-masing role
        foreach ($rolePermissions as $roleName => $permissions) {
            // Buat role jika belum ada
            $role = Role::firstOrCreate(['name' => $roleName]);

            // Assign permissions ke role
            $role->givePermissionTo($permissions);
        }

        if (isset($rolePermissions[$validatedData['role']])) {
            $data->givePermissionTo($rolePermissions[$validatedData['role']]);
        }

        $leaveBalance = new LeaveBalances();
        $years = date('Y');

        $leaveTypes = LeaveTypes::where('years', $years)->get();

        if ($validatedData['gender'] === 'Perempuan') {
            // Perempuan mendapatkan "Cuti Tahunan" dan "Cuti Melahirkan"
            $selectedLeaveTypes = $leaveTypes->filter(function ($leaveType) {
                return in_array($leaveType->name, ['Cuti Tahunan', 'Cuti Melahirkan']);
            });
        } else {
            // Laki-laki hanya mendapatkan "Cuti Tahunan"
            $selectedLeaveTypes = $leaveTypes->filter(function ($leaveType) {
                return $leaveType->name === 'Cuti Tahunan';
            });
        }

        foreach ($selectedLeaveTypes as $leaveType) {
            $leaveBalance = new LeaveBalances();
            $leaveBalance->leave_type_id = $leaveType->id;
            $leaveBalance->years = $years;
            $leaveBalance->balance = $leaveType->max_days;
            $leaveBalance->user_id = $data->id; // Sesuaikan dengan kolom user_id
            $leaveBalance->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil Menambahkan User',
        ]);
    }

    public function edit(Request $request, $id)
    {
        $divisi = User::findOrFail($id);
        return response()->json($divisi);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'role' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'sometimes|nullable|min:8|confirmed',
            'password_confirmation' => 'sometimes|nullable',
            'divisi_id' => 'required',
            'status_karyawan' => 'required',
            'tgl_masuk' => 'required',
        ]);

        $user = User::findOrFail($id);

        // Perbarui data pengguna tanpa password terlebih dahulu
        $user->update(array_filter($validatedData, function ($value, $key) {
            return $key !== 'password' && $key !== 'password_confirmation';
        }, ARRAY_FILTER_USE_BOTH));

        // Periksa jika password diisi
        if (!empty($validatedData['password'])) {
            $user->update([
                'password' => bcrypt($validatedData['password']),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
        ]);
    }

    public function destroy($id)
    {
        $divisi = User::findOrFail($id);
        $divisi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus'
        ]);
    }
}
