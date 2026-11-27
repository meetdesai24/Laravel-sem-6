<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'phone'      => ['nullable', 'digits:10'],
            'department' => ['nullable', 'string', 'max:100'],
        ]);

        $request->user()->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update(['password' => $data['password']]);

        return back()->with('success', 'Password changed.');
    }
}
