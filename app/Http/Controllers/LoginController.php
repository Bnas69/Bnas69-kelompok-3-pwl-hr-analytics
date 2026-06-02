<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (session('hr_logged_in')) {
            return redirect()->route('dashboard');
        }

        return view('login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $validUsername = config('app.login_username');
        $validPassword = config('app.login_password');

        if ($credentials['username'] !== $validUsername || $credentials['password'] !== $validPassword) {
            return back()
                ->withErrors(['login' => 'Username atau password salah.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();
        $request->session()->put('hr_logged_in', true);
        $request->session()->put('hr_username', $credentials['username']);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['hr_logged_in', 'hr_username']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
