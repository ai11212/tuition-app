<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller{
    public function showLogin(){ return view('auth.login'); }
    public function login(Request $r){
        $cred = $r->only('email','password');
        if(Auth::attempt($cred)){ $r->session()->regenerate(); return redirect('/'); }
        return back()->withErrors(['email'=>'Invalid credentials.']);
    }
    public function logout(Request $r){
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect('/login');
    }
}
