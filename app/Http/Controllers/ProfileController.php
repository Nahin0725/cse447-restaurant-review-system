<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        ini_set('max_execution_time', 300); // Increase timeout for crypto operations

        $user = $request->attributes->get('auth_user');

        // Pre-decrypt user fields to avoid slow decryption in view
        $userData = [
            'user_id' => $user->user_id,
            'username' => $user->username,
            'email' => $user->email,
            'contact_info' => $user->contact_info,
            'role' => $user->role,
            'created_at' => $user->created_at,
        ];

        return view('profile.show', [
            'user' => $userData,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->attributes->get('auth_user');

        $request->validate([
            'username' => [
                'required',
                'string',
                'size:10',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).+$/',
                Rule::unique('users', 'username_hash')->ignore($user->user_id, 'user_id'),
            ],
            'email' => [
                'required',
                'email',
                'max:180',
                Rule::unique('users', 'email_hash')->ignore($user->user_id, 'user_id'),
            ],
            'contact_info' => [
                'required',
                'digits:11',
                Rule::unique('users', 'contact_hash')->ignore($user->user_id, 'user_id'),
            ],
            'password' => 'nullable|string|min:12',
        ]);

        // Update user fields - accessors will handle encryption
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->contact_info = $request->input('contact_info');

        if ($request->filled('password')) {
            $user->password = $request->input('password');
        }

        $user->save();

        return redirect('/profile')->with('status', 'Profile updated successfully.');
    }
}
