<?php

namespace Tests\Feature\Pos;

use App\Models\Order;

class CashierWorkspaceTest extends PosTestCase
{
    public function test_cashier_operational_pages_never_render_backoffice_navigation(): void
    {
        $cashier = $this->shop('kasir');
        $cashier->tenant->update(['business_type' => 'service', 'business_modules' => ['services']]);
        $service = $this->product($cashier, ['type' => 'service', 'manage_stock' => false, 'stock' => 0]);
        $this->shift($cashier);

        $this->actingAs($cashier)->postJson('/pos', $this->checkout($service))->assertOk();
        $order = Order::firstOrFail();

        $this->get('/services')
            ->assertOk()
            ->assertSee('Mode Kasir')
            ->assertSee($order->invoice_number)
            ->assertDontSee('SaaS Management')
            ->assertDontSee('Produk & Stok')
            ->assertDontSee('Laporan Penjualan');

        $this->get('/help')
            ->assertOk()
            ->assertSee('Mode Kasir')
            ->assertDontSee('SaaS Management');
    }

    public function test_cashier_is_blocked_from_business_billing_and_legacy_management_pages(): void
    {
        $cashier = $this->shop('kasir');
        $this->actingAs($cashier);

        foreach (['/dashboard', '/getting-started', '/billing', '/tenants', '/setup-business', '/products', '/inventory', '/reports', '/settings', '/employees', '/cash'] as $url) {
            $this->get($url)->assertRedirect('/pos');
        }

        $this->get('/shifts')->assertRedirect('/pos');
        $this->getJson('/service-orders')->assertForbidden();
        $this->deleteJson('/profile', ['password' => 'password'])->assertForbidden();
    }

    public function test_cashier_can_still_advance_service_workflow_without_payment_side_effects(): void
    {
        $cashier = $this->shop('kasir');
        $cashier->tenant->update(['business_type' => 'service', 'business_modules' => ['services']]);
        $service = $this->product($cashier, ['type' => 'service', 'manage_stock' => false, 'stock' => 0]);
        $this->shift($cashier);
        $this->actingAs($cashier)->postJson('/pos', $this->checkout($service))->assertOk();
        $order = Order::firstOrFail();

        $this->patch('/services/'.$order->id, ['status' => 'working'])->assertRedirect();
        $this->patch('/services/'.$order->id, ['status' => 'ready'])->assertRedirect();
        $this->patch('/services/'.$order->id, ['status' => 'completed'])->assertRedirect();

        $order->refresh();
        $this->assertSame('completed', $order->service_status);
        $this->assertSame('paid', $order->payment_status);
    }
}
