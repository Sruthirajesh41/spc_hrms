<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncentiveRule extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function payouts()
    {
        return $this->hasMany(IncentivePayout::class);
    }
}
