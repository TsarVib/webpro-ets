<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $account = $request->user();

        $data = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:255', Rule::unique('accounts', 'username')->ignore($account->id)],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('accounts', 'email')->ignore($account->id)],
            'initial_balance' => ['required', 'integer', 'min:0'],
        ]);

        $account->update($data);

        return back();
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password_hash' => Hash::make($data['password']),
        ]);

        // Other sessions and "remember me" cookies hold the old hash, so AuthenticateSession signs them out.
        Auth::logoutOtherDevices($data['password']);

        return back();
    }
}
