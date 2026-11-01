<x-app-layout>
    @section('title', 'Bahan Baku & Inventori')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop">

        <!-- Header Halaman & Action Buttons -->
        <div
            class="flex flex-col justify-between gap-4 pb-6 mb-8 border-b sm:flex-row sm:items-center border-border-200">
            <div>
                <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                    Bahan Baku & Inventori
                </h1>
                <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                    Kelola ketersediaan stok bahan mentah, resep porsi, perlengkapan toko, dan mutasi dari supplier.
                </p>
            </div>

            <!-- Button Primary: Height 44px, Emerald Green -->
            <button onclick="document.getElementById('addMaterialModal').classList.remove('hidden')"
                class="inline-flex items-center justify-center gap-2 px-5 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body md:text-sm shrink-0">
                <x-icon class="text-xs fa-solid fa-plus" />
                <span>Tambah Bahan Baku</span>
            </button>
        </div>

        <!-- Metric Stat Cards Ringkasan Stok -->
        <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-3 md:gap-6">
            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Total Jenis
                        Bahan</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-ink-900">
                        {{ $materials->total() }}
                    </p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-primary-50 text-primary-600 shrink-0">
                    <x-icon class="fa-solid fa-boxes-packing" />
                </div>
            </div>

            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Stok Aman
                        Operasional</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-primary-600">
                        {{ $materials->filter(fn($m) => $m->stock > $m->min_stock)->count() }}
                    </p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-primary-100 text-primary-700 shrink-0">
                    <x-icon class="fa-solid fa-cubes" />
                </div>
            </div>

            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Stok Kritis /
                        Perlu Belanja</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-semantic-danger">
                        {{ $materials->filter(fn($m) => $m->stock <= $m->min_stock)->count() }}
                    </p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-red-50 text-semantic-danger shrink-0">
                    <x-icon class="fa-solid fa-triangle-exclamation" />
                </div>
            </div>
        </div>

        <!-- Table Container (Spesifikasi GrowPOS: Row Height 48px, bg surface-100 header) -->
        <div class="mb-6 overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">
            <div class="w-full overflow-x-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr
                            class="h-12 text-xs font-semibold tracking-wider uppercase border-b bg-surface-100 border-border-200 font-heading text-ink-700">
                            <th class="px-5 py-3">Bahan Baku & SKU</th>
                            <th class="px-5 py-3 text-center">Stok Fisik Saat Ini</th>
                            <th class="px-5 py-3 text-center">Min. Stok Warning</th>
                            <th class="px-5 py-3 text-center">Status Keamanan</th>
                            <th class="px-5 py-3 text-center">Aksi Manajemen</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y font-body md:text-sm text-ink-900 divide-border-200">
                        @forelse($materials as $item)
                            <tr class="h-12 transition-colors hover:bg-surface-100/60">

                                <!-- Material Name & SKU -->
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center w-8 h-8 rounded-md bg-primary-100 text-primary-700 shrink-0">
                                            <x-icon class="text-xs fa-solid fa-vial-wheat" />
                                        </div>
                                        <div class="min-w-0">
                                            <span
                                                class="block font-semibold leading-tight truncate text-ink-900">{{ $item->name }}</span>
                                            <span
                                                class="font-mono text-[11px] font-normal text-ink-400 mt-0.5 block truncate">
                                                SKU: {{ $item->sku }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Current Stock Level -->
                                <td class="px-5 py-3 font-mono text-center">
                                    <span
                                        class="font-bold text-sm {{ $item->stock <= $item->min_stock ? 'text-semantic-danger' : 'text-ink-900' }}">
                                        {{ \App\Support\NumberFormat::quantity($item->stock) }}
                                    </span>
                                    <span class="text-xs font-medium text-ink-400 ml-0.5">{{ $item->unit }}</span>
                                </td>

                                <!-- Minimum Stock Warning -->
                                <td class="px-5 py-3 font-mono text-xs text-center text-ink-700">
                                    {{ \App\Support\NumberFormat::quantity($item->min_stock) }} {{ $item->unit }}
                                </td>

                                <!-- Status Badge (Pill Shape: radius-full) -->
                                <td class="px-5 py-3 text-center">
                                    @if ($item->stock <= $item->min_stock)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-semibold text-semantic-danger bg-red-50 rounded-full">
                                            <span
                                                class="w-1.5 h-1.5 rounded-full bg-semantic-danger animate-pulse"></span>
                                            Stok Kritis
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-primary-700 bg-primary-100 rounded-full">
                                            Aman Operasional
                                        </span>
                                    @endif
                                </td>

                                <!-- Action Buttons -->
                                <td class="px-5 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button
                                            onclick="openHistoryModal('{{ $item->id }}', '{{ addslashes($item->name) }}')"
                                            class="inline-flex items-center gap-1.5 h-8 px-3 text-xs font-semibold text-ink-700 bg-surface-100 hover:bg-border-200 rounded-md transition-colors"
                                            title="Lihat Mutasi">
                                            <x-icon class="text-xs fa-solid fa-clock-rotate-left" />
                                            <span>Riwayat</span>
                                        </button>

                                        <button
                                            onclick="openStockModal('{{ $item->id }}', '{{ addslashes($item->name) }}', '{{ $item->unit }}')"
                                            class="inline-flex items-center gap-1.5 h-8 px-3 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-600 hover:text-white rounded-md transition-colors"
                                            title="Update Stok">
                                            <x-icon class="text-xs fa-solid fa-pen-to-square" />
                                            <span>Update Stok</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <!-- Empty State -->
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="flex items-center justify-center w-12 h-12 mb-2 rounded-full bg-primary-50 text-primary-600">
                                            <x-icon class="text-xl fa-solid fa-boxes-packing" />
                                        </div>
                                        <p class="text-sm font-semibold font-heading text-ink-900">Belum ada bahan baku
                                            terdaftar</p>
                                        <p class="font-body text-xs text-ink-700 mt-0.5 max-w-xs">
                                            Daftarkan inventori bahan baku untuk mengontrol penggunaan stok dan porsi
                                            resep outlet.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Bar -->
        <div>
            {{ $materials->links() }}
        </div>
    </div>

    <!-- Modal 1: Tambah Bahan Baku Baru (Max-Width 480px / max-w-modal-sm) -->
    <div data-dialog id="addMaterialModal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden p-4 bg-ink-900/40 backdrop-blur-[2px]">
        <div class="w-full p-6 border rounded-lg shadow-lg bg-surface-0 max-w-modal-sm border-border-200">

            <div class="flex items-center justify-between pb-3 mb-4 border-b border-border-200">
                <h3 class="text-lg font-semibold font-heading text-ink-900">Tambah Bahan Baku</h3>
                <button type="button" onclick="document.getElementById('addMaterialModal').classList.add('hidden')"
                    class="p-1 text-ink-400 hover:text-ink-900">
                    <x-icon class="text-base fa-solid fa-xmark" />
                </button>
            </div>

            <form action="{{ route('materials.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="field-name" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                        Nama Bahan Baku <span class="text-semantic-danger">*</span>
                    </label>
                    <input id="field-name" type="text" name="name" placeholder="Contoh: Biji Kopi Arabica, Susu UHT" required
                        class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 placeholder-ink-400 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="field-unit" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Satuan Ukur</label>
                        <select id="field-unit" name="unit"
                            class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                            <option value="kg">Kilogram (kg)</option>
                            <option value="gr">Gram (gr)</option>
                            <option value="liter">Liter (l)</option>
                            <option value="ml">Mililiter (ml)</option>
                            <option value="pcs">Pcs / Biji</option>
                        </select>
                    </div>
                    <div>
                        <label for="field-stock" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Stok Awal</label>
                        <input id="field-stock" type="number" name="stock" value="0" min="0" required
                            class="w-full px-3 font-mono text-xs font-semibold transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                    </div>
                </div>

                <div>
                    <label for="field-min_stock" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Min. Stok Warning</label>
                    <input id="field-min_stock" type="number" name="min_stock" value="5" min="1" required
                        class="w-full px-3 font-mono text-xs font-semibold transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                    <p class="font-body text-[11px] text-ink-400 mt-1">Sistem akan memberi peringatan jika stok di
                        bawah batas ini.</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3 pt-3">
                    <button type="button"
                        onclick="document.getElementById('addMaterialModal').classList.add('hidden')"
                        class="flex-1 text-xs font-semibold transition-colors rounded-md h-11 bg-surface-100 hover:bg-border-200 text-ink-900 font-body">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body">
                        Simpan Bahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Update Stok / Mutasi Barang (Max-Width 480px / max-w-modal-sm) -->
    <div data-dialog id="updateStockModal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden p-4 bg-ink-900/40 backdrop-blur-[2px]">
        <div class="w-full p-6 border rounded-lg shadow-lg bg-surface-0 max-w-modal-sm border-border-200">

            <div class="flex items-center justify-between pb-3 mb-4 border-b border-border-200">
                <h3 id="stockModalTitle" class="text-lg font-semibold font-heading text-ink-900">Update Stok</h3>
                <button type="button" onclick="closeStockModal()" class="p-1 text-ink-400 hover:text-ink-900">
                    <x-icon class="text-base fa-solid fa-xmark" />
                </button>
            </div>

            <form action="{{ route('materials.update-stock') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="material_id" id="stockMaterialId">

                <div>
                    <label for="typeSelect" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Tipe Mutasi Stok</label>
                    <select name="type" id="typeSelect" onchange="togglePemasokFields()"
                        class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                        <option value="stock_in">Barang Masuk / Restock (+)</option>
                        <option value="stock_out">Barang Keluar / Pemakaian (-)</option>
                        <option value="adjustment">Hitung fisik (jumlah stok akhir)</option>
                    </select>
                </div>

                <!-- Pemasok Info Box (Shown when stock_in) -->
                <div id="supplierFields" class="p-3 space-y-3 border rounded-md bg-primary-50 border-primary-100">
                    <div>
                        <label for="field-supplier_id" class="block font-body text-[11px] font-semibold text-primary-700 mb-1">Pilih
                            Pemasok</label>
                        <select id="field-supplier_id" name="supplier_id"
                            class="w-full h-10 px-3 text-xs border rounded-sm outline-none font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                            <option value="">-- Tanpa Pemasok --</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="field-purchase_price" class="block font-body text-[11px] font-semibold text-primary-700 mb-1">Harga Beli
                            Satuan (Rp)</label>
                        <input id="field-purchase_price" type="text" data-rupiah-input name="purchase_price" placeholder="0"
                            class="w-full h-10 px-3 font-mono text-xs font-semibold border rounded-sm outline-none text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold font-body text-ink-900">Jumlah Qty Mutasi</label>
                        <span id="displayUnit" class="font-mono text-xs font-semibold text-primary-600"></span>
                    </div>
                    <input type="number" name="quantity" required min="0" placeholder="0"
                        class="w-full px-3 font-mono text-sm font-bold transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                </div>

                <div>
                    <label for="field-note" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Catatan Mutasi</label>
                    <textarea id="field-note" name="note" required maxlength="255" rows="2" placeholder="Contoh: Restock mingguan pasar atau kemasan bocor"
                        class="w-full p-3 text-xs transition-all border rounded-sm outline-none font-body text-ink-900 placeholder-ink-400 bg-surface-0 border-border-200 focus:border-primary-600"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3 pt-3">
                    <button type="button" onclick="closeStockModal()"
                        class="flex-1 text-xs font-semibold transition-colors rounded-md h-11 bg-surface-100 hover:bg-border-200 text-ink-900 font-body">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body">
                        Simpan Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Riwayat Mutasi Stok (Max-Width 640px / max-w-modal-md) -->
    <div data-dialog id="historyModal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden p-4 bg-ink-900/40 backdrop-blur-[2px]">
        <div
            class="bg-surface-0 rounded-lg p-6 w-full max-w-modal-md shadow-lg border border-border-200 max-h-[85vh] flex flex-col">

            <div class="flex items-center justify-between pb-3 mb-4 border-b border-border-200">
                <h3 id="historyModalTitle" class="text-lg font-semibold font-heading text-ink-900">Riwayat Stok</h3>
                <button onclick="closeHistoryModal()" class="p-1 text-ink-400 hover:text-ink-900">
                    <x-icon class="text-base fa-solid fa-xmark" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="sticky top-0 border-b bg-surface-100 border-border-200">
                        <tr class="text-[11px] font-heading font-semibold text-ink-700 uppercase tracking-wider">
                            <th class="px-4 py-2.5">Waktu</th>
                            <th class="px-4 py-2.5">Tipe Mutasi</th>
                            <th class="px-4 py-2.5 text-center">Qty</th>
                            <th class="px-4 py-2.5 text-center">Saldo Akhir</th>
                            <th class="px-4 py-2.5">Catatan</th>
                        </tr>
                    </thead>
                    <tbody id="historyContent" class="text-xs divide-y font-body text-ink-900 divide-border-200">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Script Handling Mutasi & Fetch History -->
    <script>
        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        }

        function togglePemasokFields() {
            const type = document.getElementById('typeSelect').value;
            const fields = document.getElementById('supplierFields');
            fields.classList.toggle('hidden', type !== 'stock_in');
        }

        function openStockModal(id, name, unit) {
            document.getElementById('stockMaterialId').value = id;
            document.getElementById('stockModalTitle').innerText = 'Update Stok: ' + name;
            document.getElementById('displayUnit').innerText = 'Satuan: ' + unit;
            document.getElementById('updateStockModal').classList.remove('hidden');
            togglePemasokFields();
        }

        function closeStockModal() {
            document.getElementById('updateStockModal').classList.add('hidden');
        }

        function openHistoryModal(id, name) {
            const content = document.getElementById('historyContent');
            document.getElementById('historyModalTitle').innerText = 'Riwayat Mutasi: ' + name;
            document.getElementById('historyModal').classList.remove('hidden');

            content.innerHTML =
                '<tr><td colspan="5" class="py-8 text-xs text-center text-ink-400 font-body">Memuat riwayat...</td></tr>';

            fetch(`/materials/${id}/history`)
                .then(response => { if (!response.ok) throw new Error('Riwayat belum dapat dimuat.'); return response.json(); })
                .then(data => {
                    if (data.length === 0) {
                        content.innerHTML =
                            '<tr><td colspan="5" class="py-8 text-xs italic text-center text-ink-400 font-body">Belum ada riwayat mutasi bahan baku.</td></tr>';
                        return;
                    }

                    const rows = data.map(item => {
                        const row = document.createElement('tr');
                        row.className = 'h-10 hover:bg-surface-100/60';
                        const values = [
                            new Date(item.created_at).toLocaleString('id-ID'),
                            String(item.type || '').replaceAll('_', ' ') + (item.supplier ? ' · Pemasok: ' + item.supplier.name : ''),
                            String(item.quantity), String(item.after_stock), item.note || '—'
                        ];
                        for (const value of values) {
                            const cell = document.createElement('td');
                            cell.className = 'px-4 py-2 text-xs text-ink-700';
                            cell.textContent = value;
                            cell.title = value;
                            row.append(cell);
                        }
                        return row;
                    });
                    content.replaceChildren(...rows);
                }).catch(() => {
                    content.replaceChildren();
                    const row = document.createElement('tr');
                    const cell = document.createElement('td');
                    cell.colSpan = 5;
                    cell.textContent = 'Riwayat belum dapat dimuat. Tutup dialog dan coba lagi.';
                    row.append(cell);
                    content.append(row);
                });
        }

        function closeHistoryModal() {
            document.getElementById('historyModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
