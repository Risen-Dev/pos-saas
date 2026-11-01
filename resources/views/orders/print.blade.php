<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $order->invoice_number }}</title>
    {{-- Print-only exception: keep receipts independent of Vite, fonts, and network assets. --}}
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body {
            width: 58mm;
            margin: 0 auto;
            padding: 4mm;
            background: #fff;
            color: #000;
            font-family: "Courier New", Courier, monospace;
            font-size: 11px;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }
        body.paper-80 { width: 80mm; }
        h2, p { margin: 0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .print-actions { margin-bottom: 16px; text-align: center; }
        .print-actions button { padding: 8px 16px; cursor: pointer; }
        .receipt-header { margin-bottom: 8px; text-align: center; }
        .receipt-header h2 { font-size: 14px; }
        .receipt-header p { margin: 2px 0; font-size: 10px; }
        .divider { border-bottom: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td { vertical-align: top; }
        .info-table td { padding: 1px 0; font-size: 10px; }
        .item-table td { padding: 2px 0; }
        .item-table tr { break-inside: avoid; }
        .item-detail { font-size: 10px; font-weight: normal; text-transform: none; }
        .item-price { text-align: right; font-weight: bold; }
        .total-row { display: flex; justify-content: space-between; gap: 8px; margin: 2px 0; }
        .total-row > span:last-child { text-align: right; }
        .grand-total { margin-top: 4px; padding-top: 4px; border-top: 1px double #000; font-size: 13px; font-weight: bold; }
        .receipt-footer { margin-top: 12px; text-align: center; font-size: 10px; }
        @media print {
            .print-actions { display: none !important; }
            .total-row, .receipt-footer { break-inside: avoid; }
        }
    </style>
</head>

<body class="{{ ($paper ?? 58) == 80 ? 'paper-80' : 'paper-58' }}">

    <div class="print-actions">
        <button onclick="window.print()" class="font-bold">CETAK</button>
        <button onclick="window.close()">TUTUP</button>
    </div>

    <div class="receipt-header">
        <h2 class="uppercase">{{ auth()->user()->tenant->name ?? 'GROWPOS STORE' }}</h2>
        <p>{{ auth()->user()->tenant->address ?? 'Alamat Operasional' }}</p>
        <p>Telp: {{ auth()->user()->tenant->phone ?? '-' }}</p>
    </div>

    <div class="divider"></div>

    <table class="info-table">
        <tr>
            <td>No: {{ $order->invoice_number }}</td>
            <td class="text-right">{{ $order->created_at->format('d/m/y H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir: {{ auth()->user()->name }}</td>
            <td class="text-right">{{ $order->customer->name ?? 'Pelanggan Umum' }}</td>
        </tr>
        <tr><td colspan="2">Tipe:
            <strong>
                {{ strtoupper($order->order_type ?? 'DINE_IN') }}
                @if ($order->order_type === 'dine_in' && $order->table_number)
                    (Meja {{ $order->table_number }})
                @endif
            </strong>
        </td></tr>
    </table>

    <div class="divider"></div>

    <table class="item-table">
        @foreach ($order->items as $item)
            <tr>
                <td colspan="2" class="font-bold uppercase">{{ $item->product_name }}@foreach($item->addons ?? [] as $addon)<div class="item-detail">+ {{ $addon['name'] }}</div>@endforeach @if($item->note)<div class="item-detail">{{ $item->note }}</div>@endif</td>
            </tr>
            <tr>
                <td class="item-detail">{{ \App\Support\NumberFormat::quantity($item->quantity) }} {{ $item->unit_name }} × @ Rp {{ number_format($item->price, 0, ',', '.') }}
                </td>
                <td class="item-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <div>
        <div class="total-row">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
        </div>
        @if ($order->discount > 0)
            <div class="total-row">
                <span>Diskon Promo:</span>
                <span>-Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
            </div>
        @endif
        @if ($order->loyalty_discount > 0)
            <div class="total-row">
                <span>Potongan poin loyalitas:</span>
                <span>-Rp {{ number_format($order->loyalty_discount, 0, ',', '.') }}</span>
            </div>
        @endif
        @if ($order->tax > 0)
            <div class="total-row">
                <span>Pajak Outlet:</span>
                <span>+Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
            </div>
        @endif

        <div class="total-row grand-total">
            <span>TOTAL:</span>
            <span>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
        </div>

        <div class="divider"></div>

        <div class="total-row">
            <span>Bayar ({{ \App\Support\PaymentMethod::label($order->payment_method) }}):</span>
            <span>Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span>Kembalian:</span>
            <span>Rp {{ number_format($order->change_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="receipt-footer">
        <p class="font-bold">*** {{ $order->payment_status === 'paid' ? 'LUNAS' : 'PIUTANG / BON' }} ***</p>
        <p>Terima Kasih Atas Kunjungan Anda!</p>
        <p>Powered by GrowPOS SaaS</p>
    </div>

    <script>
        window.focus();
        window.addEventListener('load', async () => {
            await document.fonts.ready;
            window.print();
        });
    </script>
@if($order->payment_verified_at)<p class="text-center" style="font-size:10px">Dana diperiksa manual oleh petugas toko.<br>{{ $order->payment_reference ? 'Ref: '.$order->payment_reference : '' }}</p>@endif
</body>

</html>
