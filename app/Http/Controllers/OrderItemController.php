<?php

// app/Http/Controllers/OrderItemController.php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    public function index()
    {
        return OrderItem::with([
            'order',
            'product',
            'variant',
        ])->latest()->get();
    }

    public function store(Request $request)
    {
        return OrderItem::create($request->all());
    }

    public function show(OrderItem $orderItem)
    {
        return $orderItem->load([
            'order',
            'product',
            'variant',
        ]);
    }

    public function update(Request $request, OrderItem $orderItem)
    {
        $orderItem->update($request->all());

        return $orderItem;
    }

    public function destroy(OrderItem $orderItem)
    {
        $orderItem->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
