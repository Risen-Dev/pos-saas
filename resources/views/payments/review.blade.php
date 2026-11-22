<x-app-layout>
    <div class="[max-width:1280px] [margin:auto] p-7 [color:#18372d] max-[701px]:[padding:20px_16px]">
        <x-page-header>
            <div>
                <h1 class="[font-size:clamp(24px,_3vw,_34px)] font-bold [letter-spacing:-0.04em] [line-height:1.2] [margin:8px_0]">Pemeriksaan pembayaran</h1>
                <p class="[color:#65796f] [font-size:13px] [line-height:1.6]">Nota QRIS yang belum lunas atau memerlukan pemeriksaan. Tombol ini hanya memeriksa referensi lama, tidak membuat tagihan baru.</p>
            </div>
        </x-page-header>
        @if(session('success'))<p class="[padding:13px_16px] rounded-md [background:#fff6df] [color:#81591c] [font-size:13px] [line-height:1.6] mt-4">{{ session('success') }}</p>@endif
        @foreach($errors->all() as $error)<p role="alert" class="[padding:13px_16px] rounded-md [background:#fff6df] [color:#81591c] [font-size:13px] [line-height:1.6] mt-4">{{ $error }}</p>@endforeach
        @forelse($orders as $order)
            <article class="bg-white [border:1px_solid_#e1e9e4] rounded-lg [padding:22px] [box-shadow:0_2px_5px_#17392c03] max-[701px]:[padding:18px] mt-4">
                <a class="underline break-all" href="{{ route('orders.show', $order) }}">{{ $order->invoice_number }}</a>
                <p>Rp {{ number_format($order->grand_total, 0, ',', '.') }} · {{ $order->order_status }} · {{ $order->created_at->format('d/m/Y H:i') }}</p>
                @if($order->payment_attention)<p class="text-red-700">Perlu konfirmasi: status gateway belum pasti.</p>@endif
                <form method="POST" action="{{ route('payments.review.check', $order) }}">@csrf<button class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] mt-3">Periksa status penyedia</button></form>
            </article>
        @empty<p class="bg-white [border:1px_solid_#e1e9e4] rounded-lg [padding:22px] [box-shadow:0_2px_5px_#17392c03] max-[701px]:[padding:18px] mt-4">Tidak ada pembayaran yang menunggu pemeriksaan.</p>@endforelse
        <div class="mt-4">{{ $orders->links() }}</div>
    </div>
</x-app-layout>
