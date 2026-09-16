<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AppraisalController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeRecordsController;
use App\Http\Controllers\IncentiveController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PfGratuityController;
use App\Http\Controllers\RecruitmentController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\WfhController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'show'])->name('login.show');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['hr.auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Attendance
    Route::get('/modules/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/modules/attendance/regularize', [AttendanceController::class, 'regularize'])->name('attendance.regularize');
    Route::post('/modules/attendance/regularize/{regularization}/decide', [AttendanceController::class, 'decide'])->name('attendance.decide');

    // Leave
    Route::get('/modules/leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::post('/modules/leave/apply', [LeaveController::class, 'apply'])->name('leave.apply');
    Route::post('/modules/leave/{leaveRequest}/decide', [LeaveController::class, 'decide'])->name('leave.decide');

    // Payroll
    Route::get('/modules/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/modules/payroll/{employee}/salary', [PayrollController::class, 'updateSalary'])->name('payroll.salary.update');
    Route::get('/modules/payroll/payslip/{payslip}', [PayrollController::class, 'payslip'])->name('payroll.payslip');

    // Recruitment
    Route::get('/modules/recruitment', [RecruitmentController::class, 'index'])->name('recruitment.index');
    Route::post('/modules/recruitment/requisitions', [RecruitmentController::class, 'storeRequisition'])->name('recruitment.requisition.store');
    Route::post('/modules/recruitment/candidates/{candidate}/stage', [RecruitmentController::class, 'updateCandidateStage'])->name('recruitment.candidate.stage');
    Route::post('/modules/recruitment/checklist/{item}/toggle', [RecruitmentController::class, 'toggleChecklistItem'])->name('recruitment.checklist.toggle');

    // Employee records
    Route::get('/modules/employee-records', [EmployeeRecordsController::class, 'index'])->name('records.index');
    Route::post('/modules/employee-records', [EmployeeRecordsController::class, 'store'])->name('records.store');
    Route::get('/my-profile', [EmployeeRecordsController::class, 'myProfile'])->name('profile.index');
    Route::post('/modules/employee-records/{employee}', [EmployeeRecordsController::class, 'update'])->name('records.update');
    Route::post('/modules/employee-records/{employee}/status', [EmployeeRecordsController::class, 'toggleStatus'])->name('records.status');
    Route::post('/modules/employee-records/{employee}/password', [EmployeeRecordsController::class, 'updatePassword'])->name('records.password.update');
    Route::post('/modules/employee-records/{employee}/documents', [EmployeeRecordsController::class, 'uploadDocument'])->name('records.document.upload');
    Route::post('/modules/employee-records/documents/{document}/verify', [EmployeeRecordsController::class, 'verifyDocument'])->name('records.document.verify');
    Route::get('/modules/employee-records/documents/{document}/download', [EmployeeRecordsController::class, 'downloadDocument'])->name('records.document.download');

    // Appraisal
    Route::get('/modules/appraisal', [AppraisalController::class, 'index'])->name('appraisal.index');
    Route::post('/modules/appraisal/{appraisal}/self', [AppraisalController::class, 'submitSelfAssessment'])->name('appraisal.self');
    Route::post('/modules/appraisal/{appraisal}/review', [AppraisalController::class, 'submitManagerReview'])->name('appraisal.review');

    // PF & Gratuity
    Route::get('/modules/pf-gratuity', [PfGratuityController::class, 'index'])->name('pf.index');

    // Incentive
    Route::get('/modules/incentive', [IncentiveController::class, 'index'])->name('incentive.index');
    Route::post('/modules/incentive/rules', [IncentiveController::class, 'storeRule'])->name('incentive.rule.store');
    Route::post('/modules/incentive/payouts/{payout}/approve', [IncentiveController::class, 'approvePayout'])->name('incentive.payout.approve');

    // Reports
    Route::get('/modules/reports', [ReportsController::class, 'index'])->name('reports.index');

    // System & Access
    Route::get('/modules/system', [SystemController::class, 'index'])->name('system.index');
    Route::post('/modules/system/users', [SystemController::class, 'storeUser'])->name('system.user.store');
    Route::post('/modules/system/users/{user}', [SystemController::class, 'updateUser'])->name('system.user.update');

    // Work From Home
    Route::get('/modules/wfh', [WfhController::class, 'index'])->name('wfh.index');
    Route::post('/modules/wfh', [WfhController::class, 'store'])->name('wfh.store');
    Route::post('/modules/wfh/{wfhRequest}/decide', [WfhController::class, 'decide'])->name('wfh.decide');

    // Announcements
    Route::get('/modules/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/modules/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');

    // Help & Support
    Route::get('/modules/support', [SupportController::class, 'index'])->name('support.index');
    Route::post('/modules/support', [SupportController::class, 'store'])->name('support.store');
    Route::post('/modules/support/{ticket}', [SupportController::class, 'update'])->name('support.update');

    // Settings
    Route::get('/modules/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/modules/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Organization (Departments, Designations, Holidays)
    Route::get('/modules/organization', [OrganizationController::class, 'index'])->name('organization.index');
    Route::post('/modules/organization/departments', [OrganizationController::class, 'storeDepartment'])->name('organization.department.store');
    Route::post('/modules/organization/departments/{department}', [OrganizationController::class, 'updateDepartment'])->name('organization.department.update');
    Route::post('/modules/organization/designations', [OrganizationController::class, 'storeDesignation'])->name('organization.designation.store');
    Route::post('/modules/organization/designations/{designation}', [OrganizationController::class, 'updateDesignation'])->name('organization.designation.update');
    Route::post('/modules/organization/holidays', [OrganizationController::class, 'storeHoliday'])->name('organization.holiday.store');
    Route::post('/modules/organization/holidays/{holiday}/delete', [OrganizationController::class, 'destroyHoliday'])->name('organization.holiday.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});
