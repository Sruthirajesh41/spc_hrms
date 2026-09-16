<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureUserSelected
{
    /**
     * This is a demo app with no password auth: EnsureUserSelected makes sure
     * every request past this point has a valid session('user_id') to build
     * the sidebar, KPIs and role gating from. If none is set (or it points at
     * a user that no longer exists), send the visitor to the sign-in chooser.
     */
    public function handle(Request $request, Closure $next)
    {
        $id = session('user_id');

        if (! $id || ! User::find($id)) {
            return redirect()->route('login.show');
        }

        return $next($request);
    }
}
