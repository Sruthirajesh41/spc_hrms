<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Config;

abstract class Controller
{
    protected ?User $currentUser = null;

    protected ?Employee $currentEmployee = null;

    /**
     * The currently "signed in" demo user, resolved from the session.
     * EnsureUserSelected middleware guarantees session('user_id') is set
     * before any controller action runs, so this is never null in practice.
     */
    protected function currentUser(): ?User
    {
        if ($this->currentUser) {
            return $this->currentUser;
        }

        $id = session('user_id');

        return $this->currentUser = $id ? User::find($id) : null;
    }

    protected function currentEmployee(): ?Employee
    {
        if ($this->currentEmployee) {
            return $this->currentEmployee;
        }

        $user = $this->currentUser();

        return $this->currentEmployee = $user
            ? Employee::where('user_id', $user->id)->first()
            : null;
    }

    protected function currentRole(): string
    {
        return $this->currentUser()->role ?? 'employee';
    }

    protected function isManagerOrAbove(): bool
    {
        return in_array($this->currentRole(), ['manager', 'hr_admin', 'super_admin'], true);
    }

    protected function isHrOrAbove(): bool
    {
        return in_array($this->currentRole(), ['hr_admin', 'super_admin'], true);
    }

    protected function roleData(?string $role = null): array
    {
        $roles = Config::get('hr_modules.roles');
        $role ??= $this->currentRole();

        return $roles[$role] ?? $roles['employee'];
    }

    protected function allRoles(): array
    {
        return Config::get('hr_modules.roles');
    }

    protected function modulesForRole(?string $role = null): array
    {
        $role ??= $this->currentRole();

        return collect(Config::get('hr_modules.modules'))
            ->filter(fn ($module) => in_array($role, $module['roles'], true))
            ->all();
    }

    /**
     * Shared view data every module screen and the dashboard need:
     * who's signed in, their role's look & feel, and the nav they can see.
     */
    protected function baseViewData(): array
    {
        $role = $this->currentRole();
        $user = $this->currentUser();

        return [
            'authUser' => $user,
            'employee' => $this->currentEmployee(),
            'role' => $role,
            'roleData' => $this->roleData($role),
            'roles' => $this->allRoles(),
            'modules' => $this->modulesForRole($role),
            'allUsers' => User::orderBy('name')->get(),
            'navNotifications' => $user ? $user->notifications()->limit(8)->get() : collect(),
            'navUnreadCount' => $user ? $user->unreadNotificationsCount() : 0,
        ];
    }

    protected function abortUnlessModuleAllowed(string $key): array
    {
        $allModules = Config::get('hr_modules.modules');
        abort_unless(isset($allModules[$key]), 404);
        $module = $allModules[$key];
        abort_unless(in_array($this->currentRole(), $module['roles'], true), 403, 'This module is not part of your current role\'s access.');

        return $module;
    }
}
