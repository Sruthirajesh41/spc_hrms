<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncentivePayout extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function rule()
    {
        return $this->belongsTo(IncentiveRule::class, 'incentive_rule_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }
}
