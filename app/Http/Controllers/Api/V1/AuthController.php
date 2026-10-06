<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new student user.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'device_name' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'student',
        ]);

        event(new Registered($user));

        $deviceName = $request->device_name ?: ('mobile-'.Str::random(6));
        $abilities = $this->getAbilitiesForRole($user->role);
        $token = $user->createToken($deviceName, $abilities)->plainTextToken;

        $responseData = [
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
            'abilities' => $abilities,
        ];

        return response()->json([
            'success' => true,
            'message' => 'নিবন্ধন সফলভাবে সম্পন্ন হয়েছে।',
            'data' => $responseData,
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
            'abilities' => $abilities,
        ], 201);
    }

    /**
     * Login user and issue API bearer token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['প্রদত্ত ইমেইল বা পাসওয়ার্ডটি সঠিক নয়।'],
            ]);
        }

        $deviceName = $request->device_name ?: ('device-'.Str::random(6));
        $abilities = $this->getAbilitiesForRole($user->role);
        $token = $user->createToken($deviceName, $abilities)->plainTextToken;

        $responseData = [
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
            'abilities' => $abilities,
        ];

        return response()->json([
            'success' => true,
            'message' => 'সফলভাবে লগইন হয়েছে।',
            'data' => $responseData,
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
            'abilities' => $abilities,
        ]);
    }

    /**
     * Revoke current access token (logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'সফলভাবে লগআউট সম্পন্ন হয়েছে।',
        ]);
    }

    /**
     * Get authenticated user profile & permissions.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing(['teacher', 'teacherWallet']);

        $data = (new UserResource($user))->toArray($request);
        $data['abilities'] = $user->currentAccessToken()?->abilities ?? [];
        $data['enrollments_count'] = $user->enrollments()->count();

        if ($user->isInstructor() && $user->teacherWallet) {
            $data['wallet'] = [
                'balance_bdt' => $user->teacherWallet->balance_bdt,
                'pending_balance_bdt' => $user->teacherWallet->pending_balance_bdt,
                'currency' => $user->teacherWallet->currency,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'user' => $data,
        ]);
    }

    /**
     * Send password reset link / OTP.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'পাসওয়ার্ড রিসেট লিংক আপনার ইমেইলে পাঠানো হয়েছে।',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'পাসওয়ার্ড রিসেট লিংক পাঠানো সম্ভব হয়নি।',
        ], 400);
    }

    /**
     * Reset user password with token.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'আপনার পাসওয়ার্ড সফলভাবে পরিবর্তিত হয়েছে। নতুন পাসওয়ার্ড দিয়ে লগইন করুন।',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'পাসওয়ার্ড রিসেট টোকেনটি অবৈধ বা মেয়াদোত্তীর্ণ।',
        ], 400);
    }

    /**
     * Resend verification email notification.
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'আপনার ইমেইল ইতিমধ্যে ভেরিফাইড।',
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'ভেরিফিকেশন লিংক পুনরায় আপনার ইমেইলে পাঠানো হয়েছে।',
        ]);
    }

    /**
     * Get Sanctum token abilities based on user role.
     *
     * @return array<string>
     */
    private function getAbilitiesForRole(string $role): array
    {
        return match ($role) {
            'admin' => ['*'],
            'instructor' => [
                'instructor',
                'student',
                'courses:manage',
                'lessons:manage',
                'wallet:read',
                'payout:create',
                'fatawa:answer',
            ],
            'editor' => [
                'editor',
                'student',
                'content:review',
                'articles:manage',
            ],
            'scholar_reviewer' => [
                'scholar_reviewer',
                'student',
                'content:review',
                'fatawa:review',
            ],
            default => [
                'student',
                'courses:read',
                'lessons:read',
                'quizzes:submit',
                'progress:write',
                'fatawa:create',
                'certificates:read',
            ],
        };
    }
}
