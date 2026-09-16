<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppraisalGoal extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function appraisal()
    {
        return $this->belongsTo(Appraisal::class);
    }
}
