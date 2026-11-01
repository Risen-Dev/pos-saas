<x-app-layout>
    @section('title', 'Edit Promo Diskon')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-modal-lg">

        <!-- Breadcrumb & Header -->
        <div class="mb-6">
            <a href="{{ route('discounts.index') }}"
                class="inline-flex items-center gap-2 mb-2 text-xs font-semibold font-body text-primary-600 hover:text-primary-700">
                <x-icon class="text-xs fa-solid fa-arrow-left" />
                <span>Kembali ke Manajemen Diskon</span>
            </a>
            <div class="flex items-center justify-between">
                <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                    Edit Event Diskon
                </h1>
                <span
                    class="font-mono text-xs font-semibold px-2.5 py-1 bg-surface-100 border border-border-200 text-ink-700 rounded-md">
                    ID: #DSC-{{ $discount->id }}
                </span>
            </div>
            <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                Perbarui detail potongan promo, jam aktif, atau produk yang terhubung dengan event ini.
            </p>
        </div>

        <!-- Form Card Container -->
        <div class="p-6 border rounded-lg shadow-sm bg-surface-0 border-border-200">
            <form action="{{ route('discounts.update', $discount->id) }}" method="POST" class="space-y-6" x-data="{ discountType: @js(old('type', $discount->type)) }">
                @csrf
                @method('PUT')

                <!-- Row 1: Nama, Tipe & Nilai -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6">
                    <div>
                        <label for="field-name" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                            Nama Diskon / Event Promo <span class="text-semantic-danger">*</span>
                        </label>
                        <input id="field-name" type="text" name="name" value="{{ old('name', $discount->name) }}" required
                            class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="field-type" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Tipe Skema</label>
                            <select id="field-type" name="type" x-model="discountType"
                                class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                                <option value="percentage" {{ $discount->type == 'percentage' ? 'selected' : '' }}>
                                    Persentase (%)</option>
                                <option value="fixed" {{ $discount->type == 'fixed' ? 'selected' : '' }}>Nominal (Rp)
                                </option>
                            </select>
                        </div>
                        <div x-show="discountType === 'percentage'">
                            <label for="field-value-percent" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Nilai Diskon (%) *</label>
                            <input id="field-value-percent" type="number" name="value" value="{{ old('value', $discount->value) }}" :disabled="discountType !== 'percentage'" required
                                class="w-full px-3 font-mono text-xs font-semibold transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                        </div>
                        <div x-show="discountType === 'fixed'" x-cloak>
                            <label for="field-value-fixed" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Nilai Diskon (Rp) *</label>
                            <input id="field-value-fixed" type="text" inputmode="numeric" data-rupiah-input name="value" value="{{ old('value', $discount->value) }}" :disabled="discountType !== 'fixed'" required
                                class="w-full px-3 font-mono text-xs font-semibold transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                        </div>
                    </div>
                </div>

                <!-- Section: Waktu Aktif -->
                <div class="p-4 border rounded-md bg-surface-100 border-border-200">
                    <h3 class="mb-3 text-xs font-semibold tracking-wider uppercase font-heading text-ink-900">
                        Pengaturan Waktu Aktif
                    </h3>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label for="field-start_date" class="block font-body text-[11px] font-medium text-ink-700 mb-1">Tanggal
                                Mulai</label>
                            <input id="field-start_date" type="date" name="start_date"
                                value="{{ old('start_date', $discount->start_date) }}"
                                class="w-full h-10 px-3 text-xs border rounded-sm outline-none font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                        </div>
                        <div>
                            <label for="field-end_date" class="block font-body text-[11px] font-medium text-ink-700 mb-1">Tanggal
                                Selesai</label>
                            <input id="field-end_date" type="date" name="end_date" value="{{ old('end_date', $discount->end_date) }}"
                                class="w-full h-10 px-3 text-xs border rounded-sm outline-none font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                        </div>
                        <div>
                            <label for="field-start_time" class="block font-body text-[11px] font-medium text-ink-700 mb-1">Jam Mulai</label>
                            <input id="field-start_time" type="time" name="start_time"
                                value="{{ old('start_time', $discount->start_time) }}"
                                class="w-full h-10 px-3 text-xs border rounded-sm outline-none font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                        </div>
                        <div>
                            <label for="field-end_time" class="block font-body text-[11px] font-medium text-ink-700 mb-1">Jam Selesai</label>
                            <input id="field-end_time" type="time" name="end_time" value="{{ old('end_time', $discount->end_time) }}"
                                class="w-full h-10 px-3 text-xs border rounded-sm outline-none font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                        </div>
                    </div>
                </div>

                <!-- Section: Pilih Produk Terikat -->
                <div>
                    <label class="block mb-2 text-xs font-semibold font-body text-ink-900">
                        Menu Produk Terhubung Promo Diskon
                    </label>

                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 p-3 bg-surface-100 border border-border-200 rounded-md max-h-56 overflow-y-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                        @foreach ($products as $product)
                            <label
                                class="flex items-center gap-2.5 p-2.5 bg-surface-0 border border-border-200 rounded-sm cursor-pointer hover:border-primary-600 transition-colors">
                                <input type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                                    {{ in_array($product->id, $selectedProductIds) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded text-primary-600 border-border-200 focus:ring-primary-600">
                                <span
                                    class="text-xs truncate font-body text-ink-900">{{ $product->product_name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Toggle Active Status -->
                <div class="flex items-center justify-between p-4 border rounded-md bg-primary-50 border-primary-100">
                    <div>
                        <h4 class="text-xs font-semibold font-heading text-primary-700">Status Keaktifan Diskon</h4>
                        <p class="font-body text-[11px] text-ink-700 mt-0.5">Nonaktifkan jika ingin menonaktifkan promo
                            sementara di kasir.</p>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" name="is_active" value="1"
                            {{ $discount->is_active ? 'checked' : '' }} class="sr-only peer">
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
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
