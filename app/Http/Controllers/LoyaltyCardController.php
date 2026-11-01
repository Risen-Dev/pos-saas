<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerPoints;
use Illuminate\Http\Request;

class LoyaltyCardController extends Controller
{
    public function show(Request $request, string $token, CustomerPoints $points)
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{64}$/', $token), 404);

        $customer = Customer::withoutGlobalScopes()
            ->with('tenant')
            ->where('loyalty_card_token', $token)
            ->where('is_member', true)
            ->firstOrFail();

        $tenant = $customer->tenant;
        $settings = $points->settings($tenant->id);
        $step = max(1, (int) ($settings['point_redemption_points'] ?? 100));
        $rewardValue = max(1, (int) ($settings['point_redemption_value'] ?? 10000));
        $programActive = in_array($tenant->currentPlan()?->slug, ['growth', 'scale'], true)
            && ($settings['point_mode'] ?? 'disabled') !== 'disabled'
            && (float) ($settings['point_rule_value'] ?? 0) > 0;

        $activity = $customer->loyaltyPointTransactions()
            ->latest('id')
            ->limit(8)
            ->get(['id', 'type', 'points_delta', 'description', 'created_at']);

        return response()
            ->view('loyalty.card', compact('customer', 'tenant', 'settings', 'step', 'rewardValue', 'programActive', 'activity'))
            ->header('Cache-Control', 'private, no-store, max-age=0')
            ->header('Referrer-Policy', 'no-referrer');
    }
}
