<x-app-layout>
    @section('title', 'Sistem Pengaturan')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-modal-lg">

        <!-- Flash Session Notification -->
        @if (session('success'))
            <div
                class="flex items-center gap-3 p-4 mb-6 text-sm font-medium border-l-4 rounded-md shadow-sm bg-primary-50 border-primary-600 text-ink-900">
                <x-icon class="text-base fa-solid fa-circle-check text-primary-600" />
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('warning'))
            <div
                class="flex items-center gap-3 p-4 mb-6 text-sm font-medium border-l-4 rounded-md shadow-sm bg-amber-50 border-amber-500 text-ink-900">
                <x-icon class="text-base fa-solid fa-triangle-exclamation text-amber-600" />
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        <!-- Header Halaman -->
        <div class="pb-6 mb-8 border-b border-border-200">
            <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                Sistem Pengaturan Toko
            </h1>
            <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                Atur konfigurasi Pajak Restoran (PB1/PPN), kalkulasi poin loyalitas pelanggan, dan kebijakan operasional
                outlet.
            </p>
        </div>

        <form action="{{ route('settings.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Section 1: Konfigurasi Pajak (PPN/PB1) -->
            <div class="p-6 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="flex items-center gap-2.5 pb-4 mb-6 border-b border-border-200">
                    <div
                        class="flex items-center justify-center w-8 h-8 text-xs font-bold rounded-md bg-primary-50 text-primary-600 font-heading">
                        <x-icon class="text-xs fa-solid fa-receipt" />
                    </div>
                    <h3 class="text-base font-semibold font-heading text-ink-900">
                        Konfigurasi Pajak Restoran / PPN
                    </h3>
                </div>

                <!-- Toggle Aktifkan Pajak -->
                <div
                    class="flex items-center justify-between p-4 mb-4 border rounded-md bg-surface-100 border-border-200">
                    <div>
                        <h4 class="text-xs font-semibold font-heading text-ink-900">Aktifkan Pajak Otomatis</h4>
                        <p class="font-body text-[11px] text-ink-400 mt-0.5">
                            Jika diaktifkan, kalkulasi total belanja di Kasir otomatis menambahkan tarif pajak.
                        </p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input aria-label="Aktifkan pajak otomatis" type="checkbox" name="tax_active" value="1"
                            {{ ($settings['tax_active'] ?? '0') == '1' ? 'checked' : '' }} class="sr-only peer">
                        <div
                            class="w-11 h-6 bg-border-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-border-200 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600">
                        </div>
                    </label>
                </div>

                <!-- Persentase Pajak -->
                <div>
                    <label for="field-tax_percentage" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                        Tarif Persentase Pajak (%)
                    </label>
                    <div class="relative max-w-xs">
                        <input id="field-tax_percentage" type="number" aria-label="Tarif persentase pajak" name="tax_percentage" value="{{ $settings['tax_percentage'] ?? '10' }}"
                            min="0" max="100" step="0.1"
                            class="w-full pl-3 pr-8 font-mono text-xs font-semibold transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                        <span class="absolute font-mono text-xs right-3 top-3 text-ink-400">%</span>
                    </div>
                </div>
            </div>

            <!-- Section 2: Poin loyalitas pelanggan -->
            <div class="relative p-6 overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">

                <div class="flex items-center justify-between pb-4 mb-6 border-b border-border-200">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex items-center justify-center w-8 h-8 text-xs font-bold rounded-md bg-accent-100 text-accent-700 font-heading">
                            <x-icon class="text-xs fa-solid fa-star" />
                        </div>
                        <h3 class="text-base font-semibold font-heading text-ink-900">
                            Poin loyalitas pelanggan
                        </h3>
                    </div>

                    @cannot('feature-loyalty')
                        <span
                            class="px-2.5 py-1 text-[10px] font-extrabold text-amber-900 bg-amber-100 border border-amber-300 rounded-full uppercase tracking-wider">
                            <x-icon class="fa-solid fa-lock text-[9px] mr-1" /> Paket Growth & Scale
                        </span>
                    @endcannot
                </div>

                @can('feature-loyalty')
                    <!-- TAMPILAN AKTIF (USER DENGAN PAKET GROWTH / SCALE) -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Point Mode Select -->
                        <div>
                            <label for="point_mode" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                                Skema Perhitungan Poin
                            </label>
                            <select name="point_mode" id="point_mode"
                                class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                                <option value="disabled"
                                    {{ ($settings['point_mode'] ?? '') == 'disabled' ? 'selected' : '' }}>
                                    Nonaktifkan Poin Loyalitas
                                </option>
                                <option value="per_investment"
                                    {{ ($settings['point_mode'] ?? '') == 'per_investment' ? 'selected' : '' }}>
                                    Kelipatan Total Belanja (Rekomendasi)
                                </option>
                                <option value="flat" {{ ($settings['point_mode'] ?? '') == 'flat' ? 'selected' : '' }}>
                                    Poin Tetap Per Transaksi Nota
                                </option>
                                <option value="percentage"
                                    {{ ($settings['point_mode'] ?? '') == 'percentage' ? 'selected' : '' }}>
                                    Persentase dari Total Belanja
                                </option>
                            </select>
                        </div>

                        <!-- Rule Value Input -->
                        <div>
                            <label class="block font-body text-xs font-semibold text-ink-900 mb-1.5" id="value_label">
                                Nilai Aturan Poin
                            </label>
                            <input id="field-point_rule_value-money" type="text" inputmode="numeric" data-rupiah-input name="point_rule_value" value="{{ old('point_rule_value', $settings['point_rule_value'] ?? 0) }}" @disabled(old('point_mode', $settings['point_mode'] ?? 'disabled') !== 'per_investment') @class(['w-full px-3 font-mono text-xs font-semibold transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600', 'hidden' => old('point_mode', $settings['point_mode'] ?? 'disabled') !== 'per_investment'])>
                            <input id="field-point_rule_value" type="number" step="0.01" name="point_rule_value" value="{{ old('point_rule_value', $settings['point_rule_value'] ?? 0) }}" @disabled(old('point_mode', $settings['point_mode'] ?? 'disabled') === 'per_investment' || old('point_mode', $settings['point_mode'] ?? 'disabled') === 'disabled') @class(['w-full px-3 font-mono text-xs font-semibold transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600', 'hidden' => old('point_mode', $settings['point_mode'] ?? 'disabled') === 'per_investment' || old('point_mode', $settings['point_mode'] ?? 'disabled') === 'disabled'])>
                            <p class="font-body text-[11px] text-ink-400 mt-1" id="value_help">
                                Contoh: Isi 10000 jika ingin 1 poin setiap belanja Rp 10.000.
                            </p>
                        </div>
                    </div>

                    <input type="hidden" name="point_member_only" value="1">
                    <p class="p-3 mt-4 text-xs rounded-md bg-primary-50 text-primary-800">Poin diberikan kepada transaksi pelanggan yang terdaftar sebagai member dan sudah lunas.</p>
                    <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2">
                        <div>
                            <label for="point_redemption_points" class="block mb-1.5 text-xs font-semibold text-ink-900">Poin untuk ditukar</label>
                            <input id="point_redemption_points" type="number" min="1" step="1" name="point_redemption_points" value="{{ $settings['point_redemption_points'] ?? 100 }}" class="w-full px-3 text-xs border rounded-sm h-11 border-border-200">
                        </div>
                        <div>
                            <label for="point_redemption_value" class="block mb-1.5 text-xs font-semibold text-ink-900">Nilai potongan (Rp)</label>
                            <input id="point_redemption_value" type="text" inputmode="numeric" data-rupiah-input min="1" name="point_redemption_value" value="{{ $settings['point_redemption_value'] ?? 10000 }}" class="w-full px-3 text-xs border rounded-sm h-11 border-border-200">
                        </div>
                    </div>
                    <p class="mt-2 text-[11px] text-ink-700">Contoh: 100 poin ditukar dengan potongan Rp10.000. Penukaran berlaku dalam kelipatan ini.</p>
                @else
                    <!-- TAMPILAN TERKUNCI (USER DENGAN PAKET STARTER) -->
                    <div
                        class="flex flex-col justify-between gap-4 p-4 border rounded-lg bg-amber-50/60 border-amber-200 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex items-center justify-center w-10 h-10 rounded-full bg-amber-100 text-amber-700 shrink-0">
                                <x-icon class="text-base fa-solid fa-crown" />
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-amber-900">Poin loyalitas tersedia di paket berbayar</h4>
                                <p class="text-[11px] text-amber-800 mt-0.5">
                                    Fitur otomatisasi poin loyalitas dan manajemen member toko hanya tersedia pada
                                    <strong>Paket Growth</strong> dan <strong>Scale</strong>.
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('billing.index') }}"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-md transition shadow-sm shrink-0">
                            <x-icon class="fa-solid fa-arrow-up-right-from-square text-[10px]" />
                            <span>Upgrade Paket</span>
                        </a>
                    </div>

                    <!-- Input Dummy Disabled (Visual Overlay) -->
                    <div class="grid grid-cols-1 gap-4 mt-4 pointer-events-none select-none md:grid-cols-2 opacity-40">
                        <div>
                            <label class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Skema Perhitungan
                                Poin</label>
                            <select disabled class="w-full px-3 text-xs border rounded-sm h-11 bg-surface-100 text-ink-400">
                                <option>Nonaktifkan Poin Loyalitas</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Nilai Aturan
                                Poin</label>
                            <input type="number" disabled value="0"
                                class="w-full px-3 text-xs border rounded-sm h-11 bg-surface-100 text-ink-400">
                        </div>
                    </div>
                @endcan

            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end p-4 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-6 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body md:text-sm">
                    <x-icon class="text-xs fa-solid fa-check" />
                    <span>Simpan Semua Pengaturan</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Script Dynamic Help Label -->
    @can('feature-loyalty')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const pointModeSelect = document.getElementById('point_mode');
                const label = document.getElementById('value_label');
                const help = document.getElementById('value_help');
                const moneyInput = document.getElementById('field-point_rule_value-money');
                const numericInput = document.getElementById('field-point_rule_value');

                if (!pointModeSelect) return;

                const digits = (value) => String(value ?? '').replace(/\D/g, '');
                const formatRupiah = (value) => {
                    const raw = digits(value);
                    return raw ? 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(raw)) : '';
                };

                function updatePointHelp() {
                    const mode = pointModeSelect.value;
                    if (mode === 'per_investment') {
                        label.innerText = "Kelipatan Belanja (Rp)";
                        help.innerText = "Contoh: Isi 10000 artinya kelipatan Rp 10.000 akan mendapatkan 1 poin.";
                        if (moneyInput.disabled) moneyInput.value = formatRupiah(numericInput.value);
                        moneyInput.disabled = false;
                        numericInput.disabled = true;
                        moneyInput.classList.remove('hidden');
                        numericInput.classList.add('hidden');
                    } else if (mode === 'flat') {
                        label.innerText = "Jumlah Poin Tetap";
                        help.innerText =
                            "Contoh: Isi 5 artinya berapapun total belanjanya, pelanggan otomatis dapat 5 poin.";
                        if (numericInput.disabled) numericInput.value = digits(moneyInput.value);
                        numericInput.disabled = false;
                        moneyInput.disabled = true;
                        numericInput.classList.remove('hidden');
                        moneyInput.classList.add('hidden');
                    } else if (mode === 'percentage') {
                        label.innerText = "Persentase Poin (%)";
                        help.innerText =
                            "Contoh: Isi 1 artinya pelanggan dapat poin sebesar 1% dari total nilai transaksi.";
                        if (numericInput.disabled) numericInput.value = digits(moneyInput.value);
                        numericInput.disabled = false;
                        moneyInput.disabled = true;
                        numericInput.classList.remove('hidden');
                        moneyInput.classList.add('hidden');
                    } else {
                        label.innerText = "Nilai Aturan Poin";
                        help.innerText = "Poin loyalitas pelanggan saat ini dalam posisi nonaktif.";
                        moneyInput.disabled = true;
                        numericInput.disabled = true;
                        moneyInput.classList.add('hidden');
                        numericInput.classList.add('hidden');
                    }
                }

                pointModeSelect.addEventListener('change', updatePointHelp);
                updatePointHelp();
            });
        </script>
    @endcan
</x-app-layout>
