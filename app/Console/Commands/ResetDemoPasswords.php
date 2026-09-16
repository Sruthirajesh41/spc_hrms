<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetDemoPasswords extends Command
{
    protected $signature = 'users:reset-passwords';

    protected $description = 'Set every seeded user\'s password to the shared demo password (Password@123). Useful if your database was seeded before login was added.';

    public function handle(): int
    {
        $count = User::query()->update(['password' => Hash::make('Password@123')]);

        $this->info("Updated password for {$count} user(s) to: Password@123");

        return self::SUCCESS;
    }
}
