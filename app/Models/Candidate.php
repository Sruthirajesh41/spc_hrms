<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $guarded = [];
    public $timestamps = true;

    public function requisition()
    {
        return $this->belongsTo(JobRequisition::class, 'requisition_id');
    }

    public function checklistItems()
    {
        return $this->hasMany(OnboardingChecklistItem::class);
    }

    public function convertedEmployee()
    {
        return $this->belongsTo(Employee::class, 'converted_employee_id');
    }
}
