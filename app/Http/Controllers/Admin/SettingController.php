<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    /**
     * Display payment settings configuration.
     */
    public function payments(): Response
    {
        $defaultMethods = config('payments.gateways.manual.methods', []);
        $settings = [];

        foreach (array_keys($defaultMethods) as $method) {
            $settings[$method] = [
                'name' => Setting::get("payment_manual_{$method}_name", $defaultMethods[$method]['name'] ?? $method),
                'number' => Setting::get("payment_manual_{$method}_number", $defaultMethods[$method]['number'] ?? ''),
                'type' => Setting::get("payment_manual_{$method}_type", $defaultMethods[$method]['type'] ?? 'personal'),
                'instructions' => Setting::get("payment_manual_{$method}_instructions", $defaultMethods[$method]['instructions'] ?? ''),
                'enabled' => filter_var(Setting::get("payment_manual_{$method}_enabled", 'true'), FILTER_VALIDATE_BOOLEAN),
            ];
        }

        return Inertia::render('Admin/PaymentSettings', [
            'paymentSettings' => $settings,
        ]);
    }

    /**
     * Update manual payment gateway settings.
     */
    public function updatePayments(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.number' => 'required|string|max:20',
            'settings.*.type' => 'required|string|in:personal,merchant,agent',
            'settings.*.instructions' => 'nullable|string',
            'settings.*.enabled' => 'boolean',
        ]);

        foreach ($validated['settings'] as $method => $data) {
            Setting::set("payment_manual_{$method}_number", $data['number'], 'payments');
            Setting::set("payment_manual_{$method}_type", $data['type'], 'payments');
            Setting::set("payment_manual_{$method}_instructions", $data['instructions'] ?? '', 'payments');
            Setting::set("payment_manual_{$method}_enabled", $data['enabled'] ? 'true' : 'false', 'payments');
        }

        AuditLoggerService::log(
            action: 'settings.payments_updated',
            payload: $validated['settings']
        );

        return back()->with('success', 'পেমেন্ট গেটওয়ে সেটিংস সফলভাবে আপডেট করা হয়েছে।');
    }
}
