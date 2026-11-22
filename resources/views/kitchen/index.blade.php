<x-app-layout>
    @section('title', 'Antrean Dapur')
    @php
        $columns = [
            'queued' => ['label' => 'Baru Masuk', 'icon' => 'fa-bell', 'tone' => 'bg-accent-100 text-accent-700', 'next' => 'preparing', 'action' => 'Mulai Buat'],
            'preparing' => ['label' => 'Sedang Dibuat', 'icon' => 'fa-fire-burner', 'tone' => 'bg-blue-50 text-blue-700', 'next' => 'ready', 'action' => 'Tandai Siap'],
            'ready' => ['label' => 'Siap Disajikan', 'icon' => 'fa-circle-check', 'tone' => 'bg-primary-50 text-primary-700', 'next' => 'served', 'action' => 'Sudah Disajikan'],
        ];
    @endphp

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop">
        <div class="flex flex-col justify-between gap-4 pb-6 mb-6 border-b md:flex-row md:items-center border-border-200">
            <div>
                <div class="flex items-center gap-2 mb-2 text-[10px] font-bold tracking-widest uppercase text-primary-600"><x-icon class="fa-solid fa-kitchen-set" /> Dapur & Bar</div>
                <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900">Antrean Produksi</h1>
                <p class="max-w-2xl mt-1 text-xs leading-relaxed text-ink-700 md:text-sm">Pesanan lunas yang membutuhkan persiapan. Halaman diperbarui otomatis setiap 20 detik saat tidak sedang mengisi form.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('kitchen.index') }}" class="inline-flex items-center justify-center h-10 gap-2 px-4 text-xs font-semibold border rounded-md border-border-200 text-ink-700 bg-surface-0 hover:bg-surface-100"><x-icon class="fa-solid fa-rotate-right" /> Perbarui</a>
                <a href="{{ route('pos.index') }}" class="inline-flex items-center justify-center h-10 gap-2 px-4 text-xs font-semibold text-white rounded-md shadow-sm bg-primary-600 hover:bg-primary-700"><x-icon class="fa-solid fa-cash-register" /> Buka Kasir</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3" data-motion-stagger>
            @foreach($columns as $status => $meta)
                <section class="min-w-0">
                    <div class="flex items-center justify-between p-4 mb-3 border rounded-lg bg-surface-0 border-border-200">
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center w-9 h-9 rounded-md {{ $meta['tone'] }}"><x-icon class="fa-solid {{ $meta['icon'] }}" /></span>
                            <div><h2 class="text-sm font-semibold text-ink-900">{{ $meta['label'] }}</h2><p class="text-[10px] text-ink-400">{{ $orders->where('kitchen_status', $status)->count() }} pesanan</p></div>
                        </div>
                        <span class="inline-flex items-center justify-center min-w-7 h-7 px-2 text-[11px] font-bold rounded-full bg-surface-100 text-ink-700">{{ $orders->where('kitchen_status', $status)->count() }}</span>
                    </div>

                    <div class="space-y-3">
                        @forelse($orders->where('kitchen_status', $status) as $order)
                            <article class="overflow-hidden transition-shadow border rounded-lg shadow-sm bg-surface-0 border-border-200 hover:shadow-md">
                                <div class="p-4 border-b border-border-200 bg-surface-100/60">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <a href="{{ route('orders.show', $order) }}" class="text-sm font-bold text-ink-900 hover:text-primary-600">#{{ $order->id }} · {{ $order->invoice_number }}</a>
                                            <p class="mt-1 text-[10px] text-ink-400"><x-icon class="mr-1 fa-regular fa-clock" />{{ $order->created_at->format('H:i') }} · {{ $order->created_at->diffForHumans() }}</p>
                                        </div>
                                        <span class="px-2.5 py-1 text-[10px] font-semibold rounded-full bg-primary-50 text-primary-700 whitespace-nowrap">
                                            {{ $order->order_type === 'dine_in' ? 'Meja '.($order->table_number ?: '—') : ucwords(str_replace('_', ' ', $order->order_type)) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="p-4">
                                    <div class="space-y-3">
                                        @foreach($order->items->filter(fn ($item) => $item->requires_preparation !== 0 && $item->requires_preparation !== false) as $item)
                                            <div class="pb-3 border-b last:border-0 last:pb-0 border-border-200">
                                                <div class="flex items-start gap-2"><span class="font-mono text-sm font-bold text-primary-600">{{ \App\Support\NumberFormat::quantity($item->quantity) }}×</span><div class="min-w-0"><p class="text-sm font-semibold text-ink-900">{{ $item->product_name }}</p>
                                                @foreach($item->addons ?? [] as $addon)<p class="mt-0.5 text-[11px] text-ink-400">+ {{ $addon['name'] }}</p>@endforeach
                                                @if($item->note)<p class="p-2 mt-2 text-[11px] leading-relaxed rounded-md bg-accent-100/60 text-accent-700"><x-icon class="mr-1 fa-solid fa-note-sticky" />{{ $item->note }}</p>@endif</div></div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($order->note)<p class="p-2.5 mt-3 text-[11px] leading-relaxed border rounded-md border-border-200 bg-surface-100 text-ink-700"><strong>Catatan order:</strong> {{ $order->note }}</p>@endif

                                    <form action="{{ route('kitchen.update', $order) }}" method="post" class="mt-4">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $meta['next'] }}">
                                        <button class="inline-flex items-center justify-center w-full h-10 gap-2 text-xs font-semibold text-white transition-colors rounded-md bg-primary-600 hover:bg-primary-700">{{ $meta['action'] }} <x-icon class="fa-solid fa-arrow-right" /></button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="px-5 py-12 text-center border border-dashed rounded-lg bg-surface-0 border-border-200">
                                <div class="flex items-center justify-center w-10 h-10 mx-auto mb-3 rounded-full bg-surface-100 text-ink-400"><x-icon class="fa-solid {{ $meta['icon'] }}" /></div>
                                <p class="text-xs font-semibold text-ink-700">Belum ada pesanan</p>
                                <p class="mt-1 text-[10px] text-ink-400">Pesanan akan muncul otomatis di kolom ini.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    <script>
        setInterval(() => {
            if (!document.hidden && !document.activeElement?.closest('form')) window.location.reload();
        }, 20000);
    </script>
</x-app-layout>
