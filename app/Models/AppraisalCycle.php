<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppraisalCycle extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function appraisals()
    {
        return $this->hasMany(Appraisal::class);
    }
}
