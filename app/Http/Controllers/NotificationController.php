<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Show the complete notification history for the currently logged-in user.
     * Newest notifications are always displayed first.
     */
    public function index()
    {
        $user = $this->currentUser();

        $notifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('modules.notifications', array_merge($this->baseViewData(), [
            'notifications' => $notifications,
        ]));
    }

    /**
     * Mark one notification as read and follow its related module link.
     */
    public function markRead(Notification $notification)
    {
        abort_unless($notification->user_id === $this->currentUser()->id, 403);

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return redirect($notification->link ?? route('notifications.index'));
    }

    /**
     * Mark all notifications belonging to the logged-in user as read.
     */
    public function markAllRead(Request $request)
    {
        Notification::where('user_id', $this->currentUser()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }
}
