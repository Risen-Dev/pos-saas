<?php

namespace Tests\Feature\Pos;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Stocktake;
use App\Models\Subscription;
use App\Models\User;
use App\Services\BillingPaymentService;
use App\Services\SafeReportHtml;
use App\Services\SalesDataset;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuditRemediationTest extends PosTestCase
{
    private function invoice($user): Invoice
    {
        return Invoice::create(['tenant_id' => $user->tenant_id, 'plan_id' => Plan::where('slug', 'growth')->value('id'),
            'invoice_number' => 'INV-SAAS-'.Str::uuid(), 'amount' => 100000, 'status' => 'pending', 'due_date' => now()->addDay()]);
    }

    private function settlement(Invoice $invoice): array
    {
        return ['order_id' => $invoice->invoice_number, 'gross_amount' => '100000.00', 'transaction_status' => 'settlement', 'payment_type' => 'qris'];
    }

    public function test_webhook_creates_subscription_and_duplicate_payment_does_not_extend_again(): void
    {
        $user = $this->shop();
        Subscription::withoutGlobalScopes()->where('tenant_id', $user->tenant_id)->delete();
        $invoice = $this->invoice($user);
        config(['services.midtrans.server_key' => 'test-only']);
        $payload = $this->settlement($invoice) + ['status_code' => '200', 'signature_key' => hash('sha512', $invoice->invoice_number.'200100000.00test-only')];
        $this->postJson('/api/midtrans/callback', $payload)->assertOk();
        $subscription = Subscription::withoutGlobalScopes()->where('tenant_id', $user->tenant_id)->firstOrFail();
        $end = $subscription->end_date->toDateString();
        app(BillingPaymentService::class)->apply($invoice->id, $this->settlement($invoice));
        $this->postJson('/midtrans/callback', $payload)->assertOk();
        $this->assertSame($end, $subscription->fresh()->end_date->toDateString());
        $this->assertSame(1, Subscription::withoutGlobalScopes()->where('tenant_id', $user->tenant_id)->count());
        $this->assertNotNull($invoice->fresh()->activation_applied_at);
        $this->assertNotNull($user->tenant->currentPlan());
    }

    public function test_stale_invoice_instances_and_paid_invoice_without_activation_are_reconciled_once(): void
    {
        $user = $this->shop();
        $invoice = $this->invoice($user);
        $stale = Invoice::find($invoice->id);
        $invoice->update(['status' => 'paid', 'paid_at' => now()]);
        app(BillingPaymentService::class)->apply($invoice->id, $this->settlement($invoice));
        $end = Subscription::first()->end_date->toDateString();
        app(BillingPaymentService::class)->apply($stale->id, $this->settlement($stale));
        $this->assertSame($end, Subscription::first()->end_date->toDateString());
        $this->assertNotNull($invoice->fresh()->subscription_id);
    }

    public function test_billing_rejects_mismatched_identity_and_amount_and_ignores_fraud_challenge(): void
    {
        $user = $this->shop();
        $invoice = $this->invoice($user);
        $service = app(BillingPaymentService::class);
        $service->apply($invoice->id, array_replace($this->settlement($invoice), ['transaction_status' => 'capture', 'fraud_status' => 'challenge']));
        $this->assertSame('pending', $invoice->fresh()->status);
        foreach (['order_id' => 'wrong', 'gross_amount' => '1.00'] as $key => $value) {
            try {
                $service->apply($invoice->id, array_replace($this->settlement($invoice), [$key => $value]));
                $this->fail('Invalid billing payment accepted');
            } catch (ValidationException) {
                $this->assertSame('pending', $invoice->fresh()->status);
            }
        }
    }

    public function test_inactive_private_plan_and_inactive_session_are_blocked(): void
    {
        $user = $this->shop();
        $plan = Plan::create(['slug' => 'private', 'name' => 'Private', 'price' => 0, 'is_active' => false, 'is_public' => false]);
        $this->actingAs($user)->postJson('/billing/subscribe', ['plan_id' => $plan->id])->assertUnprocessable();
        $user->update(['status' => 'inactive']);
        $this->getJson('/pos')->assertForbidden();
        $this->assertGuest();
    }

    public function test_catalog_edit_never_overwrites_stock_from_an_intervening_sale(): void
    {
        $user = $this->shop();
        $product = $this->product($user, ['manage_stock' => true]);
        $this->shift($user);
        $this->actingAs($user)->postJson('/pos', $this->checkout($product))->assertOk();
        $this->put('/products/'.$product->id, ['category_id' => $product->category_id, 'sku' => $product->sku,
            'product_name' => 'Renamed', 'type' => 'product', 'sell_price' => 10000, 'stock' => 10, 'min_stock' => 5, 'manage_stock' => 1, 'is_active' => 1])->assertRedirect();
        $this->assertEquals(9, $product->fresh()->stock);
        $this->assertSame('Renamed', $product->fresh()->product_name);
    }

    public function test_opname_rejects_intervening_movements_even_if_stock_returns_to_original_value(): void
    {
        $user = $this->shop();
        $product = $this->product($user, ['manage_stock' => true]);
        $this->shift($user);
        $this->actingAs($user)->post('/stocktakes')->assertRedirect();
        $take = Stocktake::first();
        $line = $take->items()->first();
        $this->postJson('/pos', $this->checkout($product))->assertOk();
        $this->postJson('/inventory/adjust', ['product_id' => $product->id, 'type' => 'stock_in', 'quantity' => 1, 'note' => 'Restock'])->assertRedirect();
        $this->postJson('/stocktakes/'.$take->id.'/complete', ['counts' => [$line->id => 10]])->assertUnprocessable();
        $this->assertEquals(10, $product->fresh()->stock);
        $this->assertSame('open', $take->fresh()->status);
    }

    public function test_withdrawal_replays_one_debit_and_conflicting_payload_is_rejected(): void
    {
        $user = $this->shop();
        DB::table('tenant_wallets')->insert(['tenant_id' => $user->tenant_id, 'balance' => 100000, 'bank_name' => 'Fixture', 'account_number' => '000', 'account_name' => 'Fixture']);
        $data = ['amount' => 10000, 'idempotency_key' => (string) Str::uuid()];
        $this->actingAs($user)->postJson('/finance/withdraw', $data)->assertOk();
        $this->postJson('/finance/withdraw', $data)->assertOk();
        $this->postJson('/finance/withdraw', array_replace($data, ['amount' => 20000]))->assertStatus(409);
        $this->assertDatabaseCount('withdrawal_requests', 1);
        $this->assertDatabaseHas('tenant_wallets', ['tenant_id' => $user->tenant_id, 'balance' => 90000]);
    }

    public function test_generated_report_html_has_no_active_content_or_attributes(): void
    {
        $html = app(SafeReportHtml::class)->clean('<h3>Report</h3><script>alert(1)</script><img src=x onerror=alert(2)><p onclick="bad()">Safe</p><svg onload="bad()"></svg><table><tr><td>1</td></tr></table>');
        $this->assertStringContainsString('<h3>Report</h3>', $html);
        foreach (['<script', '<img', 'onclick', 'onerror', '<svg'] as $unsafe) {
            $this->assertStringNotContainsString($unsafe, $html);
        }
    }

    public function test_sales_dataset_uses_sold_date_and_excludes_unpaid_cancelled_orders(): void
    {
        $user = $this->shop();
        $product = $this->product($user);
        $this->shift($user);
        $this->actingAs($user)->postJson('/pos', $this->checkout($product))->assertOk();
        $order = Order::first();
        $order->update(['sold_at' => today()->subDay()]);
        $this->assertSame(0, app(SalesDataset::class)->between($user->tenant_id, today(), today()->endOfDay())->count());
        $this->assertSame(1, app(SalesDataset::class)->orders($user->tenant_id)->count());
        $order->update(['order_status' => 'cancelled']);
        $this->assertSame(0, app(SalesDataset::class)->orders($user->tenant_id)->count());
    }

    public function test_catalog_search_pagination_totals_and_customer_details_are_tenant_scoped(): void
    {
        $user = $this->shop();
        foreach (range(1, 12) as $index) {
            $this->product($user, ['product_name' => 'Search fixture '.$index, 'manage_stock' => true, 'stock' => 1, 'min_stock' => 5]);
        }
        $other = $this->shop();
        $foreign = Customer::withoutGlobalScopes()->create(['tenant_id' => $other->tenant_id, 'name' => 'Other customer']);
        $customer = Customer::withoutGlobalScopes()->create(['tenant_id' => $user->tenant_id, 'name' => 'Local customer']);
        $result = $this->actingAs($user)->get('/products')->assertOk()->assertSee('page=2', false);
        $this->assertEquals(12, $result->viewData('catalogTotal'));
        $this->assertEquals(12, $result->viewData('lowCount'));
        $this->get('/products?search=fixture+12')->assertOk()->assertSee('Search fixture 12')->assertDontSee('Search fixture 11');
        $this->get('/customers/'.$customer->id)->assertOk()->assertSee('Local customer');
        $this->get('/customers/'.$foreign->id)->assertNotFound();
    }

    public function test_subscription_reuses_a_pending_invoice_with_a_snap_reference(): void
    {
        $user = $this->shop();
        $invoice = $this->invoice($user);
        $invoice->update(['snap_token' => 'fixture-only']);
        Plan::whereKey($invoice->plan_id)->update(['is_active' => true, 'is_public' => true]);
        $this->actingAs($user)->post('/billing/subscribe', ['plan_id' => $invoice->plan_id])->assertRedirect(route('billing.invoice', $invoice));
        $this->assertDatabaseCount('invoices', 1);
    }

    public function test_cancelled_invoice_never_displays_a_success_message(): void
    {
        $user = $this->shop();
        $invoice = $this->invoice($user);
        $invoice->update(['status' => 'cancelled']);
        $this->actingAs($user)->get('/billing/invoices/'.$invoice->id)->assertOk()->assertDontSee('Pembayaran Berhasil / Lunas')->assertSee('dibatalkan atau gagal');
    }

    public function test_ai_report_fallback_runs_on_sqlite_and_excludes_unpaid_sales(): void
    {
        config(['services.gemini.api_key' => '']);
        $user = $this->shop();
        Plan::where('slug', 'growth')->update(['slug' => 'scale']);
        $product = $this->product($user, ['product_name' => 'Fallback fixture']);
        $this->shift($user);
        $this->actingAs($user)->postJson('/pos', $this->checkout($product))->assertOk();
        $order = Order::first();
        $order->update(['payment_status' => 'unpaid']);
        $response = $this->get('/reports/ai-analysis')->assertOk();
        $this->assertEquals(0, $response->viewData('rataRataOmsetHarian'));
    }

    public function test_owner_can_suspend_staff_but_not_owner_or_self(): void
    {
        $owner = $this->shop();
        $staff = User::factory()->create(['tenant_id' => $owner->tenant_id, 'role' => 'kasir']);
        $this->actingAs($owner)->patch('/employees/'.$staff->id.'/status', ['status' => 'inactive'])->assertRedirect();
        $this->assertSame('inactive', $staff->fresh()->status);
        $this->patchJson('/employees/'.$owner->id.'/status', ['status' => 'inactive'])->assertStatus(422);
        $this->actingAs($staff->fresh())->getJson('/pos')->assertForbidden();
    }

    public function test_ai_html_is_sanitized_on_first_response_and_cache_hit(): void
    {
        $user = $this->shop();
        Plan::where('slug', 'growth')->update(['slug' => 'scale']);
        config(['services.gemini.api_key' => 'synthetic-test-key']);
        Cache::flush();
        Http::fake(['*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => '<h3>Safe insight</h3><script>alert("unsafe-ai")</script><img src=x onerror=alert(1)><p onclick="alert(1)">Report</p>']]]]],
        ])]);
        foreach ([1, 2] as $request) {
            $response = $this->actingAs($user)->get('/reports/ai-analysis')->assertOk();
            $this->assertStringContainsString('<h3>Safe insight</h3>', $response->viewData('aiAnalysis'));
            $this->assertStringNotContainsString('unsafe-ai', $response->viewData('aiAnalysis'));
            $this->assertStringNotContainsString('onerror', $response->viewData('aiAnalysis'));
            $this->assertStringNotContainsString('onclick', $response->viewData('aiAnalysis'));
        }
        Http::assertSentCount(1);
    }

    public function test_pos_customer_lookup_is_bounded_searchable_and_tenant_scoped(): void
    {
        $owner = $this->shop();
        $other = $this->shop();
        for ($index = 0; $index < 55; $index++) {
            Customer::withoutGlobalScopes()->create(['tenant_id' => $owner->tenant_id, 'name' => sprintf('Customer %02d', $index)]);
        }
        $foreign = Customer::withoutGlobalScopes()->create(['tenant_id' => $other->tenant_id, 'name' => 'Secret customer']);
        $selected = Customer::withoutGlobalScopes()->where('tenant_id', $owner->tenant_id)->orderByDesc('id')->first();
        $this->actingAs($owner)->getJson('/pos/customers/lookup')->assertOk()->assertJsonCount(50, 'customers');
        $this->getJson('/pos/customers/lookup?q=Customer%2054')->assertOk()->assertJsonCount(1, 'customers')->assertJsonPath('customers.0.id', $selected->id);
        $this->getJson('/pos/customers/lookup?q=Secret&selected='.$foreign->id)->assertOk()->assertJsonCount(0, 'customers');
        $this->getJson('/pos/customers/lookup?q=Missing&selected='.$selected->id)->assertOk()->assertJsonCount(1, 'customers');
    }
}
