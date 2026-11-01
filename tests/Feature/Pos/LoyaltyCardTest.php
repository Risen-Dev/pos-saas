<?php

namespace Tests\Feature\Pos;

use App\Models\Customer;
use App\Models\LoyaltyPointTransaction;
use App\Models\Order;
use App\Models\Setting;
use App\Services\MidtransGateway;
use Illuminate\Support\Str;

class LoyaltyCardTest extends PosTestCase
{
    private function configureProgram(int $tenantId): void
    {
        foreach ([
            'point_mode' => 'per_investment',
            'point_rule_value' => '10000',
            'point_member_only' => '1',
            'point_redemption_points' => '100',
            'point_redemption_value' => '10000',
        ] as $key => $value) {
            Setting::updateOrCreate(['tenant_id' => $tenantId, 'key' => $key], ['value' => $value]);
        }
    }

    public function test_member_can_open_private_digital_card_from_shared_token(): void
    {
        $user = $this->shop();
        $this->configureProgram($user->tenant_id);

        $response = $this->actingAs($user)->postJson('/customers/api', [
            'name' => 'Sari Pelanggan',
            'phone' => '081234567890',
            'is_member' => true,
        ])->assertOk()->assertJsonPath('success', true);

        $customer = Customer::where('tenant_id', $user->tenant_id)->firstOrFail();
        $cardUrl = $response->json('data.loyalty_card_url');
        $this->assertNotEmpty($cardUrl);
        $this->assertNotEmpty($response->json('data.whatsapp_share_url'));
        $this->get($cardUrl)->assertOk()
            ->assertSee('Sari Pelanggan')
            ->assertSee($customer->loyalty_member_code)
            ->assertSee('Saldo poin')
            ->assertDontSee('081234567890')
            ->assertDontSee('INV-');
    }

    public function test_checkout_redeems_and_awards_points_once_and_updates_card(): void
    {
        $user = $this->shop();
        $this->configureProgram($user->tenant_id);
        $product = $this->product($user, ['sell_price' => 30000]);
        $this->shift($user);
        $customer = Customer::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Member Kasir',
            'phone' => '081234567890',
            'is_member' => true,
            'points' => 100,
        ]);
        $customer->ensureLoyaltyCard();

        $payload = $this->checkout($product, [
            'checkout_key' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'points_to_redeem' => 100,
            'grand_total' => 20000,
            'paid_amount' => 20000,
        ]);

        $response = $this->actingAs($user)->postJson('/pos', $payload)->assertOk()
            ->assertJsonPath('payment_status', 'paid')
            ->assertJsonPath('grand_total', 20000)
            ->assertJsonPath('loyalty_discount', 10000)
            ->assertJsonPath('points_redeemed', 100)
            ->assertJsonPath('points_awarded', 2)
            ->assertJsonPath('points_balance', 2);

        $this->actingAs($user)->postJson('/pos', $payload)->assertOk()
            ->assertJsonPath('order_id', $response->json('order_id'));

        $order = Order::findOrFail($response->json('order_id'));
        $this->assertSame(2, (int) $customer->fresh()->points);
        $this->assertSame(100, (int) $order->points_redeemed);
        $this->assertSame(10000, (int) $order->loyalty_discount);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('loyalty_point_transactions', 2);
        $this->assertDatabaseHas('loyalty_point_transactions', [
            'order_id' => $order->id,
            'type' => 'redeemed',
            'points_delta' => -100,
        ]);
        $this->assertDatabaseHas('loyalty_point_transactions', [
            'order_id' => $order->id,
            'type' => 'earned',
            'points_delta' => 2,
        ]);

        $this->get(route('loyalty.card', $customer->loyalty_card_token))
            ->assertOk()->assertSee('Saldo poin')->assertSee('2');
    }

    public function test_redemption_rejects_non_members_and_insufficient_balance(): void
    {
        $user = $this->shop();
        $this->configureProgram($user->tenant_id);
        $product = $this->product($user, ['sell_price' => 30000]);
        $this->shift($user);
        $customer = Customer::create(['tenant_id' => $user->tenant_id, 'name' => 'Pelanggan', 'points' => 0]);

        $this->actingAs($user)->postJson('/pos', $this->checkout($product, [
            'customer_id' => $customer->id,
            'points_to_redeem' => 100,
            'grand_total' => 20000,
            'paid_amount' => 20000,
        ]))->assertUnprocessable();

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(0, (int) $customer->fresh()->points);
    }

    public function test_expired_qris_releases_reserved_points_only_once(): void
    {
        config(['services.midtrans.server_key' => 'loyalty-test-key']);
        $user = $this->shop();
        $this->configureProgram($user->tenant_id);
        $product = $this->product($user, ['sell_price' => 30000]);
        $this->shift($user);
        $customer = Customer::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Member QRIS',
            'phone' => '081234567890',
            'is_member' => true,
            'points' => 100,
        ]);
        $this->mock(MidtransGateway::class, fn ($mock) => $mock->shouldReceive('charge')->once()->andReturn('https://example.test/qr'));

        $checkout = $this->checkout($product, [
            'checkout_key' => (string) Str::uuid(),
            'payment_method' => 'midtrans',
            'payment_status' => 'unpaid',
            'paid_amount' => 0,
            'customer_id' => $customer->id,
            'points_to_redeem' => 100,
            'grand_total' => 20000,
        ]);
        $this->actingAs($user)->postJson('/pos', $checkout)->assertOk()->assertJsonPath('payment_status', 'unpaid');
        $order = Order::firstOrFail();
        $this->assertSame(0, (int) $customer->fresh()->points);

        $amount = number_format((float) $order->grand_total, 2, '.', '');
        $expired = [
            'order_id' => $order->invoice_number,
            'transaction_status' => 'expire',
            'status_code' => '200',
            'gross_amount' => $amount,
            'signature_key' => hash('sha512', $order->invoice_number.'200'.$amount.'loyalty-test-key'),
        ];
        $this->postJson('/api/midtrans/callback', $expired)->assertOk();
        $this->postJson('/api/midtrans/callback', $expired)->assertOk();

        $this->assertSame(100, (int) $customer->fresh()->points);
        $this->assertSame('cancelled', $order->fresh()->order_status);
        $this->assertDatabaseCount('loyalty_point_transactions', 2);
        $this->assertDatabaseHas('loyalty_point_transactions', [
            'order_id' => $order->id,
            'type' => 'redemption_reversed',
            'points_delta' => 100,
        ]);
    }
}
