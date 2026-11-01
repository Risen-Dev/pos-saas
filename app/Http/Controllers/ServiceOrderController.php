<?php

// app/Http/Controllers/ServiceOrderController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ServiceOrder;
use App\Models\Tenant;
use App\Services\LegacyServiceProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServiceOrderController extends Controller
{
    public function index()
    {
        return ServiceOrder::with('order')
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        return DB::transaction(function () use ($request, $data) {
            Tenant::whereKey($request->user()->tenant_id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($data['order_id'])->lockForUpdate()->firstOrFail();
            abort_if(ServiceOrder::where('order_id', $order->id)->exists(), 409, 'Pengerjaan jasa ini sudah tercatat.');
            app(LegacyServiceProgress::class)->apply($order, $data['service_status'] ?? 'received');

            return ServiceOrder::create([...$data, 'tenant_id' => $request->user()->tenant_id]);
        }, 3);
    }

    public function show(ServiceOrder $serviceOrder)
    {
        return $serviceOrder->load('order');
    }

    public function update(Request $request, ServiceOrder $serviceOrder)
    {
        $data = $this->validated($request, false);
        DB::transaction(function () use ($request, $serviceOrder, $data) {
            Tenant::whereKey($request->user()->tenant_id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($serviceOrder->order_id)->lockForUpdate()->firstOrFail();
            $legacy = ServiceOrder::whereKey($serviceOrder->id)->lockForUpdate()->firstOrFail();
            if (isset($data['service_status'])) {
                app(LegacyServiceProgress::class)->apply($order, $data['service_status']);
            }
            $legacy->update($data);
        }, 3);
        $serviceOrder->refresh();

        return $serviceOrder;
    }

    private function validated(Request $request, bool $creating = true): array
    {
        return $request->validate([
            'order_id' => $creating ? ['required', 'integer', Rule::exists('orders', 'id')->where('tenant_id', $request->user()->tenant_id)] : ['prohibited'],
            'service_status' => 'sometimes|required|in:received,washing,drying,finished,picked_up',
            'estimated_finish' => 'nullable|date',
            'finished_at' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);
    }

    public function destroy(ServiceOrder $serviceOrder)
    {
        $serviceOrder->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
