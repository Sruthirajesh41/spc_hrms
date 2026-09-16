<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('code', 20);
            $table->timestamps();
        });

        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 150);
            $table->string('password');
            $table->enum('role', ['employee', 'manager', 'hr_admin', 'super_admin'])->default('employee');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('employee_code', 30);
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reporting_manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('date_of_joining');
            $table->date('date_of_exit')->nullable();
            $table->enum('employment_status', ['active', 'on_notice', 'exited'])->default('active');
            $table->string('phone', 20)->nullable();
            $table->string('personal_email', 150)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('bank_name', 120)->nullable();
            $table->string('bank_account_number', 40)->nullable();
            $table->string('bank_ifsc', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->boolean('is_paid')->default(true);
            $table->decimal('default_annual_days', 5, 2)->default(0);
            $table->boolean('carry_forward')->default(false);
            $table->decimal('max_carry_forward', 5, 2)->default(0);
        });

        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('opening_balance', 5, 2)->default(0);
            $table->decimal('accrued', 5, 2)->default(0);
            $table->decimal('used', 5, 2)->default(0);
            $table->decimal('carried_forward', 5, 2)->default(0);
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days', 5, 2);
            $table->string('reason', 255)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'on_leave'])->default('present');
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('early_exit_minutes')->default(0);
            $table->timestamps();
        });

        Schema::create('attendance_regularizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendance')->cascadeOnDelete();
            $table->dateTime('requested_check_in')->nullable();
            $table->dateTime('requested_check_out')->nullable();
            $table->string('reason', 255);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('appraisal_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('cycle_type', ['quarterly', 'half_yearly', 'annual']);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['upcoming', 'active', 'closed'])->default('upcoming');
        });

        Schema::create('appraisals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_cycle_id')->constrained('appraisal_cycles')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('self_assessment')->nullable();
            $table->text('manager_review')->nullable();
            $table->decimal('final_rating', 3, 2)->nullable();
            $table->enum('status', ['not_started', 'self_review', 'manager_review', 'completed'])->default('not_started');
            $table->timestamp('completed_at')->nullable();
        });

        Schema::create('appraisal_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_id')->constrained('appraisals')->cascadeOnDelete();
            $table->string('goal_text', 255);
            $table->unsignedTinyInteger('weight_percent')->default(0);
            $table->decimal('self_rating', 3, 2)->nullable();
            $table->decimal('manager_rating', 3, 2)->nullable();
        });

        Schema::create('job_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('openings')->default(1);
            $table->enum('status', ['open', 'on_hold', 'closed'])->default('open');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained('job_requisitions')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('email', 150);
            $table->string('phone', 20)->nullable();
            $table->string('resume_path', 255)->nullable();
            $table->string('source', 60)->nullable();
            $table->enum('stage', ['applied', 'shortlisted', 'interviewed', 'offered', 'hired', 'rejected'])->default('applied');
            $table->foreignId('converted_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('onboarding_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->string('item', 150);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
        });

        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->decimal('basic', 12, 2)->default(0);
            $table->decimal('hra', 12, 2)->default(0);
            $table->decimal('other_allowances', 12, 2)->default(0);
            $table->decimal('variable_pay', 12, 2)->default(0);
            $table->decimal('gross_monthly', 12, 2)->default(0);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->enum('status', ['draft', 'processed', 'paid'])->default('draft');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
        });

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('gross_pay', 12, 2)->default(0);
            $table->decimal('pf_deduction', 12, 2)->default(0);
            $table->decimal('esi_deduction', 12, 2)->default(0);
            $table->decimal('professional_tax', 12, 2)->default(0);
            $table->decimal('tds_deduction', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->timestamp('generated_at')->nullable();
        });

        Schema::create('pf_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('pf_number', 40)->nullable();
            $table->string('uan_number', 40)->nullable();
        });

        Schema::create('pf_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->decimal('employee_share', 12, 2)->default(0);
            $table->decimal('employer_share', 12, 2)->default(0);
        });

        Schema::create('gratuity_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('eligible_date')->nullable();
            $table->decimal('tenure_years', 4, 1)->default(0);
            $table->boolean('is_eligible')->default(false);
            $table->decimal('estimated_amount', 12, 2)->default(0);
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('incentive_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->enum('applies_to_role', ['employee', 'manager', 'hr_admin', 'super_admin'])->nullable();
            $table->string('target_metric', 100)->nullable();
            $table->decimal('slab_from', 14, 2)->nullable();
            $table->decimal('slab_to', 14, 2)->nullable();
            $table->decimal('incentive_percent', 5, 2)->nullable();
            $table->decimal('flat_amount', 12, 2)->nullable();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('incentive_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('incentive_rule_id')->nullable()->constrained('incentive_rules')->nullOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('achieved_value', 14, 2)->default(0);
            $table->decimal('incentive_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'included_in_payroll', 'paid'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('payroll_run_id')->nullable()->constrained('payroll_runs')->nullOnDelete();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100);
            $table->text('setting_value')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60);
            $table->string('module', 60);
            $table->unsignedBigInteger('record_id')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 60);
            $table->string('file_path', 255);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
        });

        Schema::create('employee_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('field_changed', 60);
            $table->string('old_value', 150)->nullable();
            $table->string('new_value', 150)->nullable();
            $table->date('effective_date');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_history');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('incentive_payouts');
        Schema::dropIfExists('incentive_rules');
        Schema::dropIfExists('gratuity_records');
        Schema::dropIfExists('pf_contributions');
        Schema::dropIfExists('pf_accounts');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('salary_structures');
        Schema::dropIfExists('onboarding_checklist_items');
        Schema::dropIfExists('candidates');
        Schema::dropIfExists('job_requisitions');
        Schema::dropIfExists('appraisal_goals');
        Schema::dropIfExists('appraisals');
        Schema::dropIfExists('appraisal_cycles');
        Schema::dropIfExists('attendance_regularizations');
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('users');
        Schema::dropIfExists('designations');
        Schema::dropIfExists('departments');
    }
};
