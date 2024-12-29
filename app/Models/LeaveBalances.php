<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveBalances extends Model
{
    use SoftDeletes;

    protected $table = 'leave_balances';

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'years',
        'balance',
    ];
}
