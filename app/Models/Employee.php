<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = [];
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function reportingManager()
    {
        return $this->belongsTo(Employee::class, 'reporting_manager_id');
    }

    public function directReports()
    {
        return $this->hasMany(Employee::class, 'reporting_manager_id');
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function appraisals()
    {
        return $this->hasMany(Appraisal::class);
    }

    public function salaryStructures()
    {
        return $this->hasMany(SalaryStructure::class);
    }

    public function currentSalaryStructure()
    {
        return $this->hasOne(SalaryStructure::class)->latestOfMany('effective_from');
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    public function pfAccount()
    {
        return $this->hasOne(PfAccount::class);
    }

    public function pfContributions()
    {
        return $this->hasMany(PfContribution::class);
    }

    public function gratuityRecord()
    {
        return $this->hasOne(GratuityRecord::class);
    }

    public function incentivePayouts()
    {
        return $this->hasMany(IncentivePayout::class);
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function wfhRequests()
    {
        return $this->hasMany(WfhRequest::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function history()
    {
        return $this->hasMany(EmployeeHistory::class);
    }
}
