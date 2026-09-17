<?php

return [

    'roles' => [
        'employee' => [
            'label' => 'Employee',
            'tagline' => 'Self-service access to your own records',
            'accent' => '#0F8A5F',
            'kpis' => [
                ['label' => 'Attendance this month', 'value' => '21 / 22 days'],
                ['label' => 'Leave balance', 'value' => '8 days'],
                ['label' => 'Next payslip', 'value' => '30 Sep'],
                ['label' => 'Latest appraisal rating', 'value' => '4.2 / 5'],
            ],
        ],
        'manager' => [
            'label' => 'Reporting Manager',
            'tagline' => 'Oversight for your direct reports',
            'accent' => '#0E7490',
            'kpis' => [
                ['label' => 'Team attendance today', 'value' => '11 / 12 present'],
                ['label' => 'Pending leave approvals', 'value' => '3'],
                ['label' => 'Appraisals due', 'value' => '5 this cycle'],
                ['label' => 'Team incentive payout', 'value' => '₹1.4L this cycle'],
            ],
        ],
        'hr_admin' => [
            'label' => 'HR Admin',
            'tagline' => 'Configuration and processing across HR',
            'accent' => '#4D7C0F',
            'kpis' => [
                ['label' => 'Headcount', 'value' => '248'],
                ['label' => 'Payroll cost this month', 'value' => '₹42.6L'],
                ['label' => 'Open positions', 'value' => '9'],
                ['label' => 'Appraisal completion', 'value' => '76%'],
            ],
        ],
        'super_admin' => [
            'label' => 'Super Admin',
            'tagline' => 'Org-wide visibility and system control',
            'accent' => '#065F46',
            'kpis' => [
                ['label' => 'Headcount & attrition', 'value' => '248 · 2.1%'],
                ['label' => 'Attendance trend', 'value' => '94% avg'],
                ['label' => 'Payroll cost trend', 'value' => '+3.2% MoM'],
                ['label' => 'Audit log entries', 'value' => '1,204 (30d)'],
            ],
        ],
    ],

    'modules' => [

        'attendance' => [
            'code' => 'AT',
            'group' => 'Workforce',
            'nav_label' => 'Attendance',
            'title' => 'Attendance',
            'summary' => 'Check-in / check-out, timestamp capture, and regularization.',
            'roles' => ['employee', 'manager', 'hr_admin', 'super_admin'],
            'features' => [
                'Web-based check-in / check-out with timestamp capture.',
                'Daily, weekly, and monthly attendance summary per employee.',
                'Late arrival, early exit, and absence flagging.',
                'Regularization requests for missed punches, with manager approval.',
            ],
        ],

        'leave' => [
            'code' => 'LV',
            'group' => 'Workforce',
            'nav_label' => 'Leave Management',
            'title' => 'Leave Management',
            'summary' => 'Leave types, balances, approvals, and the team calendar.',
            'roles' => ['employee', 'manager', 'hr_admin', 'super_admin'],
            'features' => [
                'Configurable leave types (casual, sick, earned, unpaid, etc.) with balance tracking.',
                'Leave application, approval / rejection workflow, and status notifications.',
                'Leave calendar view for managers and HR.',
                'Automatic accrual and carry-forward rules.',
            ],
        ],

        'payroll' => [
            'code' => 'PR',
            'group' => 'Money',
            'nav_label' => 'Payroll',
            'title' => 'Payroll',
            'summary' => 'Salary structure, monthly runs, and payslip access.',
            'roles' => ['employee', 'hr_admin', 'super_admin'],
            'features' => [
                'Salary structure setup per employee (fixed, variable, deductions).',
                'Automated monthly payroll run based on attendance and leave data.',
                'Payslip generation and employee self-service access to payslips.',
                'Integration hooks for statutory deductions (PF, ESI, professional tax, TDS).',
            ],
        ],

        'recruitment' => [
            'code' => 'RC',
            'group' => 'Growth',
            'nav_label' => 'Recruitment',
            'title' => 'Recruitment & Onboarding',
            'summary' => 'Requisitions, candidate pipeline, and onboarding checklists.',
            'roles' => ['hr_admin', 'super_admin'],
            'features' => [
                'Job requisition and posting tracker.',
                'Candidate pipeline: applied, shortlisted, interviewed, offered, hired.',
                'Digital onboarding checklist and document collection for new joiners.',
                'Conversion of selected candidate records into employee profiles.',
            ],
        ],

        'my-profile' => [
            'code' => 'ME',
            'group' => 'Overview',
            'nav_label' => 'My Profile',
            'title' => 'My Profile',
            'summary' => 'Your own personal details, bank info, documents and password.',
            'roles' => ['employee', 'manager', 'hr_admin', 'super_admin'],
            'features' => [
                'Personal contact details, bank & statutory info.',
                'Upload and track your own documents.',
                'Change your sign-in password.',
            ],
        ],

        'employee-records' => [
            'code' => 'ER',
            'group' => 'People',
            'nav_label' => 'Employees',
            'title' => 'Employee Records',
            'summary' => 'Master data, documents, and employment history.',
            'roles' => ['hr_admin', 'super_admin'],
            'features' => [
                'Centralized employee master data: personal, employment, bank, and document details.',
                'Document upload and storage (ID proofs, certificates, contracts).',
                'Employment history — designation, department, and location changes.',
                'Searchable, filterable employee directory for HR and admin.',
            ],
        ],

        'appraisal' => [
            'code' => 'PA',
            'group' => 'Growth',
            'nav_label' => 'Performance',
            'title' => 'Performance Appraisal',
            'summary' => 'Appraisal cycles, self-assessment, and manager review.',
            'roles' => ['employee', 'manager', 'hr_admin', 'super_admin'],
            'features' => [
                'Configurable appraisal cycles (quarterly / half-yearly / annual).',
                'Goal-setting, self-assessment, and manager review workflow.',
                'Consolidated rating and appraisal history per employee.',
                'Exportable appraisal reports for management review.',
            ],
        ],

        'pf-gratuity' => [
            'code' => 'PF',
            'group' => 'Money',
            'nav_label' => 'PF & Gratuity',
            'title' => 'PF & Gratuity',
            'summary' => 'Contribution tracking, eligibility, and statements.',
            'roles' => ['employee', 'hr_admin', 'super_admin'],
            'features' => [
                'PF contribution tracking (employee and employer share) per payroll cycle.',
                'Gratuity eligibility tracking based on tenure.',
                'Statement generation for PF and gratuity balances.',
                'Exportable reports for statutory filing support.',
            ],
        ],

        'incentive' => [
            'code' => 'CI',
            'group' => 'Money',
            'nav_label' => 'Commission & Incentive',
            'title' => 'Commission & Incentive',
            'summary' => 'Configurable rules, calculations, and payout approval.',
            'roles' => ['employee', 'manager', 'hr_admin', 'super_admin'],
            'features' => [
                'Configurable commission / incentive rules by role, target, or slab.',
                'Automated calculation based on sales / performance data (integrates with Sales Order Management).',
                'Incentive payout summary per employee, per cycle.',
                'Approval workflow before payout is included in payroll.',
            ],
        ],

        'reports' => [
            'code' => 'RD',
            'group' => 'Records',
            'nav_label' => 'Reports',
            'title' => 'Reports & Dashboards',
            'summary' => 'Workforce, payroll, recruitment, and appraisal reporting.',
            'roles' => ['hr_admin', 'super_admin'],
            'features' => [
                'Headcount and department-wise workforce distribution.',
                'Attendance and leave trend reports.',
                'Payroll cost summary by department / month.',
                'Recruitment funnel and time-to-hire report.',
                'Appraisal completion and rating distribution.',
                'Commission / incentive payout summary.',
            ],
        ],

        'system' => [
            'code' => 'SA',
            'group' => 'System',
            'nav_label' => 'System & Audit Log',
            'title' => 'System & Access',
            'summary' => 'Roles, permissions, configuration, and audit logs.',
            'roles' => ['super_admin'],
            'features' => [
                'Manage role-based access and user permissions across the system.',
                'Organization-wide dashboards: headcount, attrition, attendance trends, payroll cost.',
                'System configuration, audit logs, and data export.',
            ],
        ],

        'wfh' => [
            'code' => 'WF',
            'group' => 'Workforce',
            'nav_label' => 'WFH Management',
            'title' => 'Work From Home',
            'summary' => 'WFH requests, approvals, and calendar.',
            'roles' => ['employee', 'manager', 'hr_admin', 'super_admin'],
            'features' => [
                'WFH request with date range, reason, location, and contact number.',
                'Manager / HR approval workflow, same shape as leave.',
                'Approved WFH days are visible alongside attendance and leave records.',
            ],
        ],

        'announcements' => [
            'code' => 'AN',
            'group' => 'Comms',
            'nav_label' => 'Announcements',
            'title' => 'Announcements',
            'summary' => 'Company-wide or role-targeted communication.',
            'roles' => ['employee', 'manager', 'hr_admin', 'super_admin'],
            'features' => [
                'Publish announcements to everyone or to a specific role.',
                'Read / unread tracking per person.',
                'Feeds the notification bell and the employee dashboard.',
            ],
        ],

        'support' => [
            'code' => 'HS',
            'group' => 'Comms',
            'nav_label' => 'Help & Support',
            'title' => 'Help & Support',
            'summary' => 'Central request tracker for HR, IT, and payroll queries.',
            'roles' => ['employee', 'manager', 'hr_admin', 'super_admin'],
            'features' => [
                'Raise a ticket by category (IT, HR, Payroll, Facilities, Documents, Other).',
                'HR Admin / Super Admin triage, assign, and resolve tickets.',
                'Doubles as "My Requests" — every ticket you\'ve raised, in one place.',
            ],
        ],

        'settings' => [
            'code' => 'CF',
            'group' => 'System',
            'nav_label' => 'Settings',
            'title' => 'Settings',
            'summary' => 'Company profile and policy configuration.',
            'roles' => ['hr_admin', 'super_admin'],
            'features' => [
                'Company profile, PF/ESI rates, leave year start, payroll cutoff day.',
                'Every change is written to the audit log.',
            ],
        ],

        'organization' => [
            'code' => 'OG',
            'group' => 'Records',
            'nav_label' => 'Organization',
            'title' => 'Organization',
            'summary' => 'Departments, designations, and the company holiday calendar.',
            'roles' => ['hr_admin', 'super_admin'],
            'features' => [
                'Add and rename departments and designations without touching seed data.',
                'Maintain the company holiday calendar — feeds the dashboard and WFH/leave context.',
                'Headcount per department / designation at a glance.',
            ],
        ],

    ],

];
