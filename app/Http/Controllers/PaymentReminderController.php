<?php

namespace App\Http\Controllers;

use App\Models\PaymentReminder;
use App\Models\ReminderSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class PaymentReminderController extends Controller
{
    /**
     * Display payment reminders dashboard
     */
    public function index(Request $request)
    {
        // Check if module is enabled
        if (!config('reminders.enabled')) {
            abort(404, 'Payment Reminders module is disabled');
        }

        // Get overdue and due reminders
        $overdue = PaymentReminder::overdue()->get();
        $due = PaymentReminder::due()->get();

        // Calculate summary stats
        $summary = [
            'overdue_count' => $overdue->count(),
            'overdue_total' => $overdue->sum('expected_amount'),
            'due_count' => $due->count(),
            'due_total' => $due->sum('expected_amount'),
        ];

        // Last calculation time
        $lastCalculated = PaymentReminder::max('calculated_at');
        if ($lastCalculated) {
            $lastCalculated = \Carbon\Carbon::parse($lastCalculated);
        }

        return view('reminders.index', compact('overdue', 'due', 'summary', 'lastCalculated'));
    }

    /**
     * Display settings page
     */
    public function settings()
    {
        if (!config('reminders.enabled')) {
            abort(404, 'Payment Reminders module is disabled');
        }

        $settings = ReminderSetting::getAllSettings();

        return view('reminders.settings', compact('settings'));
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'weekly_due_days' => 'required|integer|min:1|max:30',
            'weekly_overdue_days' => 'required|integer|min:1|max:30',
            'monthly_due_days' => 'required|integer|min:1|max:60',
            'monthly_overdue_days' => 'required|integer|min:1|max:60',
        ]);

        foreach ($validated as $key => $value) {
            ReminderSetting::set($key, $value, 'number');
        }

        return redirect()
            ->route('reminders.settings')
            ->with('success', 'Settings updated successfully!');
    }

    /**
     * Skip a reminder (mark as holiday)
     */
    public function skip($id)
    {
        $reminder = PaymentReminder::findOrFail($id);

        $reminder->update([
            'is_holiday' => true,
            'notes' => 'Skipped on ' . now()->format('d/m/Y'),
        ]);

        return redirect()
            ->route('reminders.index')
            ->with('success', "Reminder for {$reminder->reference} skipped successfully!");
    }

    /**
     * Manually refresh/recalculate reminders
     */
    public function refresh()
    {
        if (!config('reminders.features.manual_refresh')) {
            abort(403, 'Manual refresh is disabled');
        }

        // Run the calculation command
        Artisan::call('reminders:update');

        return redirect()
            ->route('reminders.index')
            ->with('success', 'Reminders refreshed successfully!');
    }

    /**
     * Get badge count for menu
     */
    public static function getBadgeCount(): int
    {
        try {
            if (!config('reminders.enabled')) {
                return 0;
            }

            $badgeType = config('reminders.display.badge_count', 'overdue');

            return match($badgeType) {
                'overdue' => PaymentReminder::where('status', 'overdue')
                                           ->where('is_holiday', false)
                                           ->count(),
                'due' => PaymentReminder::where('status', 'due')
                                       ->where('is_holiday', false)
                                       ->count(),
                'all' => PaymentReminder::where('is_holiday', false)->count(),
                default => 0,
            };
        } catch (\Exception $e) {
            // Silently fail if tables don't exist or other issues
            return 0;
        }
    }
}
