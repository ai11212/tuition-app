<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /** Is the signed-in user the super? */
    private function actorIsSuper(): bool
    {
        return (bool) optional(Auth::user())->is_super;
    }

    /** List + add form on one screen, same shape as the Book Library page */
    public function index()
    {
        $query = User::orderByDesc('is_admin')->orderBy('name');

        // Admins manage assistants only: admin and super accounts are invisible
        // to them, so there is nothing to see, probe or attempt to edit.
        // Safe before the is_super column exists — the accessor then falls back
        // to is_admin, so everyone reaching this admin-gated screen is "super"
        // and this filter never runs.
        if (!$this->actorIsSuper()) {
            $query->where('is_admin', false)->where('is_super', false);
        }

        return view('admin.users.index', [
            'users'        => $query->paginate(25),
            'capabilities' => User::CAPABILITIES,
        ]);
    }

    public function store(Request $r)
    {
        $data = $this->validated($r);

        // Only the super may create an admin. Forced server-side, so hiding the
        // tick-box is cosmetic only and a crafted request cannot get past it.
        $isAdmin      = $this->actorIsSuper() && $r->boolean('is_admin');
        $capabilities = $isAdmin ? [] : ($data['capabilities'] ?? []);

        // An account with neither admin nor any capability can sign in but
        // reach nothing, which looks like a broken app. Refuse to create one.
        if (!$isAdmin && empty($capabilities)) {
            return back()->withInput()->withErrors([
                'capabilities' => 'Give this user at least one permission, or tick Full admin.',
            ]);
        }

        User::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'password'     => Hash::make($data['password']),
            'is_admin'     => $isAdmin,
            'capabilities' => $capabilities,
            'is_active'    => $r->boolean('is_active'),
        ]);

        return redirect()->route('admin.users.index')->with('ok', 'User "'.$data['name'].'" created.');
    }

    public function update(Request $r, $id)
    {
        $user = User::findOrFail($id);

        // Only the super may modify an admin or super account
        if (!$this->actorIsSuper() && ($user->is_admin || $user->is_super)) {
            return back()->with('warning', 'Only the super user can modify admin accounts.');
        }

        $data = $this->validated($r, $user->id);

        // Non-supers can never grant admin, whatever was posted
        $isAdmin      = $this->actorIsSuper() ? $r->boolean('is_admin') : false;
        $isActive     = $r->boolean('is_active');
        $capabilities = $isAdmin ? [] : ($data['capabilities'] ?? []);

        // Never leave an account able to sign in but reach nothing
        if (!$isAdmin && empty($capabilities)) {
            return back()->withInput()->withErrors([
                'capabilities' => 'Give this user at least one permission, or tick Full admin.',
            ]);
        }

        // The super account must always remain usable
        if ($user->is_super && (!$isAdmin || !$isActive)) {
            return back()->with('warning', 'The super user cannot be demoted or deactivated.');
        }

        // Self-lockout guards
        if ($user->id === Auth::id() && !$isAdmin) {
            return back()->with('warning', 'You cannot remove your own admin access.');
        }
        if ($user->id === Auth::id() && !$isActive) {
            return back()->with('warning', 'You cannot deactivate your own account.');
        }
        if ($user->is_admin && $user->is_active && (!$isAdmin || !$isActive) && $this->lastActiveAdmin($user->id)) {
            return back()->with('warning', 'This is the only active admin — the app would be left with no administrator.');
        }

        $user->fill([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'is_admin'     => $isAdmin,
            'capabilities' => $capabilities,
            'is_active'    => $isActive,
        ]);

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('ok', 'User "'.$user->name.'" updated.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Only the super may delete an admin or super account
        if (!$this->actorIsSuper() && ($user->is_admin || $user->is_super)) {
            return back()->with('warning', 'Only the super user can delete admin accounts.');
        }
        if ($user->is_super) {
            return back()->with('warning', 'The super user account cannot be deleted.');
        }
        if ($user->id === Auth::id()) {
            return back()->with('warning', 'You cannot delete your own account.');
        }
        if ($user->is_admin && $user->is_active && $this->lastActiveAdmin($user->id)) {
            return back()->with('warning', 'This is the only active admin and cannot be deleted.');
        }

        $name = $user->name;
        $user->delete();   // activity_logs.user_id is set null, the history stays

        return redirect()->route('admin.users.index')->with('ok', 'User "'.$name.'" deleted.');
    }

    private function validated(Request $r, ?int $ignoreId = null): array
    {
        // Trim first, or a leading space would create a second account that
        // looks identical on screen and slips past the uniqueness rule below.
        $r->merge(['name' => trim((string) $r->input('name'))]);

        return $r->validate([
            // Names must be unique: the User column on payments, books and
            // attendance stores the name, so two people sharing one would be
            // indistinguishable there.
            'name'           => ['required', 'string', 'max:100', Rule::unique('users', 'name')->ignore($ignoreId)],
            'email'          => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($ignoreId)],
            'password'       => $ignoreId ? 'nullable|string|min:6|confirmed' : 'required|string|min:6|confirmed',
            'capabilities'   => 'nullable|array',
            'capabilities.*' => ['string', Rule::in(array_keys(User::CAPABILITIES))],
        ]);
    }

    /** True when $excludeId is the only active admin left */
    private function lastActiveAdmin(int $excludeId): bool
    {
        return User::where('is_admin', true)
            ->where('is_active', true)
            ->where('id', '!=', $excludeId)
            ->count() === 0;
    }
}
