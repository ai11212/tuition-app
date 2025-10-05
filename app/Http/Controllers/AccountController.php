<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller {
    public function passwordForm(){ return view('auth.password'); }
    public function passwordSave(Request $r){
        $r->validate([
            'current_password'=>'required',
            'password'=>'required|min:6|confirmed',
        ]);
        $user = $r->user();
        if (!Hash::check($r->current_password, $user->password)){
            return back()->withErrors(['current_password'=>'Current password is incorrect']);
        }
        $user->password = Hash::make($r->password);
        $user->save();
        return back()->with('ok','Password updated successfully');
    }
}
