<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function toonLogin()
    {
        return view('auth.login');
    }

    public function inloggen(Request $request)
    {
        $request->validate([
            'email'      => 'required|email',
            'wachtwoord' => 'required',
        ]);

        if (Auth::attempt(
            ['email' => $request->email, 'password' => $request->wachtwoord],
            $request->boolean('onthoud')
        )) {
            $request->session()->regenerate();
            return redirect()->intended(route('home'));
        }

        return back()
            ->withErrors(['email' => 'E-mailadres of wachtwoord klopt niet.'])
            ->onlyInput('email');
    }

    public function uitloggen(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
