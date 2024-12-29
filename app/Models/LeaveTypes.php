<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveTypes extends Model
{
    use SoftDeletes;

    protected $table = 'leave_types';

    protected $fillable = [
        'name',
        'max_days',
        'years',
    ];
}
