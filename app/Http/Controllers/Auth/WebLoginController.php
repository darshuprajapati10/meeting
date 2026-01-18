<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class WebLoginController extends Controller
{
    /**
     * Show the login form (handled by Inertia)
     */
    public function showLoginForm()
    {
        // Redirect if already authenticated
        if (auth()->check()) {
            if (auth()->user()->is_platform_admin) {
                return redirect('/admin/dashboard');
            }
            return redirect('/');
        }
        
        return Inertia::render('Auth/Login');
    }

    /**
     * Handle a login request for web routes (session-based)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Log the user in using session
        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        // Redirect to admin dashboard if user is platform admin, otherwise redirect to home
        if ($user->is_platform_admin) {
            return redirect()->intended('/admin/dashboard');
        }

        return redirect()->intended('/');
    }

    /**
     * Log the user out
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

