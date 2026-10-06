<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLoggerService;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TwoFactorController extends Controller
{
    /**
     * Show the 2FA setup screen (QR code & secret).
     */
    public function setup(Request $request)
    {
        $user = $request->user();

        // If already confirmed, redirect to dashboard or show management
        if ($user->hasTwoFactorEnabled() && $request->session()->get('two_factor_verified')) {
            return redirect()->intended($this->dashboardUrl($user));
        }

        $secret = $user->two_factor_secret;
        $recoveryCodes = $user->two_factor_recovery_codes ?? [];

        if (empty($secret)) {
            $secret = TotpService::generateSecret();
            $recoveryCodes = TotpService::generateRecoveryCodes();

            $user->update([
                'two_factor_secret' => $secret,
                'two_factor_recovery_codes' => $recoveryCodes,
            ]);
        }

        $qrUri = TotpService::getQrCodeUri('Taallum BD', $user->email, $secret);

        return Inertia::render('Auth/TwoFactorSetup', [
            'secret' => $secret,
            'qrUri' => $qrUri,
            'recoveryCodes' => $recoveryCodes,
            'isMandatory' => $user->requiresTwoFactor(),
        ]);
    }

    /**
     * Confirm 2FA setup with initial 6-digit TOTP code.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        $code = $request->input('code');

        if (! $user->two_factor_secret || ! TotpService::verifyCode($user->two_factor_secret, $code)) {
            return back()->withErrors([
                'code' => 'প্রদত্ত ৬-সংখ্যার যাচাইকরণ কোডটি সঠিক নয়। অনুগ্রহ করে আপনার প্রমাণীকরণ অ্যাপে দেখে সঠিক কোড দিন।',
            ]);
        }

        $user->update([
            'two_factor_confirmed_at' => now(),
        ]);

        $request->session()->put('two_factor_verified', true);

        AuditLoggerService::log(
            action: 'auth.2fa_enabled',
            modelType: User::class,
            modelId: $user->id,
            payload: ['email' => $user->email, 'role' => $user->role]
        );

        return redirect()->to($this->dashboardUrl($user))->with(
            'success',
            '২-ধাপ যাচাইকরণ (2FA) সফলভাবে সক্রিয় করা হয়েছে!'
        );
    }

    /**
     * Show the 2FA challenge screen during login.
     */
    public function challenge(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($request->session()->get('two_factor_verified')) {
            return redirect()->intended($this->dashboardUrl($user));
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    /**
     * Verify the 2FA code or recovery code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $code = $request->input('code');
        $recoveryCode = $request->input('recovery_code');

        if ($code) {
            if ($user->two_factor_secret && TotpService::verifyCode($user->two_factor_secret, $code)) {
                $request->session()->put('two_factor_verified', true);

                AuditLoggerService::log(
                    action: 'auth.2fa_verified_totp',
                    modelType: User::class,
                    modelId: $user->id
                );

                return redirect()->intended($this->dashboardUrl($user));
            }

            return back()->withErrors([
                'code' => 'কোডটি সঠিক নয় বা এর মেয়াদ শেষ হয়ে গেছে।',
            ]);
        }

        if ($recoveryCode) {
            $recoveryCode = strtoupper(trim($recoveryCode));
            $codes = $user->two_factor_recovery_codes ?? [];

            if (in_array($recoveryCode, $codes, true)) {
                // Remove used recovery code
                $updatedCodes = array_values(array_diff($codes, [$recoveryCode]));
                $user->update(['two_factor_recovery_codes' => $updatedCodes]);

                $request->session()->put('two_factor_verified', true);

                AuditLoggerService::log(
                    action: 'auth.2fa_verified_recovery',
                    modelType: User::class,
                    modelId: $user->id,
                    payload: ['remaining_codes' => count($updatedCodes)]
                );

                return redirect()->intended($this->dashboardUrl($user))->with(
                    'warning',
                    'রিকভারি কোড ব্যবহার করা হয়েছে। অবশিষ্ট রিকভারি কোড: '.count($updatedCodes)
                );
            }

            return back()->withErrors([
                'recovery_code' => 'প্রদত্ত রিকভারি কোডটি সঠিক নয়।',
            ]);
        }

        return back()->withErrors([
            'code' => 'যাচাইকরণ কোড অথবা রিকভারি কোড প্রদান করুন।',
        ]);
    }

    private function dashboardUrl(User $user): string
    {
        return match ($user->role) {
            'admin' => route('admin.dashboard'),
            'instructor' => route('instructor.dashboard'),
            default => route('dashboard'),
        };
    }
}
