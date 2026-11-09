<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LoginHistoryService;
use App\Services\SimplyDataMigrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Display the authentication login view.
     */
    public function showLogin(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return Inertia::render('Auth/Login', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     * Supports login via Simply Username (NIP/Handle) or Email, with automatic Simply credential sync.
     */
    public function login(Request $request): RedirectResponse
    {
        $loginInput = trim((string)($request->input('login') ?? $request->input('email', '')));
        $request->merge([
            'login' => $loginInput,
            'email' => $loginInput,
        ]);

        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Username, NIP, atau Email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $password = (string)$request->input('password');
        $remember = $request->boolean('remember');

        // 1. Try local Sadaya database first (matched by username OR email)
        $user = User::where('username', $loginInput)
            ->orWhere('email', $loginInput)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user, $remember);
            $request->session()->regenerate();

            // Record login success with IP, User-Agent, Device, Platform
            LoginHistoryService::recordSuccess($user, $request);

            return redirect()->intended(route('home'));
        }

        // 2. Fallback: Authenticate directly against Simply database
        $simplyUser = SimplyDataMigrationService::findAndVerifySimplyUser($loginInput, $password);
        if ($simplyUser) {
            $syncedUser = SimplyDataMigrationService::syncUserFromSimplyData($simplyUser);
            Auth::login($syncedUser, $remember);
            $request->session()->regenerate();

            LoginHistoryService::recordSuccess($syncedUser, $request);

            return redirect()->intended(route('home'));
        }

        // 3. Record failed login attempt for security audit
        LoginHistoryService::recordFailed($loginInput, $request);

        return back()->withErrors([
            'login' => 'Username / Email atau Kata Sandi yang Anda masukkan salah.',
            'email' => 'Username / Email atau Kata Sandi yang Anda masukkan salah.',
        ])->onlyInput('login', 'email');
    }

    /**
     * Destroy an authenticated session and redirect to the login page.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->header('X-Inertia')) {
            return Inertia::location(route('login'));
        }

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar dari sistem.');
    }
}
