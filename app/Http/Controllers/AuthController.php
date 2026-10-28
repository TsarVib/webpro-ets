<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function create(Request $request): RedirectResponse
    {
        $this->lowercaseIdentity($request);

        $data = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:255', 'unique:accounts,username'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:accounts,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        Account::create([
            'username' => $data['username'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
        ]);

        return redirect()->route('login');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = mb_strtolower($data['login']);
        $credentials = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? ['email' => $login]
            : ['username' => $login];

        if (Auth::attempt([...$credentials, 'password' => $data['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();
            Account::query()
                ->where($credentials)
                ->update(['last_login' => now()]);

            return redirect()->intended(route('home'));
        }

        return back()->withErrors(['login' => 'These credentials do not match our records.'])->onlyInput('login');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
