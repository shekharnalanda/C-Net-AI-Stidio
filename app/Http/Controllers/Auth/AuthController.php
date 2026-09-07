<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SecurityAuditEvent;

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
            $this->audit($request, 'auth.login_failed', 'warning', ['email_hash' => hash('sha256', strtolower($credentials['email']))]);
            return back()
                ->withErrors(['email' => 'Invalid email or password.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $request->user()->update([
            'last_login_at' => now(),
        ]);

        $this->audit($request, 'auth.login_succeeded');

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

        $this->audit($request, 'auth.registered');

        return redirect()
            ->route('studio.dashboard')
            ->with('success', 'Your 7-day free trial is now active.');
    }

    public function logout(Request $request)
    {
        $this->audit($request, 'auth.logout');
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('studio.home');
    }

    private function audit(Request $request, string $event, string $severity = 'info', array $context = []): void
    {
        try {
            SecurityAuditEvent::create([
                'user_id' => $request->user()?->id,
                'event' => $event,
                'severity' => $severity,
                'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
                'request_id' => $request->headers->get('X-Request-ID'),
                'context' => $context,
            ]);
        } catch (\Throwable) {
            // Authentication must remain available during zero-downtime migrations.
        }
    }
}
