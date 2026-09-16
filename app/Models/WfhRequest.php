<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WfhRequest extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
