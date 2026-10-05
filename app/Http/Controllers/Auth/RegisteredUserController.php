<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'phone' => 'nullable|string|max:30',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Extract UTM attribution from session
        $utm = $request->session()->get('utm_attribution', []);
        $refCode = $request->session()->get('referral_code') ?: $request->input('ref');

        $referrer = null;
        if ($refCode) {
            $referrer = User::where('referral_code', $refCode)->first();
        }

        $user = User::create([
            ...$data,
            'role' => 'student', // registration always creates students
            'referred_by_id' => $referrer?->id,
            'utm_source' => $utm['utm_source'] ?? null,
            'utm_medium' => $utm['utm_medium'] ?? null,
            'utm_campaign' => $utm['utm_campaign'] ?? null,
            'utm_term' => $utm['utm_term'] ?? null,
            'utm_content' => $utm['utm_content'] ?? null,
        ]);

        if ($referrer) {
            Referral::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $user->id,
                'code' => $refCode,
                'reward_status' => 'pending',
                'converted_at' => now(),
            ]);
        }

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
