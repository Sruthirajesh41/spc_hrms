<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        $module = $this->abortUnlessModuleAllowed('support');
        $employee = $this->currentEmployee();

        $ownTickets = $employee
            ? $employee->supportTickets()->orderByDesc('id')->get()
            : collect();

        $allTickets = collect();
        if ($this->isHrOrAbove()) {
            $allTickets = SupportTicket::with(['employee.user', 'resolvedBy'])
                ->orderByRaw("CASE status WHEN 'open' THEN 1 WHEN 'in_progress' THEN 2 WHEN 'resolved' THEN 3 ELSE 4 END")
                ->orderByDesc('id')
                ->get();
        }

        return view('modules.support', array_merge($this->baseViewData(), [
            'module' => $module,
            'moduleKey' => 'support',
            'ownTickets' => $ownTickets,
            'allTickets' => $allTickets,
        ]));
    }

    public function store(Request $request)
    {
        $this->abortUnlessModuleAllowed('support');
        $employee = $this->currentEmployee();
        abort_unless($employee, 403);

        $data = $request->validate([
            'category' => 'required|in:it,hr,payroll,facilities,documents,other',
            'subject' => 'required|string|max:200',
            'description' => 'required|string|max:2000',
            'priority' => 'required|in:low,normal,high',
        ]);

        SupportTicket::create(array_merge($data, [
            'employee_id' => $employee->id,
            'status' => 'open',
            'created_at' => now(),
        ]));

        foreach (User::whereIn('role', ['hr_admin', 'super_admin'])->pluck('id') as $userId) {
            Notification::notify($userId, 'support_ticket', 'New '.$data['category'].' ticket: '.$data['subject'], '/modules/support');
        }

        return back()->with('status', 'Ticket raised — HR will follow up.');
    }

    public function update(Request $request, SupportTicket $ticket)
    {
        $this->abortUnlessModuleAllowed('support');
        abort_unless($this->isHrOrAbove(), 403);

        $data = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'resolution_note' => 'nullable|string|max:2000',
        ]);

        $isClosing = in_array($data['status'], ['resolved', 'closed'], true);
        $data['resolved_at'] = $isClosing ? now() : null;
        $data['resolved_by'] = $isClosing ? $this->currentUser()->id : null;
        $ticket->update($data);

        $ticket->loadMissing('employee.user');
        if ($ticket->employee && $ticket->employee->user) {
            Notification::notify(
                $ticket->employee->user->id,
                'support_update',
                'Your ticket "'.$ticket->subject.'" is now '.str_replace('_', ' ', $data['status']).'.',
                '/modules/support'
            );
        }

        return back()->with('status', 'Ticket updated.');
    }
}
