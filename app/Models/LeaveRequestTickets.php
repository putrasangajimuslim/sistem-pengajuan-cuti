<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequestTickets extends Model
{
    use SoftDeletes;

    protected $table = 'leave_request_tickets';

    protected $fillable = [
        'no_ticket',
        'start_date',
        'end_date',
        'years',
        'total_days',
        'status',
    ];

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequests::class, 'leave_request_ticket_id');
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at ? $this->created_at->format('d F Y H:i') : null;
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('d F Y H:i') : null;
    }

    public function getUserRoleAttribute()
    {
        return auth()->check() ? auth()->user()->role : null;
    }

    public function getApprovalStatusAttribute()
    {
        if ($this->status === 'PENDING') {
            return 'Pending';
        } else if ($this->status === 'REJECTED') {
            return 'Ditolak';
        } else {
            return 'Disetujui';
        }

        return 'Unknown Status';
    }

    protected $appends = [
        'formatted_created_at',
        'formatted_updated_at',
        'user_role',
    ];
}
