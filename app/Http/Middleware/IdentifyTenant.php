<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request and bind tenant organization.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $organization = $this->resolveTenant($request);

        if (! $organization) {
            abort(404, 'প্রতিষ্ঠানটি খুঁজে পাওয়া যায়নি বা অনুপলব্ধ।');
        }

        if (in_array($organization->status, ['suspended', 'cancelled'])) {
            abort(403, 'দুঃখিত, এই প্রতিষ্ঠানটির প্ল্যাটফর্ম সেবা বর্তমানে স্থগিত রয়েছে। অনুগ্রহ করে কর্তৃপক্ষের সাথে যোগাযোগ করুন।');
        }

        // Bind tenant to static context and session
        TenantContext::setTenant($organization);
        session(['current_organization_id' => $organization->id]);

        // Share tenant branding with Inertia
        Inertia::share('tenant', [
            'id' => $organization->id,
            'name' => $organization->name,
            'slug' => $organization->slug,
            'subdomain' => $organization->subdomain,
            'type' => $organization->type,
            'branding' => $organization->branding ?? [],
            'plan' => $organization->plan,
            'seat_limit' => $organization->seat_limit,
            'used_seats' => $organization->used_seats,
        ]);

        return $next($request);
    }

    /**
     * Resolve organization from route param, host/subdomain, custom domain, header, or session.
     */
    protected function resolveTenant(Request $request): ?Organization
    {
        // 1. Route parameter (e.g. /org/{organization}/...)
        $routeParam = $request->route('organization') ?: $request->route('subdomain');
        if ($routeParam) {
            if ($routeParam instanceof Organization) {
                return $routeParam;
            }

            return Organization::where('subdomain', $routeParam)
                ->orWhere('slug', $routeParam)
                ->orWhere('id', is_numeric($routeParam) ? (int) $routeParam : 0)
                ->first();
        }

        // 2. HTTP Custom Header (for API / Test suites)
        $headerSubdomain = $request->header('X-Organization-Subdomain');
        if ($headerSubdomain) {
            return Organization::where('subdomain', $headerSubdomain)->first();
        }

        $headerId = $request->header('X-Organization-Id');
        if ($headerId && is_numeric($headerId)) {
            return Organization::find((int) $headerId);
        }

        // 3. Subdomain / Host detection
        $host = $request->getHost();

        // Check custom domain first (e.g. lms.darululoom.edu.bd)
        $customDomainOrg = Organization::where('custom_domain', $host)->first();
        if ($customDomainOrg) {
            return $customDomainOrg;
        }

        // Check subdomain (e.g. darululoom.taallumbd.com or darululoom.localhost)
        $parts = explode('.', $host);
        if (count($parts) >= 2) {
            $subdomain = $parts[0];
            if (! in_array($subdomain, ['www', 'api', 'admin', 'app', 'taallumbd', 'localhost', '127'])) {
                $subdomainOrg = Organization::where('subdomain', $subdomain)->first();
                if ($subdomainOrg) {
                    return $subdomainOrg;
                }
            }
        }

        // 4. Fallback to active session
        $sessionId = session('current_organization_id');
        if ($sessionId) {
            return Organization::find($sessionId);
        }

        return null;
    }
}
