<x-app-layout>
    @section('title', 'Pengerjaan Jasa')
    @php
        $labels = ['queued' => 'Menunggu', 'working' => 'Dikerjakan', 'ready' => 'Siap Diserahkan', 'completed' => 'Selesai'];
        $icons = ['queued' => 'fa-clock', 'working' => 'fa-screwdriver-wrench', 'ready' => 'fa-circle-check', 'completed' => 'fa-flag-checkered'];
    @endphp

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop">
        <div class="flex flex-col justify-between gap-4 pb-6 mb-6 border-b md:flex-row md:items-center border-border-200">
            <div>
                <div class="flex items-center gap-2 mb-2 text-[10px] font-bold tracking-widest uppercase text-primary-600"><x-icon class="fa-solid fa-screwdriver-wrench" /> Layanan Pelanggan</div>
                <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900">Pengerjaan Jasa</h1>
                <p class="max-w-2xl mt-1 text-xs leading-relaxed text-ink-700 md:text-sm">Tetapkan petugas, estimasi selesai, dan progres setiap order jasa. Status pengerjaan tidak mengubah status pembayaran.</p>
            </div>
            <a href="{{ route('pos.index') }}" class="inline-flex items-center justify-center h-10 gap-2 px-4 text-xs font-semibold text-white rounded-md shadow-sm bg-primary-600 hover:bg-primary-700"><x-icon class="fa-solid fa-plus" /> Transaksi Baru</a>
        </div>

        @if(session('success'))<div role="status" class="flex items-center gap-2 p-4 mb-5 text-sm border rounded-lg bg-primary-50 border-primary-100 text-primary-700"><x-icon class="fa-solid fa-circle-check" />{{ session('success') }}</div>@endif
        @if($errors->any())<div role="alert" class="p-4 mb-5 text-xs border rounded-lg bg-red-50 border-red-100 text-semantic-danger">{{ $errors->first() }}</div>@endif

        <div class="flex gap-2 pb-2 mb-6 overflow-x-auto">
            @foreach($labels as $key => $label)
                <a href="{{ route('services.index', ['status' => $key]) }}" class="inline-flex items-center h-9 gap-2 px-3 text-xs font-semibold transition-colors border rounded-md whitespace-nowrap {{ $status === $key ? 'bg-primary-600 border-primary-600 text-white shadow-sm' : 'bg-surface-0 border-border-200 text-ink-700 hover:bg-surface-100' }}"><x-icon class="fa-solid {{ $icons[$key] }}" />{{ $label }}</a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse($orders as $order)
                @php($overdue = $order->service_due_at && $order->service_due_at->isPast() && $order->service_status !== 'completed')
                <article class="overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">
                    <div class="flex items-start justify-between gap-3 p-5 border-b bg-surface-100/60 border-border-200">
                        <div class="min-w-0">
                            <a class="block text-sm font-bold truncate text-ink-900 hover:text-primary-600" href="{{ route('orders.show', $order) }}">{{ $order->invoice_number }}</a>
                            <p class="mt-1 text-[10px] text-ink-400"><x-icon class="mr-1 fa-regular fa-calendar" />{{ $order->created_at->format('d M Y · H:i') }}</p>
                        </div>
                        <span class="px-2.5 py-1 text-[10px] font-semibold rounded-full {{ $order->payment_status === 'paid' ? 'bg-primary-50 text-primary-700' : 'bg-accent-100 text-accent-700' }}">{{ $order->payment_status === 'paid' ? 'Lunas' : 'Belum Lunas' }}</span>
                    </div>

                    <div class="p-5">
                        <div class="flex items-center gap-2 mb-4"><span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-50 text-primary-600"><x-icon class="text-xs fa-solid fa-user" /></span><div><p class="text-[10px] uppercase tracking-wider text-ink-400 font-bold">Pelanggan</p><p class="text-xs font-semibold text-ink-900">{{ $order->customer?->name ?? 'Pelanggan umum' }}</p></div></div>

                        <div class="space-y-2.5 mb-4">
                            @foreach($order->items as $item)
                                <div class="flex items-start gap-2 text-xs"><span class="font-mono font-bold text-primary-600">{{ \App\Support\NumberFormat::quantity($item->quantity) }}×</span><div><p class="font-semibold text-ink-900">{{ $item->product_name }}</p>@if($item->note)<p class="mt-0.5 text-[10px] text-ink-400">{{ $item->note }}</p>@endif</div></div>
                            @endforeach
                        </div>

                        @if($order->note)<div class="p-2.5 mb-4 text-[11px] leading-relaxed rounded-md bg-surface-100 text-ink-700">{{ $order->note }}</div>@endif
                        @if($overdue)<div class="flex items-center gap-2 p-2.5 mb-4 text-[11px] font-semibold rounded-md bg-red-50 text-semantic-danger"><x-icon class="fa-solid fa-triangle-exclamation" />Melewati estimasi selesai</div>@endif

                        <form action="{{ route('services.update', $order) }}" method="post" class="space-y-3">
                            @csrf @method('PATCH')
                            <div><label for="field-service_due_at" class="block mb-1 text-[10px] font-bold tracking-wider uppercase text-ink-400">Estimasi Selesai</label><input id="field-service_due_at" type="datetime-local" name="service_due_at" value="{{ $order->service_due_at?->format('Y-m-d\TH:i') }}" class="w-full h-10 px-3 text-xs border rounded-md border-border-200 focus:border-primary-500 focus:ring-primary-500"></div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div><label for="field-assigned_user_id" class="block mb-1 text-[10px] font-bold tracking-wider uppercase text-ink-400">Petugas</label><select id="field-assigned_user_id" name="assigned_user_id" class="w-full h-10 px-3 text-xs border rounded-md border-border-200 focus:border-primary-500 focus:ring-primary-500"><option value="">Belum ditugaskan</option>@foreach($staff as $person)<option value="{{ $person->id }}" @selected($order->assigned_user_id === $person->id)>{{ $person->name }}</option>@endforeach</select></div>
                                <div><label for="field-status" class="block mb-1 text-[10px] font-bold tracking-wider uppercase text-ink-400">Status</label><select id="field-status" name="status" class="w-full h-10 px-3 text-xs border rounded-md border-border-200 focus:border-primary-500 focus:ring-primary-500"><option value="{{ $status }}">{{ $labels[$status] }}</option>@php($next = ['queued' => 'working', 'working' => 'ready', 'ready' => 'completed'][$status] ?? null)@if($next)<option value="{{ $next }}">{{ $labels[$next] }}</option>@endif</select></div>
                            </div>
                            <button class="inline-flex items-center justify-center w-full h-10 gap-2 text-xs font-semibold text-white rounded-md bg-primary-600 hover:bg-primary-700"><x-icon class="fa-solid fa-floppy-disk" />Simpan Pengerjaan</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="py-16 text-center border border-dashed rounded-lg md:col-span-2 xl:col-span-3 bg-surface-0 border-border-200"><div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-surface-100 text-ink-400"><x-icon class="fa-solid {{ $icons[$status] }}" /></div><p class="text-sm font-semibold text-ink-900">Belum ada pekerjaan {{ strtolower($labels[$status]) }}</p><p class="mt-1 text-xs text-ink-400">Order jasa dengan status ini akan muncul di sini.</p></div>
            @endforelse
        </div>

        @if($orders->hasPages())<div class="mt-6">{{ $orders->links() }}</div>@endif
    </div>
</x-app-layout>
