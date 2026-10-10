<!DOCTYPE html>
<html class="motion-safe:scroll-smooth [&_*]:[-webkit-tap-highlight-color:transparent] motion-reduce:[&_*]:!scroll-auto motion-reduce:[&_*]:!transition-none motion-reduce:[&_*]:!animate-none motion-reduce:[&_*::before]:!animate-none motion-reduce:[&_*::after]:!animate-none" lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kasir · {{ auth()->user()->tenant->name }} — GrowPOS</title>

    {{-- GrowPOS Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $catalog = $products
        ->map(
            fn($p) => [
                'id' => $p->id,
                'name' => $p->product_name,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'type' => $p->type,
                'category_id' => $p->category_id,
                'base_unit' => $p->base_unit,
                'allow_fraction' => $p->allow_fraction,
                'price_tiers' => $p->price_tiers ?? [],
                'units' => $p->units
                    ->map(
                        fn($u) => [
                            'id' => $u->id,
                            'name' => $u->name,
                            'factor' => (float) $u->factor,
                            'price' => (int) $u->price,
                            'discount' => (int) $u->discount,
                        ],
                    )
                    ->values(),
                'price' => (int) $p->sell_price,
                'discount' => (int) $p->discount_applied,
                'stock' => (float) $p->stock,
                'tracked' => $p->type === 'product' && $p->manage_stock,
                'image' => $p->image ? asset('storage/' . $p->image) : null,
                'variants' => $p->variants
                    ->map(
                        fn($v) => [
                            'id' => $v->id,
                            'name' => $v->name,
                            'price' => (int) round($v->price),
                            'discount' => (int) $v->discount,
                            'stock' => (float) $v->stock,
                        ],
                    )
                    ->values(),
                'addons' => $p->addons
                    ->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'price' => (int) $a->price])
                    ->values(),
            ],
        )
        ->values();
    $posConfig = [
        'food' => auth()->user()->tenant->hasBusinessModule('food'),
        'pendingPayments' => $pendingPayments,
        'tenant' => auth()->user()->tenant_id,
        'user' => auth()->id(),
        'csrf' => csrf_token(),
        'checkout' => route('pos.store', absolute: false),
        'products' => $catalog,
        'shift' => $activeShift,
        'taxRate' =>
            ($settings['tax_active'] ?? '0') === '1' ? min(100, max(0, (float) ($settings['tax_percentage'] ?? 0))) : 0,
    ];
@endphp

<body
    class="min-h-screen [background:#f5f7f3] [font-family:Inter,_system-ui,_sans-serif] [&_button:focus-visible]:[outline:3px_solid_#90b9a5] [&_button:focus-visible]:[outline-offset:3px]"
    x-data="growPos"
    @keydown.f2.window.prevent="if (cart.length && !busy) pay()"
    @keydown.f6.window.prevent="if (!busy) modal='drafts'"
    @keydown.f8.window.prevent="if (!busy) modal='history'">
    <script type="application/json" id="grow-pos-data">@json($posConfig)</script>
    @if (request('panel') === 'history')
        <div x-init="$nextTick(() => modal = 'history')"></div>
    @endif
    <header
        class="min-h-[76px] bg-white border-b border-[#e2e9df]
           px-6 py-3
           flex items-center justify-between gap-4
           max-[900px]:flex-wrap
           max-[701px]:px-4"
        :inert="!!modal">

        {{-- =========================================================
        STORE IDENTITY
    ========================================================== --}}
        <div class="flex items-center min-w-0 gap-3">

            <a href="{{ auth()->user()->role === 'kasir' ? route('pos.index') : route('dashboard') }}"
                class="w-10 h-10 shrink-0
                   grid place-items-center
                   rounded-full
                   bg-[#edf5ee]
                   text-[#17694d]
                   font-bold
                   hover:bg-[#dfeee3]
                   transition-colors"
                aria-label="Beranda" title="Beranda">

                <x-icon class="text-sm fa-solid fa-store" />
            </a>

            <div class="min-w-0">

                <div class="text-sm font-bold text-[#18372d]
                       truncate">
                    {{ auth()->user()->tenant->name }}
                </div>

                <div
                    class="flex items-center gap-1.5
                       text-[11px] text-[#65796f]
                       whitespace-nowrap">

                    <span class="truncate">
                        {{ auth()->user()->name }}
                    </span>

                    <span>·</span>

                    <span class="inline-flex items-center gap-1" aria-live="polite"
                        :class="online ? 'text-[#3c7757]' : 'text-amber-700'">

                        <span class="w-1.5 h-1.5 rounded-full" :class="online ? 'bg-emerald-500' : 'bg-amber-500'">
                        </span>

                        <span x-text="online ? 'Terhubung' : 'Offline'"></span>

                    </span>

                </div>

            </div>

        </div>


        {{-- =========================================================
        CASHIER ACTIONS
    ========================================================== --}}
        <div
            class="flex items-center justify-end gap-2
               max-[900px]:w-full
               max-[900px]:justify-start
               max-[900px]:overflow-x-auto
               max-[900px]:pb-1">

            {{-- RIWAYAT TRANSAKSI: tetap di shell POS, tidak membawa kasir ke back-office --}}
            <button type="button" @click="modal='history'"
                class="inline-flex items-center justify-center gap-2 h-11 px-4 shrink-0 rounded-lg border border-[#dae4dd] bg-white text-[#234a3b] text-[13px] font-semibold hover:bg-[#f1f7f3] hover:border-[#c7d8cd] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#17694d]/20"
                title="Riwayat transaksi">
                <x-icon class="fa-solid fa-clock-rotate-left text-[13px]" />
                <span>Riwayat</span>
            </button>


            {{-- PENGERJAAN JASA --}}
            @if (auth()->user()->tenant->hasBusinessModule('services'))
                <a href="{{ route('services.index') }}"
                    class="inline-flex items-center justify-center gap-2
                       h-11 px-4
                       shrink-0
                       rounded-lg
                       border border-[#dae4dd]
                       bg-white
                       text-[#234a3b]
                       text-[13px] font-semibold
                       hover:bg-[#f1f7f3]
                       hover:border-[#c7d8cd]
                       transition-colors
                       focus-visible:outline-none
                       focus-visible:ring-2
                       focus-visible:ring-[#17694d]/20"
                    title="Pengerjaan jasa">

                    <x-icon class="fa-solid fa-screwdriver-wrench text-[13px]" />

                    <span>Pengerjaan jasa</span>

                </a>
            @endif


            {{-- QRIS MENUNGGU --}}
            <button type="button" x-show="pendingPayments.length" x-cloak @click="modal='pendingPayments'"
                class="[&[x-cloak]]:!hidden inline-flex items-center justify-center gap-2
                   h-11 px-4
                   shrink-0
                   rounded-lg
                   border border-amber-200
                   bg-amber-50
                   text-amber-800
                   text-[13px] font-semibold
                   hover:bg-amber-100
                   transition-colors
                   focus-visible:outline-none
                   focus-visible:ring-2
                   focus-visible:ring-amber-500/20">

                <x-icon class="fa-solid fa-qrcode text-[13px]" />

                <span>QRIS</span>

                <span
                    class="inline-flex items-center justify-center
                       min-w-6 h-6 px-1.5
                       rounded-full
                       bg-amber-200
                       text-amber-900
                       text-[10px] font-bold"
                    x-text="pendingPayments.length">
                </span>

            </button>


            {{-- DAPUR --}}
            @if (auth()->user()->tenant->hasBusinessModule('food'))
                <a href="{{ route('kitchen.index') }}"
                    class="inline-flex items-center justify-center gap-2
                       h-11 px-4
                       shrink-0
                       rounded-lg
                       border border-[#dae4dd]
                       bg-white
                       text-[#234a3b]
                       text-[13px] font-semibold
                       hover:bg-[#f1f7f3]
                       hover:border-[#c7d8cd]
                       transition-colors
                       max-[701px]:hidden
                       focus-visible:outline-none
                       focus-visible:ring-2
                       focus-visible:ring-[#17694d]/20"
                    title="Dapur">

                    <x-icon class="fa-solid fa-kitchen-set text-[13px]" />

                    <span>Dapur</span>

                </a>
            @endif


            {{-- ANTREAN --}}
            <button type="button" @click="modal='queue'"
                class="inline-flex items-center justify-center gap-2
                   h-11 px-4
                   shrink-0
                   rounded-lg
                   border border-[#dae4dd]
                   bg-white
                   text-[#234a3b]
                   text-[13px] font-semibold
                   hover:bg-[#f1f7f3]
                   hover:border-[#c7d8cd]
                   transition-colors
                   focus-visible:outline-none
                   focus-visible:ring-2
                   focus-visible:ring-[#17694d]/20">

                <x-icon class="fa-solid fa-cloud-arrow-up text-[13px]" />

                <span>Antrean</span>

                <span
                    class="inline-flex items-center justify-center
                       min-w-6 h-6 px-1.5
                       rounded-full
                       bg-[#edf5ee]
                       text-[#356649]
                       text-[10px] font-bold"
                    x-text="queue.length">
                    0
                </span>

            </button>


            {{-- SHIFT --}}
            <button type="button" @click="shift ? prepareClose() : (modal='openShift')"
                class="inline-flex items-center justify-center gap-2
                   h-11 px-4
                   shrink-0
                   rounded-lg
                   border
                   text-[13px] font-semibold
                   transition-colors
                   focus-visible:outline-none
                   focus-visible:ring-2
                   focus-visible:ring-[#17694d]/20"
                :class="shift
                    ?
                    'border-[#cfe2d5] bg-[#edf7f0] text-[#17694d] hover:bg-[#e2f1e7]' :
                    'border-[#dae4dd] bg-white text-[#234a3b] hover:bg-[#f1f7f3]'">

                <span class="w-2 h-2 rounded-full" :class="shift ? 'bg-emerald-500' : 'bg-slate-300'">
                </span>

                <span x-text="shift ? 'Shift aktif' : 'Buka shift'">
                </span>

                <x-icon class="fa-solid fa-chevron-down
                       text-[9px]
                       opacity-60" />

            </button>


            {{-- BANTUAN --}}
            <a href="{{ route('help') }}"
                class="inline-flex items-center justify-center
                   w-11 h-11
                   shrink-0
                   rounded-lg
                   border border-[#dae4dd]
                   bg-white
                   text-[#234a3b]
                   hover:bg-[#f1f7f3]
                   hover:border-[#c7d8cd]
                   transition-colors
                   focus-visible:outline-none
                   focus-visible:ring-2
                   focus-visible:ring-[#17694d]/20"
                aria-label="Bantuan kasir" title="Bantuan kasir">

                <x-icon class="fa-solid fa-circle-question text-[15px]" />

            </a>

        </div>

    </header>
    <div class="[&[x-cloak]]:!hidden mx-4 mt-4 [padding:13px_16px] rounded-md [background:#fff6df] [color:#81591c] [font-size:13px] [line-height:1.6]"
        x-show="!online" x-cloak>Offline. Transaksi tunai disimpan di perangkat ini dan diperiksa server saat tersambung
        kembali. QRIS membutuhkan koneksi.</div>
    <div class="[&[x-cloak]]:!hidden mx-4 mt-4 [padding:13px_16px] rounded-md [background:#fff6df] [color:#81591c] [font-size:13px] [line-height:1.6]"
        x-show="notice" x-cloak role="status"><span x-text="notice"></span><button class="float-right ml-4"
            @click="notice=''" aria-label="Tutup pemberitahuan">×</button></div>
    <div class="[&[x-cloak]]:!hidden mx-4 mt-4 [padding:13px_16px] rounded-md [background:#fff6df] [color:#81591c] [font-size:13px] [line-height:1.6] ![background:#fff0ee] ![color:#a23225]"
        x-show="error" x-cloak role="alert"><span x-text="error"></span><button class="float-right ml-4"
            @click="error=''" aria-label="Tutup kesalahan">×</button></div>
    <main
        class="grid [grid-template-columns:minmax(0,_1fr)_380px] gap-6 p-6 [max-width:1700px] [margin:auto] max-[1001px]:[grid-template-columns:minmax(0,_1fr)_330px] max-[1001px]:gap-3.5 max-[1001px]:p-4 max-[701px]:block max-[701px]:[padding:16px_16px_92px]"
        :inert="!!modal">
        <section>
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <div class="[font-size:11px] font-bold [letter-spacing:0.14em] uppercase [color:#647c72]">TERMINAL
                        KASIR</div>
                    <h1 class="font-bold [letter-spacing:-0.04em] [line-height:1.2] [margin:8px_0] [font-size:27px]">Ada
                        pesanan apa?</h1>
                </div><button
                    class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px]"
                    @click="modal='drafts'">Pesanan tersimpan <span x-text="drafts.length"
                        class="inline-block [padding:5px_10px] rounded-full [background:#edf5ee] [color:#356649] [font-size:11px] font-semibold"></span></button>
            </div>
            <label
                class="block [font-size:12px] font-semibold [color:#536b5e] [&_input]:block [&_input]:w-full [&_input]:[margin-top:7px] [&_input]:[border:1px_solid_#dce5de] [&_input]:[border-radius:9px] [&_input]:[padding:11px] [&_input]:bg-white [&_input]:[font-size:14px] [&_input]:[color:#18372d] [&_input]:min-h-11 [&_select]:block [&_select]:w-full [&_select]:[margin-top:7px] [&_select]:[border:1px_solid_#dce5de] [&_select]:[border-radius:9px] [&_select]:[padding:11px] [&_select]:bg-white [&_select]:[font-size:14px] [&_select]:[color:#18372d] [&_select]:min-h-11 [&_textarea]:block [&_textarea]:w-full [&_textarea]:[margin-top:7px] [&_textarea]:[border:1px_solid_#dce5de] [&_textarea]:[border-radius:9px] [&_textarea]:[padding:11px] [&_textarea]:bg-white [&_textarea]:[font-size:14px] [&_textarea]:[color:#18372d] [&_textarea]:min-h-11"><span
                    class="sr-only">Cari produk, layanan, atau barcode</span><input x-model.debounce.120ms="search"
                    @keydown.enter.prevent="scan($event.target.value)" type="search"
                    placeholder="Cari produk, layanan, atau barcode…" aria-label="Cari produk, layanan, atau barcode"
                    class="m-0 bg-white"></label>
            <div class="flex gap-2 overflow-auto [padding:5px_0_15px] [&_button]:whitespace-nowrap"><button
                    class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px]"
                    :class="category === 'all' &&
                        '![background:#17694d] !text-white ![border-color:#17694d] hover:![background:#12523d]'"
                    @click="category='all'">Semua item</button><button
                    class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px]"
                    :class="category === 'favorites' &&
                        '![background:#17694d] !text-white ![border-color:#17694d] hover:![background:#12523d]'"
                    @click="category='favorites'">★ Favorit</button>
                @foreach ($categories as $category)
                    <button
                        class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px]"
                        :class="Number(category) === {{ $category->id }} &&
                            '![background:#17694d] !text-white ![border-color:#17694d] hover:![background:#12523d]'"
                        @click="category='{{ $category->id }}'">{{ $category->name }}</button>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-3 mb-3"><label
                    class="block [font-size:12px] font-semibold [color:#536b5e] [&_input]:block [&_input]:w-full [&_input]:[margin-top:7px] [&_input]:[border:1px_solid_#dce5de] [&_input]:[border-radius:9px] [&_input]:[padding:11px] [&_input]:bg-white [&_input]:[font-size:14px] [&_input]:[color:#18372d] [&_input]:min-h-11 [&_select]:block [&_select]:w-full [&_select]:[margin-top:7px] [&_select]:[border:1px_solid_#dce5de] [&_select]:[border-radius:9px] [&_select]:[padding:11px] [&_select]:bg-white [&_select]:[font-size:14px] [&_select]:[color:#18372d] [&_select]:min-h-11 [&_textarea]:block [&_textarea]:w-full [&_textarea]:[margin-top:7px] [&_textarea]:[border:1px_solid_#dce5de] [&_textarea]:[border-radius:9px] [&_textarea]:[padding:11px] [&_textarea]:bg-white [&_textarea]:[font-size:14px] [&_textarea]:[color:#18372d] [&_textarea]:min-h-11">Tampilkan<select
                        x-model="itemType">
                        <option value="all">Barang & jasa</option>
                        <option value="product">Barang</option>
                        <option value="service">Jasa / layanan</option>
                    </select></label></div>
            <div
                class="grid [grid-template-columns:repeat(auto-fill,_minmax(155px,_1fr))] gap-3.5 max-[701px]:[grid-template-columns:repeat(2,_minmax(0,_1fr))] max-[701px]:gap-2.5">
                <template x-for="p in filtered" :key="p.id">
                    <article
                        class="bg-white [border:1px_solid_#e0e8df] [border-radius:14px] overflow-hidden relative [transition:transform_0.15s,_box-shadow_0.15s] hover:[transform:translateY(-2px)] hover:[box-shadow:0_7px_18px_#18372d0b]">
                        <button
                            class="absolute [top:8px] [right:8px] [width:30px] [height:30px] rounded-full bg-white [color:#a1813e] [z-index:1]"
                            @click="favorite(p.id)"
                            :aria-label="(favorites.includes(p.id) ? 'Hapus favorit ' : 'Jadikan favorit ') + p.name"
                            :aria-pressed="favorites.includes(p.id)"
                            x-text="favorites.includes(p.id)?'★':'☆'"></button><button
                            class="block w-full text-left p-3.5 [min-height:173px] max-[701px]:p-3"
                            @click="choose(p)">
                            <div
                                class="[height:66px] [border-radius:9px] [background:#f2eee4] flex items-center justify-center [font-size:28px] [color:#867450] font-bold mb-3 overflow-hidden [&_img]:w-full [&_img]:h-full [&_img]:object-cover">
                                <template x-if="p.image"><img :src="p.image" alt=""
                                        loading="lazy"></template><span x-show="!p.image"
                                    x-text="p.name.slice(0,2).toUpperCase()"></span>
                            </div>
                            <div class="text-sm font-semibold [line-height:1.35]" x-text="p.name">
                            </div>

                            <!-- Harga utama -->
                            <div class="mt-2">
                                <strong class="text-sm [color:#17694d]"
                                    x-text="money(p.price - p.discount) + ' / ' + (p.base_unit || 'pcs')">
                                </strong>
                            </div>

                            <!-- Informasi harga grosir -->
                            <template x-if="p.price_tiers && p.price_tiers.length">
                                <div class="mt-2 [padding:7px_8px] rounded-md [background:#f3f8f4]">
                                    <div class="[font-size:10px] font-semibold [color:#17694d] mb-1">
                                        Harga grosir
                                    </div>

                                    <template
                                        x-for="tier in [...p.price_tiers].sort((a,b) => Number(a.min_quantity) - Number(b.min_quantity))"
                                        :key="tier.min_quantity">

                                        <div
                                            class="flex items-center justify-between gap-2 [font-size:10px] [color:#536b5e]">

                                            <span
                                                x-text="Number(tier.min_quantity).toLocaleString('id-ID') + '+ ' + (p.base_unit || 'pcs')">
                                            </span>

                                            <strong class="[color:#234a3b]" x-text="money(tier.price)">
                                            </strong>
                                        </div>

                                    </template>
                                </div>
                            </template>

                            <!-- Informasi multi-unit -->
                            <template x-if="p.units && p.units.length">
                                <div class="mt-2 [padding:7px_8px] rounded-md [background:#f8f6ef]">

                                    <div class="[font-size:10px] font-semibold [color:#6f623e] mb-1">
                                        Pilihan satuan
                                    </div>

                                    <template x-for="unit in p.units" :key="unit.id">

                                        <div
                                            class="flex items-center justify-between gap-2 [font-size:10px] [color:#65796f]">

                                            <span
                                                x-text="unit.name + ' · ' + Number(unit.factor).toLocaleString('id-ID') + ' ' + (p.base_unit || 'pcs')">
                                            </span>

                                            <strong class="[color:#234a3b]"
                                                x-text="money(unit.price - (unit.discount || 0))">
                                            </strong>

                                        </div>

                                    </template>
                                </div>
                            </template>

                            <!-- Fractional quantity -->
                            <div x-show="p.allow_fraction" class="mt-2 [font-size:10px] [color:#65796f]">

                                ⚖ Bisa dibeli dalam jumlah pecahan
                            </div>

                            <!-- Status produk -->
                            <div class="mt-2 [font-size:10px] [color:#65796f]"
                                x-text="
        p.variants.length
            ? 'Pilihan varian'
            : (
                p.type === 'service'
                    ? 'Jasa · tanpa stok'
                    : (
                        p.tracked
                            ? 'Stok ' + Number(p.stock).toLocaleString('id-ID', {maximumFractionDigits:3}) + ' ' + (p.base_unit || 'pcs')
                            : 'Tersedia'
                    )
            )
    ">
                            </div>
                        </button>
                    </article>
                </template>
            </div>
            <div x-show="!filtered.length"
                class="text-center [color:#75867b] [font-size:13px] bg-white [border:1px_solid_#e1e9e4] rounded-lg [padding:22px] [box-shadow:0_2px_5px_#17392c03] max-[701px]:[padding:18px] mt-4">
                <p>Tidak ada menu yang cocok.</p>
                @if (auth()->user()->role === 'admin')
                    <a class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] mt-4"
                        href="{{ route('getting-started') }}">Siapkan atau impor menu →</a>
                @endif
            </div>
        </section>
        <aside
            class="bg-white [border:1px_solid_#e1e9e4] rounded-lg [padding:22px] [box-shadow:0_2px_5px_#17392c03] max-[701px]:[padding:18px] sticky [top:20px] [align-self:start] [max-height:calc(100vh_-_115px)] overflow-auto p-5 max-[701px]:relative max-[701px]:[max-height:none] max-[701px]:[margin-top:22px]"
            id="order-cart">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-bold">Pesanan sekarang</h2><span
                    class="inline-block [padding:5px_10px] rounded-full [background:#edf5ee] [color:#356649] [font-size:11px] font-semibold"
                    x-text="itemCount+' item'"></span>
            </div>
            <div class="grid [gap:18px] mt-4 [grid-template-columns:1fr_1fr] gap-2.5"><label
                    class="block [font-size:12px] font-semibold [color:#536b5e] [&_input]:block [&_input]:w-full [&_input]:[margin-top:7px] [&_input]:[border:1px_solid_#dce5de] [&_input]:[border-radius:9px] [&_input]:[padding:11px] [&_input]:bg-white [&_input]:[font-size:14px] [&_input]:[color:#18372d] [&_input]:min-h-11 [&_select]:block [&_select]:w-full [&_select]:[margin-top:7px] [&_select]:[border:1px_solid_#dce5de] [&_select]:[border-radius:9px] [&_select]:[padding:11px] [&_select]:bg-white [&_select]:[font-size:14px] [&_select]:[color:#18372d] [&_select]:min-h-11 [&_textarea]:block [&_textarea]:w-full [&_textarea]:[margin-top:7px] [&_textarea]:[border:1px_solid_#dce5de] [&_textarea]:[border-radius:9px] [&_textarea]:[padding:11px] [&_textarea]:bg-white [&_textarea]:[font-size:14px] [&_textarea]:[color:#18372d] [&_textarea]:min-h-11">Jenis
                    pesanan<select x-model="orderType">
                        <option value="takeaway">Langsung / ambil sendiri</option>
                        @if (auth()->user()->tenant->hasBusinessModule('food'))
                            <option value="dine_in">Makan di tempat</option>
                        @endif
                        <option value="delivery">
                            Diantar</option>
                    </select></label><label
                    class="block [font-size:12px] font-semibold [color:#536b5e] [&_input]:block [&_input]:w-full [&_input]:[margin-top:7px] [&_input]:[border:1px_solid_#dce5de] [&_input]:[border-radius:9px] [&_input]:[padding:11px] [&_input]:bg-white [&_input]:[font-size:14px] [&_input]:[color:#18372d] [&_input]:min-h-11 [&_select]:block [&_select]:w-full [&_select]:[margin-top:7px] [&_select]:[border:1px_solid_#dce5de] [&_select]:[border-radius:9px] [&_select]:[padding:11px] [&_select]:bg-white [&_select]:[font-size:14px] [&_select]:[color:#18372d] [&_select]:min-h-11 [&_textarea]:block [&_textarea]:w-full [&_textarea]:[margin-top:7px] [&_textarea]:[border:1px_solid_#dce5de] [&_textarea]:[border-radius:9px] [&_textarea]:[padding:11px] [&_textarea]:bg-white [&_textarea]:[font-size:14px] [&_textarea]:[color:#18372d] [&_textarea]:min-h-11"
                    x-show="orderType==='dine_in'">Nomor meja<input x-model="table" maxlength="30"
                        placeholder="Mis. 04"></label></div>
            <div class="[padding:36px_18px] text-center [color:#75867b] [font-size:13px]" x-show="!cart.length">
                <div
                    class="w-9 h-9 grid place-items-center rounded-full [background:#edf5ee] [color:#17694d] font-bold shrink-0 mx-auto mb-3">
                    ＋</div>Pilih produk atau layanan untuk memulai.<br><span
                    class="[color:#65796f] [font-size:13px] [line-height:1.6]">Pilihan dan catatan bisa
                    ditambahkan.</span>
            </div>
            <template x-for="line in cart" :key="line.key">
                <div class="py-4 border-b border-[#e9eee8] last:border-b-0">

                    {{-- Nama + total item --}}
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <strong class="block text-sm font-semibold leading-5 text-[#18372d]" x-text="line.name">
                            </strong>

                            <div x-show="line.addons.length" class="mt-1 text-xs leading-5 text-[#65796f]"
                                x-text="line.addons.join(', ')">
                            </div>

                            <div x-show="line.note" class="mt-1 text-xs leading-5 text-[#65796f]" x-text="line.note">
                            </div>
                        </div>

                        <strong class="shrink-0 text-sm font-semibold text-[#18372d]"
                            x-text="money((line.price - line.discount) * line.quantity)">
                        </strong>
                    </div>

                    {{-- Harga satuan / metadata --}}
                    <div class="flex items-center gap-2 mt-2">
                        <span class="text-xs leading-5 text-[#65796f]"
                            x-text="
                    money(line.price - line.discount)
                    + ' / '
                    + (line.unit_name || 'pcs')
                ">
                        </span>

                        <span x-show="cartTier(line)"
                            class="inline-flex items-center rounded-full
                       bg-[#edf5ee]
                       px-2 py-0.5
                       text-[9px] font-bold tracking-wide
                       text-[#17694d]">
                            HARGA GROSIR
                        </span>
                    </div>

                    {{-- Quantity --}}
                    <div class="flex items-center justify-end mt-3">
                        <div
                            class="flex items-center gap-2
                       [&_button]:h-9
                       [&_button]:w-9
                       [&_button]:rounded-md
                       [&_button]:border
                       [&_button]:border-[#dfe8df]
                       [&_button]:bg-[#f7f9f5]
                       [&_button]:font-bold
                       [&_button]:transition-colors
                       [&_button:hover]:bg-[#edf5ee]">

                            <button type="button" @click="quantity(line.key,-1)"
                                :aria-label="'Kurangi ' + line.name">
                                −
                            </button>

                            <input
                                class="h-9 w-[72px] rounded-md
                           border border-[#d7e0d9]
                           bg-white px-2 text-center
                           text-sm font-semibold text-[#18372d]
                           focus:border-[#17694d]
                           focus:outline-none
                           focus:ring-2 focus:ring-[#17694d]/10"
                                type="number" :value="line.quantity" :step="line.allow_fraction ? 0.001 : 1"
                                min="0" max="100000" :aria-label="'Jumlah ' + line.name"
                                @change="setQuantity(line.key, $event.target.value)">

                            <button type="button" @click="quantity(line.key,1)" :aria-label="'Tambah ' + line.name">
                                +
                            </button>
                        </div>
                    </div>

                </div>
            </template>
            @can('feature-crm')<label
                    class="block [font-size:12px] font-semibold [color:#536b5e] [&_input]:block [&_input]:w-full [&_input]:[margin-top:7px] [&_input]:[border:1px_solid_#dce5de] [&_input]:[border-radius:9px] [&_input]:[padding:11px] [&_input]:bg-white [&_input]:[font-size:14px] [&_input]:[color:#18372d] [&_input]:min-h-11 [&_select]:block [&_select]:w-full [&_select]:[margin-top:7px] [&_select]:[border:1px_solid_#dce5de] [&_select]:[border-radius:9px] [&_select]:[padding:11px] [&_select]:bg-white [&_select]:[font-size:14px] [&_select]:[color:#18372d] [&_select]:min-h-11 [&_textarea]:block [&_textarea]:w-full [&_textarea]:[margin-top:7px] [&_textarea]:[border:1px_solid_#dce5de] [&_textarea]:[border-radius:9px] [&_textarea]:[padding:11px] [&_textarea]:bg-white [&_textarea]:[font-size:14px] [&_textarea]:[color:#18372d] [&_textarea]:min-h-11 mt-4">Pelanggan
                    / member<select x-model="customer">
                        <option value="">Pelanggan umum</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->name }}{{ $customer->is_member ? ' · Member' : '' }}</option>
                        @endforeach
                    </select>
            </label>@endcan
            <label
                class="block [font-size:12px] font-semibold [color:#536b5e] [&_input]:block [&_input]:w-full [&_input]:[margin-top:7px] [&_input]:[border:1px_solid_#dce5de] [&_input]:[border-radius:9px] [&_input]:[padding:11px] [&_input]:bg-white [&_input]:[font-size:14px] [&_input]:[color:#18372d] [&_input]:min-h-11 [&_select]:block [&_select]:w-full [&_select]:[margin-top:7px] [&_select]:[border:1px_solid_#dce5de] [&_select]:[border-radius:9px] [&_select]:[padding:11px] [&_select]:bg-white [&_select]:[font-size:14px] [&_select]:[color:#18372d] [&_select]:min-h-11 [&_textarea]:block [&_textarea]:w-full [&_textarea]:[margin-top:7px] [&_textarea]:[border:1px_solid_#dce5de] [&_textarea]:[border-radius:9px] [&_textarea]:[padding:11px] [&_textarea]:bg-white [&_textarea]:[font-size:14px] [&_textarea]:[color:#18372d] [&_textarea]:min-h-11 mt-4">Catatan
                pesanan<input x-model="orderNote" placeholder="Mis. bungkus terpisah" maxlength="300"></label>
            <div
                class="[&_>_div]:[padding:13px_0] [&_>_div]:[border-bottom:1px_solid_#edf1ee] [&>a:not(.flex):not(.inline-flex)]:block [&_>_a]:[padding:13px_0] [&_>_a]:[border-bottom:1px_solid_#edf1ee] [&_>_:last-child]:[border-bottom:0] mt-4 text-sm">
                <div class="flex flex-wrap items-center justify-between gap-3"><span
                        class="[color:#65796f] [font-size:13px] [line-height:1.6]">Subtotal</span><span
                        x-text="money(amounts.subtotal)"></span></div>
                <div class="flex flex-wrap items-center justify-between gap-3" x-show="amounts.discount"><span
                        class="[color:#65796f] [font-size:13px] [line-height:1.6]">Diskon menu</span><span
                        x-text="'-'+money(amounts.discount)"></span></div>
                <div class="flex flex-wrap items-center justify-between gap-3" x-show="amounts.tax"><span
                        class="[color:#65796f] [font-size:13px] [line-height:1.6]">Pajak</span><span
                        x-text="money(amounts.tax)"></span></div>
                <div class="flex flex-wrap items-center justify-between gap-3"><strong>Total</strong><strong
                        class="text-xl" x-text="money(amounts.total)"></strong></div>
            </div>
            <button
                class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] ![background:#17694d] !text-white ![border-color:#17694d] hover:![background:#12523d] w-full mt-3"
                @click="pay()" :disabled="!cart.length || busy">Lanjut pembayaran →</button><button
                class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] w-full mt-2"
                @click="hold()" :disabled="!cart.length">Simpan pesanan sementara</button>
            <p class="[color:#65796f] [line-height:1.6] text-center mt-3 [font-size:10px]">Draft disimpan pada
                perangkat ini.</p>
        </aside>
    </main>
    <button
        class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] ![background:#17694d] !text-white ![border-color:#17694d] hover:![background:#12523d] hidden max-[701px]:fixed max-[701px]:[bottom:15px] max-[701px]:[left:16px] max-[701px]:[right:16px] max-[701px]:flex max-[701px]:[z-index:30] max-[701px]:[box-shadow:0_6px_20px_#163b2830] justify-between"
        :inert="!!modal"
        @click="document.getElementById('order-cart').scrollIntoView({behavior:'smooth'})"><span
            x-text="itemCount+' item · Lihat pesanan'"></span><strong x-text="money(amounts.total)"></strong></button>
    <div class="[&[x-cloak]]:!hidden fixed inset-0 [z-index:80] [background:#102b2266] flex items-center justify-center [padding:18px] [backdrop-filter:blur(3px)] [&_h2]:[font-size:22px] [&_h2]:font-bold"
        x-show="modal" x-cloak @keydown.escape.window="if(!busy)modal=''" @keydown.tab="trapFocus($event)"
        role="dialog" aria-modal="true" aria-label="Detail kasir">
        <div data-dialog-panel
            class="w-full [max-width:480px] [max-height:90vh] overflow-auto bg-white [border-radius:18px] p-6"><button
                class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] float-right"
                @click="modal=''" :disabled="busy" aria-label="Tutup dialog">×</button>
            <p class="[padding:13px_16px] rounded-md [background:#fff6df] [color:#81591c] [font-size:13px] [line-height:1.6] ![background:#fff0ee] ![color:#a23225] mb-3 clear-both"
                x-show="error" x-text="error" role="alert"></p>
            <template x-if="modal==='item' && selected">
                <section>
                    <h2 x-text="selected.name"></h2>
                    <p class="[color:#65796f] [font-size:13px] [line-height:1.6] mt-2">Sesuaikan pilihan pesanan.</p>
                    <!-- Ringkasan harga -->
                    <div class="mt-4 [padding:14px] rounded-lg [background:#f7f9f6] [border:1px_solid_#e3eae4]">

                        <div class="[font-size:11px] [color:#65796f]">
                            Harga satuan
                        </div>

                        <div class="mt-1 text-lg font-bold [color:#17694d]"
                            x-text="money(selected.price - selected.discount) + ' / ' + (selected.base_unit || 'pcs')">
                        </div>

                        <!-- Harga grosir -->
                        <template x-if="selected.price_tiers && selected.price_tiers.length">

                            <div class="mt-4">

                                <div class="text-xs font-bold [color:#234a3b]">
                                    Harga grosir
                                </div>

                                <div class="mt-2 overflow-hidden rounded-md [border:1px_solid_#e1e9e4]">

                                    <template
                                        x-for="tier in [...selected.price_tiers].sort((a,b) => Number(a.min_quantity) - Number(b.min_quantity))"
                                        :key="tier.min_quantity">

                                        <div
                                            class="flex items-center justify-between gap-4 [padding:9px_11px] [border-bottom:1px_solid_#edf1ee] last:border-0">

                                            <span class="[font-size:12px] [color:#65796f]"
                                                x-text="'Minimal ' + Number(tier.min_quantity).toLocaleString('id-ID') + ' ' + (selected.base_unit || 'pcs')">
                                            </span>

                                            <strong class="text-xs [color:#17694d]"
                                                x-text="money(tier.price) + ' / ' + (selected.base_unit || 'pcs')">
                                            </strong>

                                        </div>

                                    </template>

                                </div>

                            </div>

                        </template>

                        <!-- Multi unit -->
                        <template x-if="selected.units && selected.units.length">

                            <div class="mt-4">

                                <div class="text-xs font-bold [color:#234a3b]">
                                    Pilihan satuan
                                </div>

                                <template x-for="unit in selected.units" :key="unit.id">

                                    <div class="flex items-center justify-between gap-3 mt-2 text-xs">

                                        <span class="[color:#65796f]"
                                            x-text="
                            unit.name +
                            ' · isi ' +
                            Number(unit.factor).toLocaleString('id-ID') +
                            ' ' +
                            (selected.base_unit || 'pcs')
                        ">
                                        </span>

                                        <strong x-text="money(unit.price - (unit.discount || 0))">
                                        </strong>

                                    </div>

                                </template>

                            </div>

                        </template>

                        <!-- Fractional -->
                        <div x-show="selected.allow_fraction"
                            class="mt-4 [padding:9px_11px] rounded-md [background:#edf5ee] [font-size:12px] [color:#356649]">

                            ✓ Bisa dibeli dalam jumlah pecahan,
                            misalnya 0,5 atau 1,5
                            <span x-text="selected.base_unit || 'pcs'"></span>.

                        </div>

                    </div>
                    <div class="grid gap-3 mt-4 sm:grid-cols-2">
                        <label class="text-sm">Satuan jual<select class="w-full mt-1 border rounded-md"
                                x-model="saleUnit" @change="variant=''">
                                <option value="" x-text="selected.base_unit || 'pcs'"></option>
                                <template x-for="u in (selected.units || [])" :key="u.id">
                                    <option :value="u.id"
                                        x-text="u.name+' ('+u.factor+' '+selected.base_unit+')'"></option>
                                </template>
                            </select>
                        </label>
                        <label class="text-sm">Jumlah<input class="w-full mt-1 border rounded-md" type="number"
                                x-model.number="itemQuantity" :min="selected.allow_fraction ? 0.001 : 1"
                                :step="selected.allow_fraction ? 0.001 : 1" max="100000"></label>
                    </div>
                    <!-- Status harga aktif -->
                    <div class="mt-3">

                        <!-- Harga grosir sedang aktif -->
                        <template x-if="activeTier">
                            <div
                                class="rounded-lg [border:1px_solid_#cfe2d5] [background:#edf7f0] [padding:12px_14px]">

                                <div class="flex items-start justify-between gap-4">

                                    <div>
                                        <div class="text-xs font-bold [color:#17694d]">
                                            ✓ Harga grosir diterapkan
                                        </div>

                                        <div class="mt-1 [font-size:11px] [color:#65796f]"
                                            x-text="
                            'Karena membeli minimal ' +
                            formatQuantity(activeTier.min_quantity) +
                            ' ' +
                            (selected.base_unit || 'pcs')
                        ">
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <div class="text-sm font-bold [color:#17694d]"
                                            x-text="
                            money(
                                selectedUnit.price -
                                selectedUnit.discount
                            )
                            + ' / '
                            + (selected.base_unit || 'pcs')
                        ">
                                        </div>

                                        <div x-show="wholesaleSaving > 0"
                                            class="mt-1 [font-size:10px] [color:#3c7757]"
                                            x-text="'Hemat ' + money(wholesaleSaving)">
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </template>

                        <!-- Belum mencapai harga grosir -->
                        <template x-if="!activeTier && nextTier">
                            <div
                                class="rounded-lg [border:1px_solid_#eadfbd] [background:#fffaf0] [padding:11px_13px]">

                                <div class="[font-size:11px] [color:#715f31]"
                                    x-text="
                    'Tambah ' +
                    formatQuantity(
                        Number(nextTier.min_quantity) -
                        Number(itemQuantity)
                    ) +
                    ' ' +
                    (selected.base_unit || 'pcs') +
                    ' lagi untuk harga ' +
                    money(nextTier.price) +
                    ' / ' +
                    (selected.base_unit || 'pcs')
                ">
                                </div>

                            </div>
                        </template>

                    </div>
                    <label
                        class="block [font-size:12px] font-semibold [color:#536b5e] [&_input]:block [&_input]:w-full [&_input]:[margin-top:7px] [&_input]:[border:1px_solid_#dce5de] [&_input]:[border-radius:9px] [&_input]:[padding:11px] [&_input]:bg-white [&_input]:[font-size:14px] [&_input]:[color:#18372d] [&_input]:min-h-11 [&_select]:block [&_select]:w-full [&_select]:[margin-top:7px] [&_select]:[border:1px_solid_#dce5de] [&_select]:[border-radius:9px] [&_select]:[padding:11px] [&_select]:bg-white [&_select]:[font-size:14px] [&_select]:[color:#18372d] [&_select]:min-h-11 [&_textarea]:block [&_textarea]:w-full [&_textarea]:[margin-top:7px] [&_textarea]:[border:1px_solid_#dce5de] [&_textarea]:[border-radius:9px] [&_textarea]:[padding:11px] [&_textarea]:bg-white [&_textarea]:[font-size:14px] [&_textarea]:[color:#18372d] [&_textarea]:min-h-11 mt-5"
                        x-show="selected.variants.length">Ukuran / varian<select x-model="variant"
                            @change="saleUnit=''">
                            <option value="">Reguler</option><template x-for="v in selected.variants"
                                :key="v.id">
                                <option :value="v.id" x-text="v.name+' · '+money(v.price-v.discount)">
                                </option>
                            </template>
                        </select></label>
                    <div class="mt-4"><template x-for="a in selected.addons" :key="a.id"><label
                                class="flex flex-wrap items-center gap-3 py-3 text-sm border-b"><input type="checkbox"
                                    :value="a.id" x-model.number="addonIds"><span x-text="a.name"
                                    class="flex-1"></span><span x-text="'+'+money(a.price)"></span></label></template>
                    </div><label
                        class="block [font-size:12px] font-semibold [color:#536b5e] [&_input]:block [&_input]:w-full [&_input]:[margin-top:7px] [&_input]:[border:1px_solid_#dce5de] [&_input]:[border-radius:9px] [&_input]:[padding:11px] [&_input]:bg-white [&_input]:[font-size:14px] [&_input]:[color:#18372d] [&_input]:min-h-11 [&_select]:block [&_select]:w-full [&_select]:[margin-top:7px] [&_select]:[border:1px_solid_#dce5de] [&_select]:[border-radius:9px] [&_select]:[padding:11px] [&_select]:bg-white [&_select]:[font-size:14px] [&_select]:[color:#18372d] [&_select]:min-h-11 [&_textarea]:block [&_textarea]:w-full [&_textarea]:[margin-top:7px] [&_textarea]:[border:1px_solid_#dce5de] [&_textarea]:[border-radius:9px] [&_textarea]:[padding:11px] [&_textarea]:bg-white [&_textarea]:[font-size:14px] [&_textarea]:[color:#18372d] [&_textarea]:min-h-11 mt-4">Catatan
                        item<input x-model="itemNote" maxlength="300"
                            placeholder="Mis. less ice, tanpa gula"></label><button
                        class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] ![background:#17694d] !text-white ![border-color:#17694d] hover:![background:#12523d] w-full mt-5"
                        @click="addItem()">Tambahkan · <span
                            x-text="money(Math.round(selectedUnit.price*itemQuantity)-Math.round(selectedUnit.discount*itemQuantity))"></span></button>
                </section>
            </template>
            <section x-show="modal==='history'">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-[#789085]">Aktivitas kasir</p>
                        <h2 class="mt-1 text-xl font-bold text-[#18372d]">Riwayat transaksi</h2>
                        <p class="mt-1 text-xs leading-5 text-[#65796f]">20 transaksi terakhir yang Anda proses.</p>
                    </div>
                    <span class="grid w-10 h-10 place-items-center rounded-full bg-[#edf5ee] text-[#17694d] shrink-0">
                        <x-icon class="fa-solid fa-clock-rotate-left" />
                    </span>
                </div>

                <div class="space-y-2">
                    @forelse ($recentOrders as $order)
                        <a href="{{ route('orders.show', $order) }}"
                            class="block p-4 transition-colors bg-white border border-[#e1e9e4] rounded-xl hover:border-[#bcd2c4] hover:bg-[#f8fbf9]">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="grid w-8 h-8 place-items-center rounded-lg bg-[#edf5ee] text-[#17694d] shrink-0">
                                            <x-icon class="text-xs fa-solid fa-receipt" />
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-[#18372d] truncate">
                                                {{ $order->invoice_number }}</p>
                                            <p class="mt-0.5 text-[10px] text-[#75867b]">
                                                {{ optional($order->sold_at ?? $order->created_at)->format('d M Y · H:i') }}
                                                · {{ $order->customer->name ?? 'Pelanggan umum' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-[#18372d]">Rp
                                        {{ number_format($order->grand_total, 0, ',', '.') }}</p>
                                    <span
                                        class="inline-flex items-center gap-1 mt-1 text-[9px] font-bold {{ $order->payment_status === 'paid' ? 'text-[#17694d]' : 'text-amber-700' }}">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full {{ $order->payment_status === 'paid' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                        {{ $order->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
                                    </span>
                                </div>
                            </div>
                            <div
                                class="flex items-center justify-between mt-3 pt-3 border-t border-[#edf1ee] text-[11px]">
                                <span class="text-[#65796f]">{{ $order->items->count() }} jenis item</span>
                                <span class="font-semibold text-[#17694d]">Detail & struk <x-icon
                                        class="ml-1 fa-solid fa-chevron-right text-[8px]" /></span>
                            </div>
                        </a>
                    @empty
                        <div class="py-12 text-center">
                            <span
                                class="grid w-12 h-12 mx-auto place-items-center rounded-full bg-[#edf5ee] text-[#17694d]"><x-icon
                                    class="fa-solid fa-receipt" /></span>
                            <h3 class="mt-3 text-sm font-bold text-[#18372d]">Belum ada transaksi</h3>
                            <p class="mt-1 text-xs text-[#75867b]">Transaksi yang Anda proses akan muncul di sini.</p>
                        </div>
                    @endforelse
                </div>

                @if (auth()->user()->role !== 'kasir')
                    <a href="{{ route('orders.index') }}"
                        class="flex items-center justify-center w-full h-11 gap-2 mt-4 rounded-lg border border-[#d7e2da] text-xs font-semibold text-[#234a3b] hover:bg-[#f1f7f3]">
                        Lihat seluruh riwayat <x-icon class="text-[10px] fa-solid fa-arrow-right" />
                    </a>
                @endif
            </section>

            <section x-show="modal==='pay'">
                <h2>Pembayaran</h2>
                <p class="[font-size:clamp(24px,_3vw,_32px)] font-bold [letter-spacing:-0.04em] [margin:12px_0_7px] max-[701px]:[font-size:24px]"
                    x-text="money(amounts.total)"></p>
                <div class="flex flex-wrap items-center gap-3 mt-4"><button
                        class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px]"
                        :class="method === 'cash' &&
                            '![background:#17694d] !text-white ![border-color:#17694d] hover:![background:#12523d]'"
                        @click="method='cash'">Tunai</button>
                    @can('feature-qris')
                        <button
                            class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px]"
                            :class="method === 'midtrans' &&
                                '![background:#17694d] !text-white ![border-color:#17694d] hover:![background:#12523d]'"
                            @click="method='midtrans'" :disabled="!online">QRIS</button>
                    @endcan
                </div>
                <form @submit.prevent="submit()">
                    <div x-show="method==='cash'"><label
                            class="block [font-size:12px] font-semibold [color:#536b5e] [&_input]:block [&_input]:w-full [&_input]:[margin-top:7px] [&_input]:[border:1px_solid_#dce5de] [&_input]:[border-radius:9px] [&_input]:[padding:11px] [&_input]:bg-white [&_input]:[font-size:14px] [&_input]:[color:#18372d] [&_input]:min-h-11 [&_select]:block [&_select]:w-full [&_select]:[margin-top:7px] [&_select]:[border:1px_solid_#dce5de] [&_select]:[border-radius:9px] [&_select]:[padding:11px] [&_select]:bg-white [&_select]:[font-size:14px] [&_select]:[color:#18372d] [&_select]:min-h-11 [&_textarea]:block [&_textarea]:w-full [&_textarea]:[margin-top:7px] [&_textarea]:[border:1px_solid_#dce5de] [&_textarea]:[border-radius:9px] [&_textarea]:[padding:11px] [&_textarea]:bg-white [&_textarea]:[font-size:14px] [&_textarea]:[color:#18372d] [&_textarea]:min-h-11 mt-5">Uang
                            diterima (Rp)<input type="text" inputmode="numeric" :value="currencyInput(cash)"
                                @input="setCurrencyField('cash', $event.target.value); $event.target.value=currencyInput(cash)"
                                :required="method === 'cash'"></label>
                        <div class="flex flex-wrap items-center gap-3 mt-3"><button type="button"
                                class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px]"
                                @click="cash=amounts.total">Uang pas</button><template
                                x-for="amount in [20000,50000,100000]" :key="amount"><button type="button"
                                    class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px]"
                                    x-show="amount>=amounts.total" @click="cash=amount"
                                    x-text="money(amount)"></button></template></div>
                        <p class="[color:#65796f] [font-size:13px] [line-height:1.6] mt-4">Kembalian <strong
                                x-text="money(Math.max(0,Number(cash)-amounts.total))"></strong></p>
                    </div>
                    <p class="[color:#65796f] [font-size:13px] [line-height:1.6] mt-4" x-show="method==='midtrans'">
                        Buat QR, lalu tunggu konfirmasi pembayaran dari server.</p><button
                        class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] ![background:#17694d] !text-white ![border-color:#17694d] hover:![background:#12523d] w-full mt-5"
                        :disabled="busy"
                        x-text="busy?'Memproses…':(!online?'Simpan transaksi tunai di perangkat':'Konfirmasi pembayaran')"></button>
                </form>
            </section>
            <section x-show="modal==='drafts'">
                <h2>Pesanan tersimpan</h2>
                <p class="[color:#65796f] [font-size:13px] [line-height:1.6] mt-2">Belum dibayar dan belum memotong
                    stok.</p><template x-for="draft in drafts" :key="draft.id"><button
                        class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] justify-between w-full mt-3"
                        @click="resume(draft.id)"><span x-text="draft.name"></span><span>Buka
                            →</span></button></template>
                <p class="[padding:36px_18px] text-center [color:#75867b] [font-size:13px]" x-show="!drafts.length">
                    Belum ada draft pesanan.</p>
            </section>
            <section x-show="modal==='queue'">
                <h2>Antrean perangkat</h2>
                <p class="[color:#65796f] [font-size:13px] [line-height:1.6] mt-2">Transaksi di sini belum
                    terkonfirmasi tersimpan di server. Jangan menerima pembayaran kedua kali.</p>
                <div class="flex flex-wrap items-center gap-3 mt-4"><button
                        class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px]"
                        @click="syncQueue()" :disabled="syncing || busy || !online"
                        x-text="syncing?'Menyinkronkan…':'Coba sinkronkan tunai'"></button><button
                        class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px]"
                        @click="backupQueue()" :disabled="!queue.length">Unduh cadangan</button></div><template
                    x-for="entry in queue" :key="entry.payload.checkout_key">
                    <div
                        class="bg-white [border:1px_solid_#e1e9e4] rounded-lg [padding:22px] [box-shadow:0_2px_5px_#17392c03] max-[701px]:[padding:18px] mt-4">
                        <strong x-text="money(entry.payload.grand_total)"></strong>
                        <p class="[color:#65796f] [font-size:13px] [line-height:1.6]"
                            x-text="entry.payload.payment_method+' · '+new Date(entry.payload.sold_at).toLocaleString('id-ID')">
                        </p>
                        <p class="[color:#65796f] [line-height:1.6] break-all [font-size:10px]"
                            x-text="entry.payload.checkout_key"></p>
                        <p class="[padding:13px_16px] rounded-md [background:#fff6df] [color:#81591c] [font-size:13px] [line-height:1.6] mt-3"
                            x-show="entry.error" x-text="entry.error"></p><button
                            class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] mt-3"
                            @click="retry(entry)" :disabled="!online || busy || syncing">Periksa / kirim
                            ulang</button>
                    </div>
                </template>
                <p class="[padding:36px_18px] text-center [color:#75867b] [font-size:13px]" x-show="!queue.length">✓
                    Semua transaksi sudah tersinkron.</p><a
                    class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] mt-4"
                    href="{{ route('help') }}">Cara menangani antrean tertahan</a>
            </section>
            <section x-show="modal==='pendingPayments'">
                <h2>QRIS menunggu konfirmasi</h2>
                <p class="[color:#65796f] [font-size:13px] [line-height:1.6] mt-2">Buka kembali pembayaran yang sudah
                    dibuat. Periksa status sebelum menerima pembayaran lain.</p><template
                    x-for="pending in pendingPayments" :key="pending.order_id">
                    <div
                        class="bg-white [border:1px_solid_#e1e9e4] rounded-lg [padding:22px] [box-shadow:0_2px_5px_#17392c03] max-[701px]:[padding:18px] mt-3">
                        <p class="break-all" x-text="pending.invoice_number"></p><strong
                            x-text="money(pending.grand_total)"></strong><button
                            class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] mt-3"
                            @click="payment=pending;modal='qris';checkPayment()">Buka dan periksa</button>
                    </div>
                </template>
            </section>
            <template x-if="modal==='qris' && payment">
                <section>
                    <h2>Scan untuk membayar</h2>
                    <p class="[font-size:clamp(24px,_3vw,_32px)] font-bold [letter-spacing:-0.04em] [margin:12px_0_7px] max-[701px]:[font-size:24px]"
                        x-text="money(payment.grand_total)"></p><template x-if="payment.qr_url"><img
                            :src="payment.qr_url" alt="Kode QRIS pembayaran"
                            class="object-contain w-64 h-64 mx-auto"></template>
                    <p x-show="!payment.qr_url"
                        class="[padding:13px_16px] rounded-md [background:#fff6df] [color:#81591c] [font-size:13px] [line-height:1.6]">
                        Kode QR belum tersedia. Periksa status nota ini sebelum membuat pembayaran lain.</p>
                    <p class="[color:#65796f] [font-size:13px] [line-height:1.6] mt-3">Tunggu konfirmasi server. QR ini
                        dapat dibuka kembali melalui tombol QRIS menunggu.</p><button
                        class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] ![background:#17694d] !text-white ![border-color:#17694d] hover:![background:#12523d] w-full mt-4"
                        @click="checkPayment()" :disabled="busy"
                        x-text="busy?'Memeriksa…':'Periksa pembayaran'"></button>
                </section>
            </template>
            <template x-if="modal==='receipt' && receipt">
                <section><span
                        class="inline-block [padding:5px_10px] rounded-full [background:#edf5ee] [color:#356649] [font-size:11px] font-semibold">TERCATAT
                        DI SERVER</span>
                    <h2 class="mt-3">Transaksi berhasil.</h2>
                    <p class="[font-size:clamp(24px,_3vw,_32px)] font-bold [letter-spacing:-0.04em] [margin:12px_0_7px] max-[701px]:[font-size:24px]"
                        x-text="money(receipt.grand_total)"></p>
                    <p class="[color:#65796f] [font-size:13px] [line-height:1.6] break-all"
                        x-text="receipt.invoice_number"></p><a
                        class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] ![background:#17694d] !text-white ![border-color:#17694d] hover:![background:#12523d] w-full mt-5"
                        :href="'/orders/' + receipt.order_id + '/print'" target="_blank" rel="noopener">Cetak struk
                        ↗</a><button
                        class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] w-full mt-2"
                        @click="location.reload()">Pesanan berikutnya</button>
                </section>
            </template>
            <section x-show="modal==='openShift'">
                <h2>Siap buka kedai?</h2>
                <p class="[color:#65796f] [font-size:13px] [line-height:1.6] mt-2">Catat modal tunai yang ada di laci
                    sebelum transaksi pertama.</p>
                <form @submit.prevent="openShift()"><label
                        class="block [font-size:12px] font-semibold [color:#536b5e] [&_input]:block [&_input]:w-full [&_input]:[margin-top:7px] [&_input]:[border:1px_solid_#dce5de] [&_input]:[border-radius:9px] [&_input]:[padding:11px] [&_input]:bg-white [&_input]:[font-size:14px] [&_input]:[color:#18372d] [&_input]:min-h-11 [&_select]:block [&_select]:w-full [&_select]:[margin-top:7px] [&_select]:[border:1px_solid_#dce5de] [&_select]:[border-radius:9px] [&_select]:[padding:11px] [&_select]:bg-white [&_select]:[font-size:14px] [&_select]:[color:#18372d] [&_select]:min-h-11 [&_textarea]:block [&_textarea]:w-full [&_textarea]:[margin-top:7px] [&_textarea]:[border:1px_solid_#dce5de] [&_textarea]:[border-radius:9px] [&_textarea]:[padding:11px] [&_textarea]:bg-white [&_textarea]:[font-size:14px] [&_textarea]:[color:#18372d] [&_textarea]:min-h-11 mt-5">Modal
                        awal (Rp)<input type="text" inputmode="numeric" :value="currencyInput(shiftCash)"
                            @input="setCurrencyField('shiftCash', $event.target.value); $event.target.value=currencyInput(shiftCash)"
                            required></label><button
                        class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] ![background:#17694d] !text-white ![border-color:#17694d] hover:![background:#12523d] w-full mt-5"
                        :disabled="busy">Buka shift</button></form>
            </section>
            <section x-show="modal==='closeShift'">
                <h2>Tutup shift</h2>
                <p class="[color:#65796f] [font-size:13px] [line-height:1.6] mt-2">Pastikan antrean di perangkat lain
                    juga sudah tersinkron.</p>
                <p class="[color:#65796f] [font-size:13px] [line-height:1.6] mt-5">Kas yang diharapkan</p>
                <p class="[font-size:clamp(24px,_3vw,_32px)] font-bold [letter-spacing:-0.04em] [margin:12px_0_7px] max-[701px]:[font-size:24px]"
                    x-text="money(shiftSummary?.cash_expected)"></p>
                <form @submit.prevent="closeShift()"><label
                        class="block [font-size:12px] font-semibold [color:#536b5e] [&_input]:block [&_input]:w-full [&_input]:[margin-top:7px] [&_input]:[border:1px_solid_#dce5de] [&_input]:[border-radius:9px] [&_input]:[padding:11px] [&_input]:bg-white [&_input]:[font-size:14px] [&_input]:[color:#18372d] [&_input]:min-h-11 [&_select]:block [&_select]:w-full [&_select]:[margin-top:7px] [&_select]:[border:1px_solid_#dce5de] [&_select]:[border-radius:9px] [&_select]:[padding:11px] [&_select]:bg-white [&_select]:[font-size:14px] [&_select]:[color:#18372d] [&_select]:min-h-11 [&_textarea]:block [&_textarea]:w-full [&_textarea]:[margin-top:7px] [&_textarea]:[border:1px_solid_#dce5de] [&_textarea]:[border-radius:9px] [&_textarea]:[padding:11px] [&_textarea]:bg-white [&_textarea]:[font-size:14px] [&_textarea]:[color:#18372d] [&_textarea]:min-h-11">Uang
                        fisik di laci (Rp)<input type="text" inputmode="numeric" :value="currencyInput(shiftCash)"
                            @input="setCurrencyField('shiftCash', $event.target.value); $event.target.value=currencyInput(shiftCash)"
                            required></label><button
                        class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] ![background:#17694d] !text-white ![border-color:#17694d] hover:![background:#12523d] w-full mt-5"
                        :disabled="busy">Simpan & tutup shift</button></form>
            </section>
        </div>
    </div>
</body>

</html>
