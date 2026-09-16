<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Keys editable from the Settings screen, with a human label and
     * the input type to render. Anything in system_settings but not
     * listed here (there shouldn't be any) simply won't show a field.
     */
    private const EDITABLE = [
        'company_name' => ['label' => 'Company name', 'type' => 'text'],
        'pf_contribution_rate' => ['label' => 'PF contribution rate (%)', 'type' => 'number'],
        'esi_threshold' => ['label' => 'ESI threshold (monthly gross)', 'type' => 'number'],
        'leave_year_start_month' => ['label' => 'Leave year start month (1-12)', 'type' => 'number'],
        'payroll_cutoff_day' => ['label' => 'Payroll cutoff day of month', 'type' => 'number'],
        'attendance_grace_minutes' => ['label' => 'Attendance grace period (minutes)', 'type' => 'number'],
        'wfh_max_days_per_month' => ['label' => 'Max WFH days per employee / month', 'type' => 'number'],
    ];

    public function index()
    {
        $module = $this->abortUnlessModuleAllowed('settings');

        $existing = SystemSetting::pluck('setting_value', 'setting_key');

        $settings = collect(self::EDITABLE)->map(function ($meta, $key) use ($existing) {
            return array_merge($meta, ['key' => $key, 'value' => $existing[$key] ?? '']);
        })->values();

        return view('modules.settings', array_merge($this->baseViewData(), [
            'module' => $module,
            'moduleKey' => 'settings',
            'settings' => $settings,
        ]));
    }

    public function update(Request $request)
    {
        $this->abortUnlessModuleAllowed('settings');
        abort_unless($this->isHrOrAbove(), 403);

        $keys = array_keys(self::EDITABLE);
        $data = $request->validate(
            collect($keys)->mapWithKeys(fn ($k) => [$k => 'nullable|string|max:255'])->all()
        );

        foreach ($keys as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            $setting = SystemSetting::where('setting_key', $key)->first();
            $oldValue = $setting->setting_value ?? null;
            $newValue = $data[$key] ?? '';

            if ($setting) {
                $setting->update(['setting_value' => $newValue, 'updated_at' => now()]);
            } else {
                SystemSetting::create(['setting_key' => $key, 'setting_value' => $newValue, 'updated_at' => now()]);
            }

            if ($oldValue !== $newValue) {
                AuditLog::create([
                    'user_id' => $this->currentUser()->id,
                    'action' => 'UPDATE',
                    'module' => 'settings',
                    'old_value' => json_encode([$key => $oldValue]),
                    'new_value' => json_encode([$key => $newValue]),
                    'created_at' => now(),
                ]);
            }
        }

        return back()->with('status', 'Settings saved.');
    }
}
