<?php
namespace App\Http\Controllers;
use Illuminate\Http\{RedirectResponse,Request};
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
class AuthController extends Controller {
    public function showLogin(): View|RedirectResponse { if(Auth::check()) return redirect()->route('reportes.index'); return view('auth.login'); }
    public function login(Request $request): RedirectResponse {
        $credentials=$request->validate(['email'=>'required|email','password'=>'required|string']);
        if(Auth::attempt($credentials,$request->boolean('remember'))) { $request->session()->regenerate(); return redirect()->intended(route('reportes.index')); }
        return back()->withInput($request->only('email'))->withErrors(['email'=>'Las credenciales no son correctas.']);
    }
    public function logout(Request $request): RedirectResponse {
        Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}