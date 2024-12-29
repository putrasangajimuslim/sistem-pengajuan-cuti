<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mendapatkan ID user terakhir untuk membuat NPP unik
        $latestEmployee = User::latest('id')->first();
        $newId = $latestEmployee ? $latestEmployee->id + 1 : 1;

        $npp = 'NP' . str_pad($newId, 6, '0', STR_PAD_LEFT);

        // Data user yang akan dibuat
        $dataUser = [
            "first_name" => 'Superadmin',
            "middle_name" => 'Superadmin',
            "last_name" => 'Superadmin',
            "npp" => $npp,
            "role" => 'HRD',
            "email" => 'superadmin@gmail.com',
            "password" => bcrypt('password'),
            "employee_status" => 'Tetap',
            "gender" => 'Perempuan',
            "divisi_id" => 1,
            "join_date" => now(),
            "created_at" => now(),
            "updated_at" => now(),
        ];

        // Membuat user baru
        $newUser = User::create($dataUser);

        // Mendapatkan role berdasarkan nama
        $role = Role::firstOrCreate(['name' => $dataUser['role']]);

        // Assign role ke user
        $newUser->assignRole($role);

        // Daftar semua permissions
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

        // Permissions untuk setiap role
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

        // Memberikan permissions ke user sesuai role mereka
        if (isset($rolePermissions[$dataUser['role']])) {
            $newUser->givePermissionTo($rolePermissions[$dataUser['role']]);
        }

        // Informasi
        echo "User dengan role '{$dataUser['role']}' berhasil dibuat dan diberikan permissions.";
    }
}
