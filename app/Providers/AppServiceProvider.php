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

        Gate::define('attendance', fn (User $user) => $user->is_active
            && ($user->is_admin || $user->hasCapability('attendance')));

        Gate::define('books', fn (User $user) => $user->is_active
            && ($user->is_admin || $user->hasCapability('books')));

        // Student lookup: anyone who has a job to do needs to find a reference
        Gate::define('lookup', fn (User $user) => $user->is_active
            && ($user->is_admin || $user->hasAnyCapability(['attendance', 'books'])));

        // Money-only screens and links. Assistants still see the due-balance
        // prompt on the attendance sheet and the reference profile — this gate
        // governs the finance menus and the "Record Payment" button, which lead
        // to screens they cannot open.
        Gate::define('finance', fn (User $user) => $user->is_active && $user->is_admin);
    }
}
