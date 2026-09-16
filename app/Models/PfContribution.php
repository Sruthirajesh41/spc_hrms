<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PfContribution extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }
}
