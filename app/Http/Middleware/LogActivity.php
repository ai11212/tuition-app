<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records every successful write action to activity_logs.
 *
 * Deliberately a pure observer: it runs AFTER the response is produced, never
 * inspects or alters it beyond the status code, and swallows its own errors.
 * Logging can therefore never break a screen that works today.
 *
 * Reads (GET/HEAD) are never logged, and neither are requests that failed
 * validation, so the log stays a record of what actually changed.
 */
class LogActivity
{
    /** Never stored, under any circumstances */
    private const REDACT = [
        'password', 'password_confirmation', 'current_password', '_token', '_method',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $this->record($request, $response);
        } catch (\Throwable $e) {
            // Logging must never take the app down.
        }

        return $response;
    }

    private function record(Request $request, Response $response): void
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 400) {
            return;
        }

        // Sign-in and sign-out are recorded by AuthController, which has the
        // context to describe them properly. Skip them here or they log twice.
        if (in_array($request->path(), ['login', 'logout'], true)) {
            return;
        }

        $user = Auth::user();
        if (!$user) {
            return;
        }

        $action = optional($request->route())->getName() ?: $request->path();

        ActivityLog::create([
            'user_id'     => $user->id,
            'user_name'   => $user->name,
            'action'      => mb_substr($action, 0, 100),
            'description' => mb_substr($this->describe($action, $request), 0, 500),
            'method'      => $request->method(),
            'url'         => mb_substr($request->fullUrl(), 0, 500),
            'ip'          => $request->ip(),
            'created_at'  => now(),
        ]);
    }

    /** Plain-English description, built only from non-sensitive request fields */
    private function describe(string $action, Request $request): string
    {
        $v = function (string $key, string $default = '') use ($request) {
            if (in_array($key, self::REDACT, true)) {
                return $default;
            }
            $value = $request->input($key);

            return is_scalar($value) && $value !== '' ? (string) $value : $default;
        };

        $money = fn (string $key) => number_format((float) $request->input($key, 0), 2);
        $count = function (string $key) use ($request) {
            $value = $request->input($key);

            return is_array($value) ? count($value) : 0;
        };

        switch ($action) {
            // ---- Attendance ----
            case 'attendance.save':
                return trim(sprintf(
                    'Marked attendance — %s %s on %s%s',
                    $v('subject', 'lesson'),
                    $v('time'),
                    $v('date'),
                    $v('teacher') ? ' (teacher: '.$v('teacher').')' : ''
                ));
            case 'attendance.update':
                return 'Edited an attendance record';
            case 'attendance.destroy':
                return 'Deleted an attendance record';
            case 'attendance.staff.save':
                return 'Saved staff attendance for '.$v('date');

            // ---- Books ----
            case 'books.store':
                return sprintf(
                    'Issued book "%s" (%s, £%s) to %d student(s), dated %s',
                    $v('title'), $v('subject'), $money('price'), $count('students'), $v('issue_date')
                );
            case 'books.update':
                return sprintf('Edited book "%s" (%s, £%s)', $v('title'), $v('subject'), $money('price'));
            case 'books.destroy':
                return 'Deleted an issued book';
            case 'library.store':
                return sprintf('Added "%s" (%s, £%s) to the Book Library', $v('title'), $v('subject'), $money('price'));
            case 'library.update':
                return sprintf('Edited Book Library entry "%s"', $v('title'));
            case 'library.destroy':
                return 'Removed a book from the Book Library';

            // ---- Money ----
            case 'payments.store':
                $parts = [];
                if ((float) $request->input('payment_due', 0) > 0) {
                    $parts[] = 'due £'.$money('payment_due');
                }
                if ((float) $request->input('amount', 0) > 0) {
                    $parts[] = 'paid £'.$money('amount').' by '.$v('method', 'unknown');
                }

                return sprintf('Payment entry for %s — %s', $v('reference'), $parts ? implode(', ', $parts) : 'no amount');
            case 'payments.update':
                return 'Edited a payment of £'.$money('amount');
            case 'payments.destroy':
                return 'Deleted a payment';
            case 'student.updatePendingAmount':
            case 'student.setPendingAmount':
                return 'Set the fee base to £'.$money($request->has('amount_to_set') ? 'amount_to_set' : 'pending_amount');
            case 'student.addPendingAmount':
                return 'Added £'.$money('amount_to_add').' to the fee base';
            case 'student.updatePayment':
                return 'Updated the payment field';
            case 'student.updateNewPlan':
                return 'Updated the student new plan to £'.$money('new_plan');

            // ---- Students ----
            case 'students.store':
                return 'Created a new admission';
            case 'students.next':
            case 'students.next.edit':
                return 'Completed an admission form step';
            case 'students.update':
                return 'Edited admission '.$request->route('reference');
            case 'students.destroy':
                return 'DELETED admission '.$request->route('reference').' (all siblings)';

            // ---- Staff, teachers, salaries, expenses ----
            case 'teachers.store':
                return 'Added teacher '.$v('name');
            case 'teachers.update':
                return 'Edited a teacher';
            case 'teachers.destroy':
                return 'Deleted a teacher';
            case 'staff.store':
                return 'Added staff member '.$v('name');
            case 'staff.update':
                return 'Edited a staff member';
            case 'staff.destroy':
                return 'Deleted a staff member';
            case 'salaries.store':
                return 'Paid a teacher salary';
            case 'salaries.update':
                return 'Edited a teacher salary';
            case 'salaries.destroy':
                return 'Deleted a teacher salary';
            case 'expenses.store':
                return sprintf('Added %s of £%s (%s)', $v('type', 'expense'), $money('amount'), $v('category'));
            case 'expenses.update':
                return 'Edited an expense';
            case 'expenses.destroy':
                return 'Deleted an expense';

            // ---- Reminders ----
            case 'reminders.updateSettings':
                return 'Changed payment reminder settings';
            case 'reminders.skip':
                return 'Skipped a payment reminder';
            case 'reminders.refresh':
                return 'Refreshed payment reminders';

            // ---- Account & admin ----
            case 'account.password.save':
                return 'Changed their own password';
            case 'admin.users.store':
                return 'Created user '.$v('name');
            case 'admin.users.update':
                return 'Edited user '.$v('name');
            case 'admin.users.destroy':
                return 'Deleted a user';

            default:
                return 'Performed '.$action;
        }
    }
}
