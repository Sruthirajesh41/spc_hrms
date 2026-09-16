<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $module = $this->abortUnlessModuleAllowed('announcements');
        $role = $this->currentRole();
        $user = $this->currentUser();

        $announcements = Announcement::whereNotNull('published_at')
            ->where(function ($q) use ($role) {
                $q->where('audience_role', 'all')->orWhere('audience_role', $role);
            })
            ->with('createdBy')
            ->orderByDesc('published_at')
            ->get();

        $readIds = AnnouncementRead::where('user_id', $user->id)->pluck('announcement_id')->all();

        // Viewing the list marks everything currently shown as read.
        $unreadNow = $announcements->pluck('id')->diff($readIds);
        foreach ($unreadNow as $id) {
            AnnouncementRead::create(['announcement_id' => $id, 'user_id' => $user->id, 'read_at' => now()]);
        }

        return view('modules.announcements', array_merge($this->baseViewData(), [
            'module' => $module,
            'moduleKey' => 'announcements',
            'announcements' => $announcements,
            'readIds' => $readIds,
        ]));
    }

    public function store(Request $request)
    {
        $this->abortUnlessModuleAllowed('announcements');
        abort_unless($this->isHrOrAbove(), 403);

        $data = $request->validate([
            'title' => 'required|string|max:200',
            'body' => 'required|string|max:4000',
            'audience_role' => 'required|in:all,employee,manager,hr_admin,super_admin',
        ]);

        $announcement = Announcement::create(array_merge($data, [
            'created_by' => $this->currentUser()->id,
            'published_at' => now(),
            'created_at' => now(),
        ]));

        $recipients = $data['audience_role'] === 'all'
            ? User::pluck('id')
            : User::where('role', $data['audience_role'])->pluck('id');

        foreach ($recipients as $userId) {
            if ($userId === $this->currentUser()->id) {
                continue;
            }
            Notification::notify($userId, 'announcement', 'New announcement: '.$announcement->title, '/modules/announcements');
        }

        return back()->with('status', 'Announcement published.');
    }
}
