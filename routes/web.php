<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController, DashboardController, StudentController, StaffController, BookController,
    AttendanceController, StaffAttendanceController, PaymentController, TimetableController,
    ReferenceProfileController, AccountController
};

Route::get('/login', [AuthController::class,'showLogin'])->name('login');
Route::post('/login', [AuthController::class,'login']);
Route::post('/logout', [AuthController::class,'logout'])->name('logout');

Route::middleware('auth')->group(function(){
    Route::get('/', [DashboardController::class,'index'])->name('dashboard');

    // Students wizard (create)
    Route::get('/students/create', [StudentController::class,'create'])->name('students.create');
    Route::post('/students/next',   [StudentController::class,'next'])->name('students.next');
    Route::get('/students/confirm', [StudentController::class,'confirm'])->name('students.confirm');
    Route::post('/students',        [StudentController::class,'store'])->name('students.store');
    
    // Students list and management
    Route::get('/students',         [StudentController::class,'index'])->name('students.index');
    Route::get('/students/{reference}/edit', [StudentController::class,'edit'])->name('students.edit');
    Route::patch('/students/{id}/update-payment', [StudentController::class,'updatePayment'])->name('student.updatePayment');
    Route::patch('/students/{student}/update-pending-amount', [PaymentController::class,'updatePendingAmount'])->name('student.updatePendingAmount');
    Route::patch('/students/{student}/add-pending-amount', [PaymentController::class,'addPendingAmount'])->name('student.addPendingAmount');
    Route::patch('/students/{student}/set-pending-amount', [PaymentController::class,'setPendingAmount'])->name('student.setPendingAmount');
    Route::patch('/students/{student}/update-new-plan', [StudentController::class,'updateNewPlan'])->name('student.updateNewPlan');
    Route::post('/students/{reference}/next-edit', [StudentController::class,'nextEdit'])->name('students.next.edit');
    Route::put('/students/{reference}',      [StudentController::class,'update'])->name('students.update');
    Route::delete('/students/{reference}',   [StudentController::class,'destroy'])->name('students.destroy');

    // Debug routes hidden (15/07/2026) — they exposed the raw admission session
    // payload (incl. guardian details). Uncomment only while troubleshooting.
    /*
    // Debug: show current admission session (authenticated)
    Route::get('/debug/admission', function (\Illuminate\Http\Request $r) {
        return response()->json($r->session()->get('admission'));
    })->name('debug.admission');

    // Temporary public debug route (keyed). Remove after debugging.
    Route::get('/debug/admission-public', function (\Illuminate\Http\Request $r) {
        $key = $r->query('key');
        if ($key !== env('DEBUG_ADMISSION_KEY', 'local-debug-key')) {
            return response('Unauthorized', 401);
        }
        return response()->json($r->session()->get('admission'));
    });
    */
    // Staff + Books
    Route::resource('staff', StaffController::class)->except(['show']);

    // Teachers (staff rows with role=teacher, auto reference T001+)
    Route::get('/teachers/create', [\App\Http\Controllers\TeacherController::class,'create'])->name('teachers.create');
    Route::post('/teachers',       [\App\Http\Controllers\TeacherController::class,'store'])->name('teachers.store');
    Route::get('/teachers/{staff}/edit', [\App\Http\Controllers\TeacherController::class,'edit'])->name('teachers.edit');
    Route::put('/teachers/{staff}',      [\App\Http\Controllers\TeacherController::class,'update'])->name('teachers.update');
    Route::delete('/teachers/{staff}',   [\App\Http\Controllers\TeacherController::class,'destroy'])->name('teachers.destroy');
    Route::get('/books/get-siblings', [BookController::class, 'getSiblings'])->name('books.get-siblings');
    Route::resource('books', BookController::class)->except(['show']);

    // Attendance
    Route::get('/attendance', [AttendanceController::class,'sheet'])->name('attendance.sheet');
    Route::post('/attendance/save', [AttendanceController::class,'save'])->name('attendance.save');
    Route::get('/attendance/view', [AttendanceController::class,'view'])->name('attendance.view');
    Route::put('/attendance/{attendance}', [AttendanceController::class,'update'])->name('attendance.update');
    Route::delete('/attendance/{attendance}', [AttendanceController::class,'destroy'])->name('attendance.destroy');
    Route::get('/attendance/status', [AttendanceController::class,'statusByDate'])->name('attendance.status');
    Route::get('/attendance/staff', [StaffAttendanceController::class,'sheet'])->name('attendance.staff');
    Route::post('/attendance/staff/save', [StaffAttendanceController::class,'save'])->name('attendance.staff.save');

    // Finance
    Route::get('/payments', [\App\Http\Controllers\PaymentController::class,'take'])->name('payments.take');
    Route::post('/payments', [\App\Http\Controllers\PaymentController::class,'store'])->name('payments.store');
    Route::get('/invoice/{invoice}/print', [\App\Http\Controllers\PaymentController::class,'printInvoice'])->name('invoice.print');
    Route::get('/defaulters', [\App\Http\Controllers\PaymentController::class,'defaulters'])->name('defaulters');
    Route::get('/accounts', [\App\Http\Controllers\PaymentController::class,'summary'])->name('accounts.summary');

    // Reference & Timetable
    Route::get('/reference-profile', [ReferenceProfileController::class,'form'])->name('ref.form');
    Route::get('/reference-profile/show', [ReferenceProfileController::class,'show'])->name('ref.show');
    Route::get('/print-timetable', [TimetableController::class,'printForm'])->name('tt.form');
    Route::get('/print-timetable/show', [TimetableController::class,'print'])->name('tt.show');
    
    // Enhanced student timetable printing
    Route::get('/student/{reference}/timetable/print', [TimetableController::class,'printStudentTimetable'])->name('student.timetable.print');

    // Account
    Route::get('/account/password', [AccountController::class,'passwordForm'])->name('account.password');
    Route::post('/account/password', [AccountController::class,'passwordSave'])->name('account.password.save');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/payments',        [\App\Http\Controllers\PaymentController::class,'take'])->name('payments');
    Route::post('/payments',       [\App\Http\Controllers\PaymentController::class,'store'])->name('payments.store');
    Route::put('/payments/{id}',   [\App\Http\Controllers\PaymentController::class,'update'])->name('payments.update');
    Route::delete('/payments/{id}',[\App\Http\Controllers\PaymentController::class,'destroy'])->name('payments.destroy');
    Route::get('/payments/export', [\App\Http\Controllers\PaymentController::class,'exportCsv'])->name('payments.export');
    Route::get('/payments/print',  [\App\Http\Controllers\PaymentController::class,'print'])->name('payments.print');
    Route::get('/accounts',        [\App\Http\Controllers\PaymentController::class,'summary'])->name('accounts.summary');
    Route::get('/payment-verification', [\App\Http\Controllers\PaymentVerificationController::class,'index'])->name('payment.verification');

    // Payment Reminders Module (NEW - can be commented out to disable)
    Route::get('/payment-reminders',          [\App\Http\Controllers\PaymentReminderController::class, 'index'])->name('reminders.index');
    Route::get('/payment-reminders/settings', [\App\Http\Controllers\PaymentReminderController::class, 'settings'])->name('reminders.settings');
    Route::post('/payment-reminders/settings',[\App\Http\Controllers\PaymentReminderController::class, 'updateSettings'])->name('reminders.updateSettings');
    Route::post('/payment-reminders/skip/{id}',[\App\Http\Controllers\PaymentReminderController::class, 'skip'])->name('reminders.skip');
    Route::post('/payment-reminders/refresh', [\App\Http\Controllers\PaymentReminderController::class, 'refresh'])->name('reminders.refresh');

    // Expenses
    Route::get('/expenses',         [\App\Http\Controllers\ExpenseController::class,'index'])->name('expenses');
    Route::post('/expenses',        [\App\Http\Controllers\ExpenseController::class,'store'])->name('expenses.store');
    Route::put('/expenses/{id}',    [\App\Http\Controllers\ExpenseController::class,'update'])->name('expenses.update');
    Route::delete('/expenses/{id}', [\App\Http\Controllers\ExpenseController::class,'destroy'])->name('expenses.destroy');
    Route::get('/expenses/export',  [\App\Http\Controllers\ExpenseController::class,'exportCsv'])->name('expenses.export');

    // Book Library (catalog only — separate from student-issued books)
    Route::get('/book-library',         [\App\Http\Controllers\LibraryBookController::class,'index'])->name('library.index');
    Route::post('/book-library',        [\App\Http\Controllers\LibraryBookController::class,'store'])->name('library.store');
    Route::delete('/book-library/{id}', [\App\Http\Controllers\LibraryBookController::class,'destroy'])->name('library.destroy');

    // Teacher Salaries (paid from attendance; auto-creates "Teacher Salary" expenses)
    Route::get('/teacher-salaries',                  [\App\Http\Controllers\TeacherSalaryController::class,'index'])->name('salaries.index');
    Route::post('/teacher-salaries',                 [\App\Http\Controllers\TeacherSalaryController::class,'store'])->name('salaries.store');
    Route::get('/teacher-salaries/{salary}/slip',    [\App\Http\Controllers\TeacherSalaryController::class,'show'])->name('salaries.slip');
    Route::get('/teacher-salaries/{salary}/edit',    [\App\Http\Controllers\TeacherSalaryController::class,'edit'])->name('salaries.edit');
    Route::put('/teacher-salaries/{salary}',         [\App\Http\Controllers\TeacherSalaryController::class,'update'])->name('salaries.update');
    Route::delete('/teacher-salaries/{salary}',      [\App\Http\Controllers\TeacherSalaryController::class,'destroy'])->name('salaries.destroy');
});
