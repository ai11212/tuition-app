<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Access control for the whole app lives here. Every ability short-circuits
     * on is_admin, so an admin passes all of them and nothing changes for them.
     * A deactivated user passes nothing, which takes effect on their next
     * request rather than only at their next login.
     */
    public function boot(): void
    {
        Gate::define('admin', fn (User $user) => $user->is_active && $user->is_admin);

        // The single super user: may create/modify admin accounts and is the
        // only one who can read the activity log. Set by SQL, never in the UI.
        Gate::define('super', fn (User $user) => $user->is_active && $user->is_super);

        Gate::define('attendance', fn (User $user) => $user->is_active
            && ($user->is_admin || $user->hasCapability('attendance')));

        Gate::define('books', fn (User $user) => $user->is_active
            && ($user->is_admin || $user->hasCapability('books')));

        Gate::define('admission', fn (User $user) => $user->is_active
            && ($user->is_admin || $user->hasCapability('admission')));

        // Recording a payment only. Editing, deleting and exporting stay admin.
        Gate::define('payments', fn (User $user) => $user->is_active
            && ($user->is_admin || $user->hasCapability('payments')));

        // Creating a teacher only. Editing and deleting stay admin, because an
        // hourly rate drives what that teacher is paid.
        Gate::define('teachers', fn (User $user) => $user->is_active
            && ($user->is_admin || $user->hasCapability('teachers')));

        // Student lookup: anyone who has a job to do needs to find a reference.
        // Any capability grants it, so the standalone "reference" tick-box works
        // on its own and existing assistants keep the access they have today.
        Gate::define('lookup', fn (User $user) => $user->is_active
            && ($user->is_admin || count($user->capabilityList()) > 0));

        // Money-only screens and links. Assistants still see the due-balance
        // prompt on the attendance sheet and the reference profile — this gate
        // governs the finance menus and the "Record Payment" button, which lead
        // to screens they cannot open.
        Gate::define('finance', fn (User $user) => $user->is_active && $user->is_admin);
    }
}
