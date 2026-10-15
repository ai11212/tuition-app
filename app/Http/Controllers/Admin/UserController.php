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
    /** List + add form on one screen, same shape as the Book Library page */
    public function index()
    {
        $users = User::orderByDesc('is_admin')->orderBy('name')->paginate(25);

        return view('admin.users.index', [
            'users'        => $users,
            'capabilities' => User::CAPABILITIES,
        ]);
    }

    public function store(Request $r)
    {
        $data = $this->validated($r);

        User::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'password'     => Hash::make($data['password']),
            'is_admin'     => $r->boolean('is_admin'),
            'capabilities' => $r->boolean('is_admin') ? [] : ($data['capabilities'] ?? []),
            'is_active'    => $r->boolean('is_active'),
        ]);

        return redirect()->route('admin.users.index')->with('ok', 'User "'.$data['name'].'" created.');
    }

    public function update(Request $r, $id)
    {
        $user = User::findOrFail($id);
        $data = $this->validated($r, $user->id);

        $isAdmin  = $r->boolean('is_admin');
        $isActive = $r->boolean('is_active');

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
            'capabilities' => $isAdmin ? [] : ($data['capabilities'] ?? []),
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
        return $r->validate([
            'name'           => 'required|string|max:100',
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
