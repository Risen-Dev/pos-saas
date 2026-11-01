<x-app-layout>
    @section('title', 'Buat Promo Diskon Baru')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-modal-lg">

        <!-- Breadcrumb & Page Title -->
        <div class="mb-6">
            <a href="{{ route('discounts.index') }}"
                class="inline-flex items-center gap-2 mb-2 text-xs font-semibold font-body text-primary-600 hover:text-primary-700">
                <x-icon class="text-xs fa-solid fa-arrow-left" />
                <span>Kembali ke Manajemen Diskon</span>
            </a>
            <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                Buat Event Diskon Baru
            </h1>
            <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                Atur skema potongan harga, jadwal periode aktif, dan menu produk yang berhak mendapat promo.
            </p>
        </div>

        <!-- Form Card Container -->
        <div class="p-6 border rounded-lg shadow-sm bg-surface-0 border-border-200">
            <form action="{{ route('discounts.store') }}" method="POST" class="space-y-6" x-data="{ discountType: @js(old('type', 'percentage')) }">
                @csrf

                <!-- Row 1: Nama, Tipe & Nilai -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6">
                    <div>
                        <label for="field-name" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                            Nama Diskon / Event Promo <span class="text-semantic-danger">*</span>
                        </label>
                        <input id="field-name" type="text" name="name"
                            placeholder="Contoh: Morning Coffee Promo, Sale Akhir Tahun" required
                            class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 placeholder-ink-400 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="field-type" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Tipe Skema</label>
                            <select id="field-type" name="type" x-model="discountType"
                                class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                                <option value="percentage">Persentase (%)</option>
                                <option value="fixed">Nominal (Rp)</option>
                            </select>
                        </div>
                        <div x-show="discountType === 'percentage'">
                            <label for="field-value-percent" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Nilai Diskon (%) *</label>
                            <input id="field-value-percent" type="number" name="value" step="0.01" placeholder="10" :disabled="discountType !== 'percentage'" value="{{ old('value') }}" required
                                class="w-full px-3 font-mono text-xs font-semibold transition-all border rounded-sm outline-none h-11 text-ink-900 placeholder-ink-400 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                        </div>
                        <div x-show="discountType === 'fixed'" x-cloak>
                            <label for="field-value-fixed" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Nilai Diskon (Rp) *</label>
                            <input id="field-value-fixed" type="text" inputmode="numeric" data-rupiah-input name="value" placeholder="Rp 15.000" :disabled="discountType !== 'fixed'" value="{{ old('value') }}" required
                                class="w-full px-3 font-mono text-xs font-semibold transition-all border rounded-sm outline-none h-11 text-ink-900 placeholder-ink-400 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                        </div>
                    </div>
                </div>

                <!-- Section: Pengaturan Waktu Aktif -->
                <div class="p-4 border rounded-md bg-surface-100 border-border-200">
                    <h3 class="mb-3 text-xs font-semibold tracking-wider uppercase font-heading text-ink-900">
                        Pengaturan Waktu Aktif (Opsional)
                    </h3>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label for="field-start_date" class="block font-body text-[11px] font-medium text-ink-700 mb-1">Tanggal
                                Mulai</label>
                            <input id="field-start_date" type="date" name="start_date"
                                class="w-full h-10 px-3 text-xs border rounded-sm outline-none font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                        </div>
                        <div>
                            <label for="field-end_date" class="block font-body text-[11px] font-medium text-ink-700 mb-1">Tanggal
                                Selesai</label>
                            <input id="field-end_date" type="date" name="end_date"
                                class="w-full h-10 px-3 text-xs border rounded-sm outline-none font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                        </div>
                        <div>
                            <label for="field-start_time" class="block font-body text-[11px] font-medium text-ink-700 mb-1">Jam Mulai (Happy
                                Hour)</label>
                            <input id="field-start_time" type="time" name="start_time"
                                class="w-full h-10 px-3 text-xs border rounded-sm outline-none font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                        </div>
                        <div>
                            <label for="field-end_time" class="block font-body text-[11px] font-medium text-ink-700 mb-1">Jam Selesai</label>
                            <input id="field-end_time" type="time" name="end_time"
                                class="w-full h-10 px-3 text-xs border rounded-sm outline-none font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                        </div>
                    </div>
                </div>

                <!-- Section: Menu Terikat -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold font-body text-ink-900">
                            Pilih Menu / Produk Yang Berhak Menerima Diskon
                        </label>

                        <label
                            class="flex items-center gap-1.5 text-xs font-body font-semibold text-primary-600 cursor-pointer hover:text-primary-700 select-none">
                            <input type="checkbox" id="selectAllProducts" onclick="toggleSelectAll(this)"
                                class="w-4 h-4 rounded text-primary-600 border-border-200 focus:ring-primary-600">
                            <span>Pilih Semua Menu</span>
                        </label>
                    </div>

                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 p-3 bg-surface-100 border border-border-200 rounded-md max-h-56 overflow-y-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                        @foreach ($products as $product)
                            <label
                                class="flex items-center gap-2.5 p-2.5 bg-surface-0 border border-border-200 rounded-sm cursor-pointer hover:border-primary-600 transition-colors">
                                <input type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                                    class="w-4 h-4 rounded text-primary-600 border-border-200 product-checkbox focus:ring-primary-600">
                                <span
                                    class="text-xs truncate font-body text-ink-900">{{ $product->product_name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Toggle Active State Box -->
                <div class="flex items-center justify-between p-4 border rounded-md bg-primary-50 border-primary-100">
                    <div>
                        <h4 class="text-xs font-semibold font-heading text-primary-700">Aktifkan Diskon Sekarang?</h4>
                        <p class="font-body text-[11px] text-ink-700 mt-0.5">Jika diaktifkan, potongan diskon langsung
                            memotong harga otomatis di POS.</p>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                        <div
                            class="w-11 h-6 bg-border-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-border-200 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600">
                        </div>
                    </label>
                </div>

                <!-- Submit Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('discounts.index') }}"
                        class="inline-flex items-center justify-center px-5 text-xs font-semibold transition-colors rounded-md h-11 bg-surface-100 hover:bg-border-200 text-ink-900 font-body">
                        Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center px-6 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body md:text-sm">
                        Simpan Promo Diskon
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleSelectAll(master) {
            const checkboxes = document.querySelectorAll('.product-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = master.checked;
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const masterCheckbox = document.getElementById('selectAllProducts');
            const checkboxes = document.querySelectorAll('.product-checkbox');

            checkboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    const totalCheckboxes = checkboxes.length;
                    const totalChecked = document.querySelectorAll('.product-checkbox:checked')
                        .length;
                    if (masterCheckbox) {
                        masterCheckbox.checked = (totalCheckboxes === totalChecked);
                    }
                });
            });
        });
    </script>
</x-app-layout>
