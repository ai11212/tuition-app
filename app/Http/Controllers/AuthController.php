<?php
namespace App\Http\Controllers;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller{
    public function showLogin(){ return view('auth.login'); }

    public function login(Request $r){
        $cred = $r->only('email','password');

        if(Auth::attempt($cred)){
            $user = Auth::user();

            // Deactivated accounts can authenticate but may not hold a session
            if(!$user->is_active){
                Auth::logout();
                $r->session()->invalidate();
                $r->session()->regenerateToken();
                $this->log('login.blocked', 'Blocked sign-in — account is deactivated', $r, $user);
                return back()->withErrors(['email'=>'This account has been deactivated.']);
            }

            $r->session()->regenerate();
            $this->log('login', 'Signed in', $r, $user);

            return redirect($this->homeFor($user));
        }

        $this->log('login.failed', 'Failed sign-in attempt for '.$r->input('email'), $r, null);

        return back()->withErrors(['email'=>'Invalid credentials.']);
    }

    public function logout(Request $r){
        $this->log('logout', 'Signed out', $r, Auth::user());

        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect('/login');
    }

    /** Land each user where they can actually work */
    private function homeFor(User $user): string
    {
        if($user->is_admin){ return '/'; }
        if($user->hasCapability('attendance')){ return '/attendance'; }
        if($user->hasCapability('books')){ return '/books/create'; }

        return '/';
    }

    /** Sign-in events are recorded here; everything else is the LogActivity middleware */
    private function log(string $action, string $description, Request $r, ?User $user): void
    {
        try {
            ActivityLog::create([
                'user_id'     => $user?->id,
                'user_name'   => $user?->name,
                'action'      => $action,
                'description' => mb_substr($description, 0, 500),
                'method'      => $r->method(),
                'url'         => mb_substr($r->fullUrl(), 0, 500),
                'ip'          => $r->ip(),
                'created_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            // Never block sign-in or sign-out because logging failed
        }
    }
}
