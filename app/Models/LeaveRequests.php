<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequests extends Model
{
    use SoftDeletes;

    protected $table = 'leave_requests';

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'approver_id',
        'leave_request_ticket_id',
    ];

    public function leaveRequestTickets()
    {
        return $this->belongsTo(LeaveRequestTickets::class, 'leave_request_ticket_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function leaveTypes()
    {
        return $this->belongsTo(LeaveTypes::class, 'leave_type_id');
    }

    public function leaveBalance()
    {
        return $this->hasOne(LeaveBalances::class, 'user_id');
    }

    // public function getApprovalStatusAttribute()
    // {
    //     $hirarcy = [
    //         'HRD' => ['Manager'],
    //         'Manager' => ['HRD'],
    //         'Supervisor' => ['Manager', 'HRD'],
    //         'Staff' => ['Manager', 'Supervisor'],
    //     ];

    //     if ($this->status === 'APPROVED') {
    //         return 'Approved';
    //     } elseif ($this->status === 'REJECTED') {
    //         return 'Rejected';
    //     } elseif ($this->status === 'PENDING') {
    //         $requesterRole = $this->requester->role ?? null;
    //         $approverRole = $this->approver->role ?? null;

    //         if ($requesterRole && isset($hirarcy[$requesterRole])) {
    //             // Check if the current approver role is in the hierarchy for the requester role
    //             if (in_array($approverRole, $hirarcy[$requesterRole])) {
    //                 return "Waiting for {$approverRole} Approval";
    //             } else {
    //                 return 'Waiting for Approval';
    //             }
    //         }
    //     }

    //     $allApproved = true;
    //     foreach ($leaveRequest->approvers as $approver) {
    //         if ($approver->status !== 'APPROVED') {
    //             $allApproved = false;
    //             break;
    //         }
    //     }

    //     if ($this->status === 'PENDING') {
    //         return 'sedang meninjau permohonan cuti';
    //     } else if ($this->status === 'REJECTED') {
    //         return 'permohonan cuti ditolak oleh atasan';
    //     } else {
    //         return 'permohonan cuti disetujui oleh atasan';
    //     }

    //     return 'Unknown Status';
    // }

    public function canBeApprovedBy($user)
    {
        // Periksa apakah user divisi sesuai
        if ($this->requester->divisi_id !== $user->divisi_id) {
            return false;
        }

        // Periksa role yang diizinkan
        if ($this->status === 'PENDING') {
            if ($this->approver_id === $user->id && in_array($user->role, ['Manager', 'Supervisor'])) {
                return true;
            }
        }

        return false;
    }
}
