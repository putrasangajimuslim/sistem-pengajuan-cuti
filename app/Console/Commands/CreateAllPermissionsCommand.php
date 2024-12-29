<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateAllPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:createAll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create permissions and assign them to a role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $allRoles = [
            'Manager',
            'Supervisor',
            'Staff',
            'HRD',
        ];

        // Membuat roles
        foreach ($allRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Daftar permission
        $allPermissions = [
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
            'tambah_pengajuan_cuti',
            'detail_pengajuan_cuti',
            'approve_pengajuan_cuti',
            'reject_pengajuan_cuti',
            'tambah_pengajuan_izin',
            'edit_pengajuan_izin',
            'update_pengajuan_izin',
            'destroy_pengajuan_izin',
            'detail_pengajuan_izin',
            'approve_pengajuan_izin',
            'reject_pengajuan_izin',
            'view_dashboard',
            'manage_users',
            'manage_division',
            'manage_program_cuti',
            'manage_pengajuan_cuti',
            'manage_pengajuan_izin',
            'view_laporan',
            'list_laporan',
        ];

        // Membuat permissions
        foreach ($allPermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Menentukan permissions untuk masing-masing role
        $rolePermissions = [
            'HRD' => $allPermissions,
            'Manager' => [
                'tambah_pengajuan_cuti',
                'detail_pengajuan_cuti',
                'approve_pengajuan_cuti',
                'reject_pengajuan_cuti',
                'tambah_pengajuan_izin',
                'detail_pengajuan_izin',
                'approve_pengajuan_izin',
                'reject_pengajuan_izin',
                'view_dashboard',
            ],
            'Supervisor' => [
                'tambah_pengajuan_cuti',
                'detail_pengajuan_cuti',
                'approve_pengajuan_cuti',
                'reject_pengajuan_cuti',
                'tambah_pengajuan_izin',
                'detail_pengajuan_izin',
                'approve_pengajuan_izin',
                'reject_pengajuan_izin',
                'view_dashboard',
            ],
            'Staff' => [
                'tambah_pengajuan_cuti',
                'detail_pengajuan_cuti',
                'tambah_pengajuan_izin',
                'detail_pengajuan_izin',
                'view_dashboard',
            ],
        ];

        // Memberikan permissions ke setiap role
        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::findByName($roleName);
            if ($role) {
                $role->givePermissionTo($permissions);
                $this->info("Permissions assigned to the '{$roleName}' role.");
            } else {
                $this->error("Role '{$roleName}' not found.");
            }
        }
    }
}
