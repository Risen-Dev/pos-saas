<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerPoints;
use App\Services\CustomerStamps;
use App\Services\LoyaltyProgram;
use Illuminate\Http\Request;

class LoyaltyCardController extends Controller
{
    public function show(Request $request, string $token, CustomerPoints $points, CustomerStamps $stamps, LoyaltyProgram $program)
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{64}$/', $token), 404);

        $customer = Customer::withoutGlobalScopes()
            ->with('tenant')
            ->where('loyalty_card_token', $token)
            ->where('is_member', true)
            ->firstOrFail();

        $tenant = $customer->tenant;
        $settings = $points->settings($tenant->id);
        $programType = $program->type($settings);
        $isStampProgram = $programType === 'stamps';
        $step = max(1, (int) ($settings['point_redemption_points'] ?? 100));
        $rewardValue = max(1, (int) ($settings['point_redemption_value'] ?? 10000));
        $stampGoal = max(2, (int) ($settings['stamp_reward_goal'] ?? 8));
        $stampRewardValue = max(1, (int) ($settings['stamp_reward_value'] ?? 10000));
        $stampMinimumPurchase = max(0, (int) ($settings['stamp_minimum_purchase'] ?? 0));
        $stampBalance = $stamps->balance($customer);
        $visibleStampBalance = max(0, $stampBalance);
        $stampProgress = min($stampGoal, $visibleStampBalance % $stampGoal);
        $stampProgressPercent = min(100, (int) round(($stampProgress / $stampGoal) * 100));
        $stampRewardsAvailable = max(0, intdiv(max(0, $stampBalance), $stampGoal));
        $programActive = in_array($tenant->currentPlan()?->slug, ['growth', 'scale'], true)
            && match ($programType) {
                'stamps' => $stampGoal >= 2 && $stampMinimumPurchase >= 0 && $stampRewardValue > 0,
                'points' => ($settings['point_mode'] ?? 'disabled') !== 'disabled' && (float) ($settings['point_rule_value'] ?? 0) > 0,
                default => false,
            };

        $activity = $isStampProgram
            ? $customer->loyaltyStampTransactions()->latest('id')->limit(8)->get(['id', 'type', 'stamps_delta', 'description', 'created_at'])
            : $customer->loyaltyPointTransactions()->latest('id')->limit(8)->get(['id', 'type', 'points_delta', 'description', 'created_at']);

        return response()
            ->view('loyalty.card', compact('customer', 'tenant', 'settings', 'step', 'rewardValue', 'programType', 'isStampProgram', 'programActive', 'activity', 'stampGoal', 'stampRewardValue', 'stampMinimumPurchase', 'stampBalance', 'visibleStampBalance', 'stampProgress', 'stampProgressPercent', 'stampRewardsAvailable'))
            ->header('Cache-Control', 'private, no-store, max-age=0')
            ->header('Referrer-Policy', 'no-referrer');
    }
}
