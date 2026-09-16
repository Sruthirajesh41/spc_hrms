-- Expansion dataset: DOB/gender backfill, holidays, announcements, WFH requests,
-- a support ticket, and a couple of notifications. Loaded by ExpandHrmsFeaturesSeeder
-- after the 2024_01_02 migration — safe to run on top of the original seed_data.sql
-- without touching any existing rows.

UPDATE `employees` SET `date_of_birth` = '1985-03-12', `gender` = 'male'   WHERE `id` = 1;
UPDATE `employees` SET `date_of_birth` = '1988-07-22', `gender` = 'female' WHERE `id` = 2;
UPDATE `employees` SET `date_of_birth` = '1987-11-05', `gender` = 'male'   WHERE `id` = 3;
UPDATE `employees` SET `date_of_birth` = '1990-01-30', `gender` = 'female' WHERE `id` = 4;
UPDATE `employees` SET `date_of_birth` = '1994-06-18', `gender` = 'male'   WHERE `id` = 5;
UPDATE `employees` SET `date_of_birth` = '1996-09-16', `gender` = 'female' WHERE `id` = 6;
UPDATE `employees` SET `date_of_birth` = '1993-12-25', `gender` = 'male'   WHERE `id` = 7;
UPDATE `employees` SET `date_of_birth` = '1995-04-14', `gender` = 'female' WHERE `id` = 8;
UPDATE `employees` SET `date_of_birth` = '1991-09-25', `gender` = 'male'   WHERE `id` = 9;
UPDATE `employees` SET `date_of_birth` = '1998-02-27', `gender` = 'female' WHERE `id` = 10;

-- Existing employee_documents rows: mark the seeded ones as already verified
-- (they were uploaded in prior years) rather than leaving them "pending".
UPDATE `employee_documents` SET `status` = 'verified', `verified_by` = 2, `verified_at` = `uploaded_at` WHERE `id` IN (1,2,3,4,5);

INSERT INTO `holidays` (`id`, `name`, `holiday_date`, `is_optional`, `created_at`, `updated_at`) VALUES
(1, 'Onam', '2026-09-14', 0, '2026-01-05 03:30:00', '2026-01-05 03:30:00'),
(2, 'Gandhi Jayanti', '2026-10-02', 0, '2026-01-05 03:30:00', '2026-01-05 03:30:00'),
(3, 'Diwali', '2026-11-08', 0, '2026-01-05 03:30:00', '2026-01-05 03:30:00'),
(4, 'Christmas', '2026-12-25', 0, '2026-01-05 03:30:00', '2026-01-05 03:30:00'),
(5, 'Karkidaka Vavu', '2026-09-30', 1, '2026-01-05 03:30:00', '2026-01-05 03:30:00'),
(6, 'New Year''s Day', '2027-01-01', 0, '2026-01-05 03:30:00', '2026-01-05 03:30:00');

INSERT INTO `announcements` (`id`, `title`, `body`, `audience_role`, `created_by`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 'Onam holiday schedule', 'The office will be closed on 14th September for Onam. Wishing everyone a happy and prosperous Onam!', 'all', 2, '2026-09-05 04:30:00', '2026-09-05 04:30:00', '2026-09-05 04:30:00'),
(2, 'Q3 appraisal cycle is now open', 'Self-assessments for the Q3 FY26 review cycle are due by 20th September. Please complete your goal ratings in the Performance module.', 'all', 2, '2026-09-02 05:00:00', '2026-09-02 05:00:00', '2026-09-02 05:00:00'),
(3, 'Updated WFH policy', 'Reporting managers now approve WFH requests directly — HR involvement is only required for requests longer than 5 consecutive days.', 'manager', 1, '2026-08-20 06:00:00', '2026-08-20 06:00:00', '2026-08-20 06:00:00');

INSERT INTO `announcement_reads` (`id`, `announcement_id`, `user_id`, `read_at`) VALUES
(1, 1, 5, '2026-09-05 07:00:00'),
(2, 1, 6, '2026-09-05 08:10:00'),
(3, 2, 3, '2026-09-02 06:30:00');

INSERT INTO `wfh_requests` (`id`, `employee_id`, `start_date`, `end_date`, `reason`, `location`, `contact_number`, `status`, `approved_by`, `approved_at`, `created_at`) VALUES
(1, 6, '2026-09-08', '2026-09-08', 'Waiting for a home appliance delivery', 'Kochi', '9847012306', 'approved', 3, '2026-09-07 05:00:00', '2026-09-07 03:30:00'),
(2, 9, '2026-09-12', '2026-09-12', 'Internet installation at new flat', 'Kochi', '9847012309', 'pending', NULL, NULL, '2026-09-09 04:30:00');

INSERT INTO `support_tickets` (`id`, `employee_id`, `category`, `subject`, `description`, `priority`, `status`, `resolution_note`, `resolved_by`, `resolved_at`, `created_at`) VALUES
(1, 8, 'payroll', 'PF number missing on latest payslip', 'My August payslip does not show my PF number under deductions. Could you check?', 'normal', 'resolved', 'PF number field was blank due to a data entry gap — corrected and payslip regenerated.', 2, '2026-09-04 06:00:00', '2026-09-03 04:15:00'),
(2, 7, 'other', 'Regularization still pending', 'My regularization request from 1st September is still showing pending after a week.', 'high', 'open', NULL, NULL, NULL, '2026-09-08 03:30:00');

INSERT INTO `notifications` (`id`, `user_id`, `type`, `message`, `link`, `read_at`, `created_at`) VALUES
(1, 8, 'ticket_resolved', 'Your support ticket "PF number missing on latest payslip" was resolved.', '/modules/support', '2026-09-04 07:00:00', '2026-09-04 06:00:00'),
(2, 5, 'leave_decided', 'Your leave request was approved.', '/modules/leave', NULL, '2026-08-08 06:30:00'),
(3, 6, 'wfh_decided', 'Your work-from-home request for 08 Sep was approved.', '/modules/wfh', NULL, '2026-09-07 05:00:00'),
(4, 3, 'wfh_submitted', 'Anjali Krishnan requested work-from-home for 08 Sep.', '/modules/wfh', NULL, '2026-09-07 03:30:00');

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(6, 'attendance_grace_minutes', '10', '2026-09-04 00:09:16'),
(7, 'wfh_max_days_per_month', '8', '2026-09-04 00:09:16');
