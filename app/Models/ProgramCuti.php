<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramCuti extends Model
{
    use SoftDeletes;

    protected $table = 'leave_types';

    protected $fillable = [
        'name',
        'max_days',
        'years',
    ];

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at ? $this->created_at->format('d F Y H:i') : null;
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('d F Y H:i') : null;
    }

    protected $appends = [
        'formatted_created_at',
        'formatted_updated_at',
    ];
}
