<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showForm() { return view('auth.login'); }

    public function login(Request $request)
    {
        $request->validate(['email'=>'required|email','password'=>'required']);
        $remember = $request->boolean('remember');

        if (Auth::attempt(['email'=>$request->email,'password'=>$request->password,'role'=>'employee'], $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email'=>'Invalid credentials.'])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
