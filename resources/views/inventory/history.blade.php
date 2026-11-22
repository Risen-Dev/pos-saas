<x-app-layout>
    <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <x-page-header>
            <h1 class="text-3xl font-black tracking-tight text-gray-900">Riwayat Mutasi Stok</h1>
            <p class="font-medium text-gray-500">Log aktivitas keluar masuk barang & bahan baku.</p>
        </x-page-header>

        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="border-b border-gray-100 bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-black tracking-widest text-gray-400 uppercase">Waktu</th>
                        <th class="px-6 py-4 text-xs font-black tracking-widest text-gray-400 uppercase">Item / Barang
                        </th>
                        <th class="px-6 py-4 text-xs font-black tracking-widest text-center text-gray-400 uppercase">Tipe
                        </th>
                        <th class="px-6 py-4 text-xs font-black tracking-widest text-center text-gray-400 uppercase">
                            Jumlah</th>
                        <th class="px-6 py-4 text-xs font-black tracking-widest text-center text-gray-400 uppercase">
                            Saldo Akhir</th>
                        <th class="px-6 py-4 text-xs font-black tracking-widest text-gray-400 uppercase">Petugas /
                            Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($movements as $log)
                        <tr class="transition hover:bg-gray-50/50">
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $log->created_at->format('d M Y') }}<br>
                                <span
                                    class="text-[10px] text-gray-400 font-bold uppercase">{{ $log->created_at->format('H:i') }}
                                    WIB</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-900">{{ $log->item_name }}</p>
                                <p class="text-[10px] font-black text-blue-500 uppercase">
                                    {{ $log->product_id ? 'Produk' : 'Bahan Baku' }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $colors = [
                                        'stock_in' => 'bg-green-100 text-green-600',
                                        'stock_out' => 'bg-red-100 text-red-600',
                                        'adjustment' => 'bg-yellow-100 text-yellow-600',
                                        'sales' => 'bg-blue-100 text-blue-600',
                                    ];
                                @endphp
                                <span
                                    class="px-3 py-1 rounded-lg text-[10px] font-black uppercase {{ $colors[$log->type] ?? 'bg-gray-100' }}">
                                    {{ str_replace('_', ' ', $log->type) }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 text-center font-bold {{ in_array($log->type, ['stock_in', 'return']) ? 'text-green-600' : 'text-red-600' }}">
                                {{ in_array($log->type, ['stock_in', 'return']) ? '+' : '-' }} {{ \App\Support\NumberFormat::quantity($log->quantity) }}
                            </td>
                            <td class="px-6 py-4 font-black text-center text-gray-900">{{ $log->after_stock }}</td>
                            <td class="px-6 py-4">
                                <p class="text-xs font-bold text-gray-800">{{ $log->user->name }}</p>
                                <p class="text-xs italic text-gray-400">"{{ $log->note ?? '-' }}"</p>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $movements->links() }}
        </div>
    </div>
</x-app-layout>
