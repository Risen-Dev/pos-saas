<?php

namespace Tests\Feature\Pos;

use App\Http\Controllers\StocktakeController;
use App\Models\Discount;
use App\Models\PurchaseOrder;
use App\Models\Stocktake;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ManagementSafetyTest extends PosTestCase
{
    public function test_discount_rejects_foreign_products_on_create_and_update(): void
    {
        $user = $this->shop();
        $other = $this->shop();
        $product = $this->product($user);
        $foreign = $this->product($other);
        $payload = ['name' => 'Promo', 'type' => 'percentage', 'value' => 10, 'product_ids' => [$foreign->id]];
        $this->actingAs($user)->postJson('/discounts', $payload)->assertUnprocessable()->assertJsonValidationErrors('product_ids.0');
        $this->assertDatabaseCount('discounts', 0);
        $payload['product_ids'] = [$product->id];
        $this->post('/discounts', $payload)->assertRedirect();
        $discount = Discount::firstOrFail();
        $payload['product_ids'] = [$foreign->id];
        $payload['name'] = 'Changed';
        $this->putJson('/discounts/'.$discount->id, $payload)->assertUnprocessable();
        $this->assertSame('Promo', $discount->fresh()->name);
        $this->assertSame([$product->id], $discount->products()->pluck('products.id')->all());
        $this->actingAs($other)->deleteJson('/discounts/'.$discount->id)->assertNotFound();
    }

    public function test_discount_validates_amount_schedule_and_explicit_false(): void
    {
        $user = $this->shop();
        $product = $this->product($user);
        $payload = ['name' => 'Promo', 'type' => 'percentage', 'value' => 101, 'product_ids' => [$product->id], 'start_date' => '2026-10-10', 'end_date' => '2026-10-09', 'days' => ['Invalid']];
        $this->actingAs($user)->postJson('/discounts', $payload)->assertUnprocessable()->assertJsonValidationErrors(['value', 'end_date', 'days.0']);
        $payload = array_replace($payload, ['value' => 10, 'end_date' => '2026-10-11', 'days' => ['Monday'], 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'is_active' => false]);
        $this->postJson('/discounts', $payload)->assertRedirect();
        $this->assertFalse(Discount::firstOrFail()->is_active);
    }

    public function test_purchase_rejects_injected_received_quantity_and_empty_receipts(): void
    {
        $user = $this->shop();
        $product = $this->product($user, ['manage_stock' => true]);
        $line = ['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 100, 'received_quantity' => 5];
        $this->actingAs($user)->postJson('/purchases', ['items' => [$line]])->assertUnprocessable();
        $this->assertDatabaseCount('purchase_orders', 0);
        unset($line['received_quantity']);
        $this->post('/purchases', ['items' => [$line]])->assertRedirect();
        $purchase = PurchaseOrder::with('items')->firstOrFail();
        $item = $purchase->items->first();
        $this->postJson('/purchases/'.$purchase->id.'/receive', ['items' => [$item->id => 0]])->assertUnprocessable();
        $this->assertSame('ordered', $purchase->fresh()->status);
        $this->post('/purchases/'.$purchase->id.'/receive', ['items' => [$item->id => 5]])->assertRedirect();
        $this->postJson('/purchases/'.$purchase->id.'/receive', ['items' => [$item->id => 5]])->assertUnprocessable();
        $this->assertEquals(15, $product->fresh()->stock);
        $this->assertEquals(5, $item->fresh()->received_quantity);
    }

    public function test_purchase_numbers_remain_unique_after_an_order_is_deleted(): void
    {
        $user = $this->shop();
        $product = $this->product($user, ['manage_stock' => true]);
        $payload = ['items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_cost' => 100]]];
        $this->freezeTime();
        $this->actingAs($user)->post('/purchases', $payload)->assertRedirect();
        $this->post('/purchases', $payload)->assertRedirect();
        PurchaseOrder::oldest('id')->firstOrFail()->delete();
        $this->post('/purchases', $payload)->assertRedirect();
        $this->assertSame(2, PurchaseOrder::distinct()->count('number'));
    }

    public function test_purchase_rejects_quantities_below_storage_precision(): void
    {
        $user = $this->shop();
        $product = $this->product($user, ['manage_stock' => true, 'allow_fraction' => true]);
        $this->actingAs($user)->postJson('/purchases', ['items' => [
            ['product_id' => $product->id, 'quantity' => 0.0001, 'unit_cost' => 100],
        ]])->assertUnprocessable()->assertJsonValidationErrors('items.0.quantity');
        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_stocktake_numbers_are_unique_within_the_same_second(): void
    {
        $user = $this->shop();
        $this->freezeTime();
        $this->actingAs($user)->post('/stocktakes')->assertRedirect();
        $this->post('/stocktakes')->assertRedirect();
        $this->assertSame(2, Stocktake::distinct()->count('number'));
    }

    public function test_stale_stocktake_cannot_overwrite_stock_after_completion(): void
    {
        $user = $this->shop();
        $product = $this->product($user, ['manage_stock' => true]);
        $this->actingAs($user)->post('/stocktakes')->assertRedirect();
        $stocktake = Stocktake::with('items')->firstOrFail();
        $counts = [$stocktake->items->first()->id => 8];
        $this->post('/stocktakes/'.$stocktake->id.'/complete', ['counts' => $counts])->assertRedirect();
        $product->update(['stock' => 7]);
        $request = Request::create('/stocktakes/'.$stocktake->id.'/complete', 'POST', ['counts' => $counts]);
        $request->setUserResolver(fn () => $user);
        try {
            app(StocktakeController::class)->complete($request, $stocktake);
            $this->fail('A stale stocktake must be rejected.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }
        $this->assertEquals(7, $product->fresh()->stock);
    }

    public function test_customer_api_accepts_missing_phone_and_returns_validation_errors(): void
    {
        $this->actingAs($this->shop())->postJson('/customers/api', ['name' => 'Customer'])->assertOk()->assertJsonPath('data.phone', null);
        $this->postJson('/customers/api', [])->assertUnprocessable()->assertJsonValidationErrors('name');
    }
}
