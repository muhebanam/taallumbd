<?php

namespace App\Http\Middleware;

use App\Models\OrganizationMember;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationMember
{
    /**
     * Handle an incoming request to verify organization membership and role.
     *
     * @param  string|null  ...$roles  Optional allowed roles (e.g. org_admin, teacher, student, guardian)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $tenant = TenantContext::getTenant();

        if (! $tenant) {
            abort(404, 'প্রতিষ্ঠান নির্ধারিত করা হয়নি।');
        }

        $user = $request->user();

        if (! $user) {
            abort(401, 'অনুগ্রহ করে লগইন করুন।');
        }

        // Platform system admin bypass for troubleshooting & administration
        if ($user->isAdmin()) {
            Inertia::share('org_member', [
                'id' => 0,
                'role' => 'org_admin',
                'is_system_admin' => true,
            ]);

            return $next($request);
        }

        // Check organization membership
        $member = OrganizationMember::withoutGlobalScopes()
            ->where('organization_id', $tenant->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (! $member) {
            abort(403, 'আপনি এই প্রতিষ্ঠানের অন্তর্ভুক্ত সদস্য নন বা আপনার অ্যাক্সেস নিষ্ক্রিয় রাখা হয়েছে।');
        }

        // Role verification if specific roles are required
        if (! empty($roles)) {
            // Support comma-separated strings inside roles array (e.g. 'org_admin,teacher')
            $allowedRoles = [];
            foreach ($roles as $roleGroup) {
                foreach (explode(',', $roleGroup) as $r) {
                    $trimmed = trim($r);
                    if ($trimmed !== '') {
                        $allowedRoles[] = $trimmed;
                    }
                }
            }

            if (! in_array($member->role, $allowedRoles, true)) {
                abort(403, 'এই সেকশনে প্রবেশের জন্য আপনার প্রয়োজনীয় অনুমতি (রোল) নেই।');
            }
        }

        // Attach member to request attributes and share with Inertia
        $request->attributes->set('org_member', $member);

        Inertia::share('org_member', [
            'id' => $member->id,
            'role' => $member->role,
            'student_id_number' => $member->student_id_number,
            'meta' => $member->meta ?? [],
        ]);

        return $next($request);
    }
}
