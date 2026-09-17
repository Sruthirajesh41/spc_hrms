<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Loads the SPC Universal sample dataset (10 users/employees across
     * HR, Sales and Operations, plus attendance, leave, payroll, PF,
     * appraisal, recruitment and incentive history) from seed_data.sql.
     *
     * The statements in that file are ordered so that parent rows
     * (departments, users, employees, ...) are always inserted before
     * rows that reference them via foreign key.
     */
    public function run(): void
    {
        $path = __DIR__.'/seed_data.sql';

        if (! file_exists($path)) {
            $this->command?->warn('seed_data.sql not found — skipping sample data.');

            return;
        }

        // Insert order in seed_data.sql already respects foreign keys (parents
        // before children), but make sure enforcement is on regardless of driver.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }

        DB::unprepared(file_get_contents($path));

        // The SQL dump carries opaque bcrypt hashes; normalize every seeded
        // account to the documented demo password so sign-in always works.
        \App\Models\User::query()->update(['password' => bcrypt('Password@123')]);

        // Sample data for WFH, Announcements, Support, Notifications, Holidays,
        // and the document-verification/birthday fields — safe to chain here
        // since it only inserts into the newer tables and backfills two columns,
        // it never re-touches the rows just inserted above.
        $this->call(ExpandHrmsFeaturesSeeder::class);
    }
}
