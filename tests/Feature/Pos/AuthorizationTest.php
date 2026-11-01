<?php

namespace Tests\Feature\Pos;

use App\Models\User;
use App\Models\WithdrawlRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthorizationTest extends PosTestCase
{
    public function test_shop_admin_cannot_access_platform_withdrawals(): void
    {
        $user = $this->shop();
        $this->actingAs($user)->get('/withdrawals')->assertForbidden();
        $this->post('/withdrawals/1/approve')->assertForbidden();
        $this->post('/withdrawals/1/reject', ['admin_note' => 'No'])->assertForbidden();
    }

    public function test_cashier_cannot_change_bank_or_withdraw(): void
    {
        $this->actingAs($this->shop('kasir'))->postJson('/finance/settings', [])->assertForbidden();
        $this->postJson('/finance/withdraw', ['amount' => 10000])->assertForbidden();
        $this->get('/finance')->assertForbidden();
    }

    public function test_platform_admin_can_approve_without_shop_subscription(): void
    {
        $owner = $this->shop();
        $withdrawal = WithdrawlRequest::create(['tenant_id' => $owner->tenant_id, 'reference_number' => 'WDR-TEST', 'bank_name' => 'Bank', 'account_number' => '123', 'account_name' => 'Owner', 'amount' => 10000, 'status' => 'pending']);
        $admin = User::factory()->create();
        $admin->forceFill(['is_platform_admin' => true])->save();
        $this->actingAs($admin)->get('/withdrawals')->assertOk();
        $this->post('/withdrawals/'.$withdrawal->id.'/approve')->assertRedirect();
        $this->assertSame('approved', $withdrawal->fresh()->status);
    }

    public function test_platform_privilege_cannot_be_mass_assigned(): void
    {
        $user = User::factory()->create();
        $user->fill(['is_platform_admin' => true])->save();
        $this->assertFalse($user->fresh()->is_platform_admin);
    }

    public function test_shop_admin_can_save_bank_and_submit_withdrawal(): void
    {
        $user = $this->shop();
        DB::table('tenant_wallets')->insert(['tenant_id' => $user->tenant_id, 'balance' => 20000]);
        $this->actingAs($user)->postJson('/finance/settings', ['bank_name' => 'Bank', 'account_number' => '123', 'account_name' => 'Owner'])->assertOk();
        $this->postJson('/finance/withdraw', ['amount' => 10000, 'idempotency_key' => (string) Str::uuid()])->assertOk();
        $this->assertDatabaseHas('tenant_wallets', ['tenant_id' => $user->tenant_id, 'balance' => 10000]);
        $this->assertDatabaseHas('withdrawal_requests', ['tenant_id' => $user->tenant_id, 'status' => 'pending', 'amount' => 10000]);
    }
}
