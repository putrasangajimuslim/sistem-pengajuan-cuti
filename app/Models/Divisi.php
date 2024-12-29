<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Divisi extends Model
{
    use SoftDeletes;

    protected $table = 'divisions';

    protected $fillable = [
        'name',
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
