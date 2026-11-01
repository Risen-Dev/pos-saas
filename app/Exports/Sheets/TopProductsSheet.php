<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Facades\DB;

class TopProductsSheet
{
    protected int $tenantId;

    protected $startDate;

    protected $endDate;

    public function __construct($startDate, $endDate, int $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return 'Produk Terlaris';
    }

    public function collection()
    {
        // Query yang sama persis dengan yang ada di ReportController Anda
        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.tenant_id', $this->tenantId)->whereBetween(DB::raw('COALESCE(orders.sold_at, orders.created_at)'), [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59'])
            ->where('orders.payment_status', 'paid')->where('orders.order_status', 'completed')
            ->groupBy('order_items.product_name')
            ->orderBy('total_qty', 'desc')
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_sales')
            )
            ->take(5)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama Produk',
            'Jumlah Terjual',
            'Total Omzet Produk',
        ];
    }

    public function map($row): array
    {
        return [
            $row->product_name,
            $row->total_qty.'x',
            'Rp '.number_format($row->total_sales, 0, ',', '.'),
        ];
    }
}
