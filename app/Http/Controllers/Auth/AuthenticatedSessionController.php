<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLoggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            AuditLoggerService::log(
                action: 'auth.login_failed',
                payload: ['email' => $credentials['email']]
            );

            return back()->withErrors(['email' => 'ইমেইল বা পাসওয়ার্ড সঠিক নয়।'])->onlyInput('email');
        }

        AuditLoggerService::log(
            action: 'auth.login_success',
            modelType: User::class,
            modelId: Auth::id(),
            payload: ['email' => $credentials['email'], 'role' => Auth::user()->role]
        );

        $request->session()->regenerate();

        // Role-based redirect
        return redirect()->intended(match ($request->user()->role) {
            'admin' => route('admin.dashboard'),
            'instructor' => route('instructor.dashboard'),
            default => route('dashboard'),
        });
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
