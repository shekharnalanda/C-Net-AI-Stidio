<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Invalid email or password.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $request->user()->update([
            'last_login_at' => now(),
        ]);

        return redirect()->intended(route('studio.dashboard'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => $data['password'],
            'role' => 'user',
            'account_status' => 'active',
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addDays(7),
        ]);

        UserSubscription::create([
            'user_id' => $user->id,
            'status' => 'trial',
            'amount' => 0,
            'starts_at' => now(),
            'ends_at' => now()->addDays(7),
            'metadata' => [
                'source' => 'registration',
                'trial_days' => 7,
            ],
        ]);

        Auth::login($user);

        return redirect()
            ->route('studio.dashboard')
            ->with('success', 'Your 7-day free trial is now active.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('studio.home');
    }
}
