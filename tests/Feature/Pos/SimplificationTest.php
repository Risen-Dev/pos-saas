<?php

namespace Tests\Feature\Pos;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Str;

class SimplificationTest extends PosTestCase
{
    private function starter(User $user): void
    {
        $plan = Plan::create(['slug' => 'starter', 'name' => 'Starter', 'price' => 0, 'max_products' => 100, 'max_users' => 1, 'max_outlets' => 1]);
        Subscription::withoutGlobalScopes()->where('tenant_id', $user->tenant_id)->update(['plan_id' => $plan->id]);
    }

    public function test_first_setup_and_additional_business_have_distinct_pages_and_safe_entry_points(): void
    {
        $new = User::factory()->create(['role' => 'admin']);
        $this->actingAs($new)->get('/setup-business')->assertOk()->assertSee('BISNIS PERTAMA')->assertDontSee('Sudah punya akun');
        $this->get('/tenants/create')->assertRedirect(route('tenants.create'));
        $owner = $this->shop();
        $this->actingAs($owner);
        $owner->tenant->currentPlan()->update(['max_outlets' => 2]);
        $this->actingAs($owner)->get('/setup-business')->assertRedirect(route('tenants.add'));
        $this->get('/tenants/create')->assertOk()->assertSee('Tambah bisnis')->assertSee('Kembali ke bisnis saya')->assertDontSee('LANGKAH 2')->assertDontSee('Sudah punya akun');
        $this->get('/tenants')->assertOk()->assertSee(route('tenants.add'));
    }

    public function test_additional_business_preserves_previous_business_and_enforces_outlet_quota(): void
    {
        $user = $this->shop();
        $user->tenant->currentPlan()->update(['max_outlets' => 2]);
        $previous = $user->tenant_id;
        $payload = ['name' => 'Kedai Kedua', 'business_type' => 'food', 'email' => $user->email, 'phone' => '081234', 'address' => 'Bandung'];
        $this->actingAs($user)->post('/tenants', $payload)->assertRedirect(route('getting-started'));
        $this->assertNotEquals($previous, $user->fresh()->tenant_id);
        $this->assertDatabaseHas('tenants', ['id' => $previous, 'user_id' => $user->id]);
        $this->postJson('/tenants', $payload)->assertUnprocessable();
        $this->assertSame(2, $user->tenants()->count());
    }

    public function test_minimal_catalog_generates_sku_and_category_and_can_create_category_inline(): void
    {
        $user = $this->shop();
        $data = ['product_name' => 'Teh', 'sell_price' => 5000, 'type' => 'product', 'is_active' => 1];
        $this->actingAs($user)->post('/products', $data)->assertRedirect(route('products.index'));
        $item = Product::firstOrFail();
        $this->assertStringStartsWith('ITEM-', $item->sku);
        $this->assertSame('Umum', $item->category->name);
        $this->assertFalse((bool) $item->manage_stock);
        $this->post('/products', $data + ['category_name' => 'Minuman'])->assertRedirect();
        $this->assertSame(2, Category::count());
        $foreign = $this->product($this->shop());
        $this->postJson('/products', $data + ['category_id' => $foreign->category_id])->assertUnprocessable();
    }

    public function test_starter_supports_customers_credit_and_more_than_one_hundred_sales_but_not_paid_features(): void
    {
        $user = $this->shop();
        $this->starter($user);
        $product = $this->product($user, ['manage_stock' => false]);
        $this->shift($user);
        $this->actingAs($user);
        $this->post('/customers', ['name' => 'Tetangga', 'is_member' => 1])->assertRedirect();
        $customer = Customer::firstOrFail();
        $this->assertFalse((bool) $customer->is_member);
        for ($i = 0; $i < 101; $i++) {
            $this->postJson('/pos', $this->checkout($product))->assertOk();
        }
        $this->postJson('/pos', $this->checkout($product, ['customer_id' => $customer->id, 'payment_status' => 'unpaid', 'paid_amount' => 0]))->assertOk();
        $this->assertEquals(10000, $customer->fresh()->total_debt);
        $this->postJson('/pos', $this->checkout($product, ['payment_method' => 'midtrans']))->assertForbidden();
        $this->postJson('/settings/update-points', ['point_mode' => 'flat', 'point_rule_value' => 10])->assertForbidden();
        $this->get('/customers')->assertOk();
        $this->get('/settings')->assertOk()->assertDontSee('name="point_mode"', false);
    }

    public function test_manual_receipts_require_confirmation_and_do_not_change_cash_or_platform_wallet(): void
    {
        $user = $this->shop();
        $product = $this->product($user, ['manage_stock' => true]);
        $shift = $this->shift($user);
        $this->actingAs($user);
        foreach (['transfer', 'qris_manual'] as $method) {
            $data = $this->checkout($product, ['payment_method' => $method]);
            $this->postJson('/pos', $data)->assertUnprocessable();
            $this->postJson('/pos', $data + ['manual_confirmed' => false])->assertUnprocessable();
            $data += ['manual_confirmed' => true, 'payment_reference' => 'REF-TEST'];
            $this->postJson('/pos', $data)->assertOk();
            $this->postJson('/pos', $data)->assertOk();
            $order = Order::where('payment_method', $method)->firstOrFail();
            $this->assertSame($user->id, $order->payment_verified_by);
            $this->assertSame('REF-TEST', $order->payment_reference);
            $this->assertNotNull($order->payment_verified_at);
            $this->get('/orders/'.$order->id)->assertOk()->assertSee('Pembayaran diperiksa manual');
            $this->get('/orders/'.$order->id.'/print')->assertOk()->assertSee('Dana diperiksa manual');
            $this->postJson('/pos', array_replace($data, ['payment_reference' => 'changed']))->assertConflict();
            $this->postJson('/pos', array_replace($data, ['checkout_key' => (string) Str::uuid(), 'paid_amount' => 9999]))->assertUnprocessable();
            $this->postJson('/pos', array_replace($data, ['checkout_key' => (string) Str::uuid(), 'payment_status' => 'unpaid']))->assertUnprocessable();
        }
        $this->assertDatabaseCount('orders', 2);
        $this->assertDatabaseCount('cash_entries', 0);
        $this->assertDatabaseCount('tenant_wallets', 0);
        $this->assertEquals(8, $product->fresh()->stock);
        $this->getJson('/shifts/summary')->assertJsonPath('cash_expected', (int) $shift->cash_start);
        $this->get('/dashboard')->assertOk()->assertViewHas('overview', fn ($summary) => $summary['manual_receipts'] === 20000.0);
        $this->get('/reports')->assertOk()->assertSee('Transfer bank (manual)')->assertSee('QRIS toko (manual)');
    }

    public function test_quick_stock_is_tenant_scoped_weighted_and_retry_safe(): void
    {
        $user = $this->shop();
        $product = $this->product($user, ['stock' => 10, 'cost_price' => 4000, 'manage_stock' => true]);
        $foreign = $this->product($this->shop(), ['manage_stock' => true]);
        $this->actingAs($user);
        $data = ['operation_key' => (string) Str::uuid(), 'product_id' => $product->id, 'quantity' => 10, 'purchase_price' => 6000];
        $this->post('/inventory/quick', $data)->assertRedirect();
        $this->post('/inventory/quick', $data)->assertRedirect();
        $this->assertEquals(20, $product->fresh()->stock);
        $this->assertEquals(5000, $product->fresh()->cost_price);
        $this->assertDatabaseCount('stock_movements', 1);
        $this->assertDatabaseCount('cash_entries', 0);
        $this->postJson('/inventory/quick', array_replace($data, ['quantity' => 20]))->assertUnprocessable();
        $this->postJson('/inventory/quick', array_replace($data, ['operation_key' => (string) Str::uuid(), 'product_id' => $foreign->id]))->assertNotFound();
        $this->get('/inventory?search=Kopi')->assertOk()->assertSee('Tambah stok cepat');
    }

    public function test_quick_stock_without_cost_preserves_cost_and_variant_uses_existing_workflow(): void
    {
        $user = $this->shop();
        $product = $this->product($user, ['manage_stock' => true, 'cost_price' => 4000]);
        $this->actingAs($user)->post('/inventory/quick', ['operation_key' => (string) Str::uuid(), 'product_id' => $product->id, 'quantity' => 1])->assertRedirect();
        $this->assertEquals(4000, $product->fresh()->cost_price);
        $product->variants()->create(['sku' => 'VAR-LARGE', 'name' => 'Besar', 'price' => 12000, 'stock' => 4]);
        $this->postJson('/inventory/quick', ['operation_key' => (string) Str::uuid(), 'product_id' => $product->id, 'quantity' => 1])->assertUnprocessable();
    }

    public function test_navigation_matches_modules_and_cashier_does_not_gain_backoffice_access(): void
    {
        $user = $this->shop();
        $user->tenant->update(['business_type' => 'service', 'business_modules' => ['services']]);
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertDontSee('href="'.route('inventory.index').'"', false)->assertSee('Pengerjaan jasa');
        $this->get('/workspace/settings')->assertOk()->assertSee('Bisnis saya');
        $cashier = $this->shop('kasir');
        $this->actingAs($cashier)->get('/tenants/create')->assertRedirect('/pos');
        $this->get('/workspace/settings')->assertRedirect('/pos');
        $this->post('/inventory/quick', [])->assertRedirect('/pos');
    }
}
