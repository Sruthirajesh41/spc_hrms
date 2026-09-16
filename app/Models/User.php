<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $guarded = [];
    public $timestamps = true;

    protected $hidden = ['password'];

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->whereNull('read_at')->count();
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'employee' => 'Employee',
            'manager' => 'Reporting Manager',
            'hr_admin' => 'HR Admin',
            'super_admin' => 'Super Admin',
            default => ucfirst($this->role),
        };
    }
}
