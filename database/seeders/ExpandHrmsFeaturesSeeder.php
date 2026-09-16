<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpandHrmsFeaturesSeeder extends Seeder
{
    /**
     * Adds sample data for the features introduced by the
     * 2024_01_02_000000_expand_hrms_features migration: birthdays/gender
     * backfill, holidays, announcements, WFH requests, a support ticket and
     * a few notifications. Safe to run once on top of the original
     * DatabaseSeeder — it only updates existing rows and inserts new ones
     * into the new tables, it never re-inserts the original dataset.
     */
    public function run(): void
    {
        $path = __DIR__.'/seed_data_v2.sql';

        if (! file_exists($path)) {
            $this->command?->warn('seed_data_v2.sql not found — skipping expansion sample data.');

            return;
        }

        DB::unprepared(file_get_contents($path));
    }
}
