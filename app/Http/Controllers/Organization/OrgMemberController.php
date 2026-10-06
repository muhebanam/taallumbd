<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\OrganizationMember;
use App\Services\EnterpriseLmsService;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrgMemberController extends Controller
{
    public function __construct(
        protected EnterpriseLmsService $lmsService
    ) {}

    /**
     * List organization members with filters.
     */
    public function index(Request $request): Response
    {
        $role = $request->query('role');
        $search = $request->query('search');

        $query = OrganizationMember::with(['user', 'guardian'])
            ->latest('joined_at');

        if ($role && in_array($role, ['org_admin', 'teacher', 'student', 'guardian'], true)) {
            $query->where('role', $role);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $members = $query->paginate(20)->withQueryString();
        $tenant = TenantContext::getTenant();

        return Inertia::render('Organization/Members', [
            'members' => $members,
            'filters' => [
                'role' => $role,
                'search' => $search,
            ],
            'seat_info' => [
                'seat_limit' => $tenant->seat_limit,
                'used_seats' => $tenant->used_seats,
                'available_seats' => max(0, $tenant->seat_limit - $tenant->used_seats),
            ],
        ]);
    }

    /**
     * Store a single new member.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'role' => 'required|in:org_admin,teacher,student,guardian',
            'id_number' => 'nullable|string|max:64',
            'guardian_user_id' => 'nullable|exists:users,id',
        ]);

        $tenant = TenantContext::getTenant();

        $this->lmsService->addMember(
            $tenant,
            [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ],
            $validated['role'],
            $validated['id_number'] ?? null,
            $validated['guardian_user_id'] ?? null
        );

        return back()->with('success', 'সদস্য সফলভাবে যুক্ত করা হয়েছে।');
    }

    /**
     * Bulk import members via CSV file or CSV text content.
     */
    public function bulkImport(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant();

        $csvContent = '';
        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:5120',
                'default_role' => 'nullable|in:student,teacher,guardian',
            ]);
            $csvContent = file_get_contents($request->file('file')->getRealPath());
        } elseif ($request->filled('csv_text')) {
            $request->validate([
                'csv_text' => 'required|string',
                'default_role' => 'nullable|in:student,teacher,guardian',
            ]);
            $csvContent = $request->input('csv_text');
        } else {
            return back()->withErrors(['file' => 'অনুগ্রহ করে CSV ফাইল আপলোড করুন অথবা টেক্সট পেস্ট করুন।']);
        }

        $defaultRole = $request->input('default_role', 'student');
        $result = $this->lmsService->bulkImportMembers($tenant, $csvContent, $defaultRole);

        $msg = "মোট {$result['imported']} জন সদস্য সফলভাবে যুক্ত করা হয়েছে।";
        if (! empty($result['errors'])) {
            $msg .= ' কিছু ত্রুটি: '.implode('; ', array_slice($result['errors'], 0, 3));
        }

        return back()->with('success', $msg);
    }

    /**
     * Remove or deactivate a member.
     */
    public function destroy(Request $request, mixed $organization, OrganizationMember $member): RedirectResponse
    {
        $tenant = TenantContext::getTenant();

        // Enforce tenant boundary
        if ($member->organization_id !== $tenant->id) {
            abort(403);
        }

        $member->delete();
        $tenant->syncUsedSeats();

        return back()->with('success', 'সদস্য অপসারণ করা হয়েছে।');
    }
}
