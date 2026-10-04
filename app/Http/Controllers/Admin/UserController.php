<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLoggerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->is('admin/students*') ? 'student' : $request->role;

        return Inertia::render('Admin/Users', [
            'users' => User::when($role, fn ($q, $r) => $q->where('role', $r))
                ->when($request->search, fn ($q, $s) => $q->where(function ($query) use ($s) {
                    $query->where('name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%");
                }))
                ->latest()->paginate(20)->withQueryString(),
            'filters' => ['role' => $role, 'search' => $request->search],
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $oldRole = $user->role;
        $validated = $request->validate(['role' => 'required|in:admin,instructor,student']);
        $user->update($validated);

        AuditLoggerService::log(
            action: 'user.role_updated',
            modelType: User::class,
            modelId: $user->id,
            payload: [
                'target_user' => $user->email,
                'old_role' => $oldRole,
                'new_role' => $validated['role'],
            ]
        );

        return back()->with('success', 'ভূমিকা আপডেট হয়েছে।');
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 422, 'নিজেকে ডিলিট করা যাবে না।');

        AuditLoggerService::log(
            action: 'user.deleted',
            modelType: User::class,
            modelId: $user->id,
            payload: [
                'deleted_user_email' => $user->email,
                'deleted_user_name' => $user->name,
                'deleted_user_role' => $user->role,
            ]
        );

        $user->delete();

        return back()->with('success', 'ইউজার ডিলিট হয়েছে।');
    }
}
