<x-app-layout>
    @section('title', 'Keuangan & Dompet Toko')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop">

        <!-- Header Halaman -->
        <div class="pb-6 mb-8 border-b border-border-200">
            <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                Keuangan & Dompet Toko
            </h1>
            <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                Kelola saldo dompet digital toko, pengaturan rekening bank penampung, dan pengajuan penarikan dana
                (payout).
            </p>
        </div>

        <!-- Grid 3 Kartu Ringkasan Saldo & Rekening -->
        <div class="grid grid-cols-1 gap-4 mb-8 md:grid-cols-3 md:gap-6">

            <!-- Kartu 1: Saldo Utama (Dapat Ditarik) -->
            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="block text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Saldo yang
                        Dapat Ditarik</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-primary-600">
                        Rp {{ number_format($wallet->balance ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-primary-50 text-primary-600 shrink-0">
                    <x-icon class="fa-solid fa-wallet" />
                </div>
            </div>

            <!-- Kartu 2: Rekening Bank Terdaftar -->
            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div class="min-w-0 pr-3">
                    <span
                        class="block mb-1 text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Rekening
                        Bank Tujuan</span>
                    @if (isset($wallet->account_number) && $wallet->account_number != '')
                        <p class="font-mono text-xs font-semibold truncate md:text-sm text-ink-900">
                            {{ $wallet->bank_name }} • {{ $wallet->account_number }}
                        </p>
                        <p class="font-body text-[11px] text-ink-400 mt-0.5 truncate">
                            a.n. {{ $wallet->account_name }}
                        </p>
                    @else
                        <p class="text-xs italic font-semibold font-body text-accent-700">
                            Belum diatur
                        </p>
                    @endif
                </div>
                <button onclick="openBankSettingsModal()"
                    class="inline-flex items-center gap-1.5 h-8 px-3 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-md transition-colors shrink-0">
                    <x-icon class="fa-solid fa-pen-to-square text-[11px]" />
                    <span>Atur</span>
                </button>
            </div>

            <!-- Kartu 3: Tombol Aksi Tarik Saldo -->
            <div
                class="flex items-center justify-center p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <button onclick="openWithdrawModal()"
                    class="w-full h-11 inline-flex items-center justify-center gap-2 px-5 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 text-white font-body font-semibold text-xs md:text-sm rounded-md shadow-sm transition-colors {{ ($wallet->balance ?? 0) <= 0 || !isset($wallet->account_number) ? 'opacity-50 cursor-not-allowed' : '' }}"
                    {{ ($wallet->balance ?? 0) <= 0 || !isset($wallet->account_number) ? 'disabled' : '' }}>
                    <x-icon class="text-xs fa-solid fa-money-bill-transfer" />
                    <span>Tarik Saldo ke Bank</span>
                </button>
            </div>
        </div>

        <!-- Tabel Riwayat Pengajuan Penarikan Dana -->
        <div class="mb-8 overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">
            <div class="p-4 border-b bg-surface-100 border-border-200">
                <h3 class="text-sm font-semibold font-heading md:text-base text-ink-900">
                    Riwayat Pengajuan Penarikan Dana
                </h3>
            </div>

            <div class="w-full overflow-x-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr
                            class="h-10 text-xs font-semibold tracking-wider uppercase border-b bg-surface-100 border-border-200 font-heading text-ink-700">
                            <th class="px-5 py-2.5">No. Referensi</th>
                            <th class="px-5 py-2.5">Tanggal Permintaan</th>
                            <th class="px-5 py-2.5">Tujuan Bank & Rekening</th>
                            <th class="px-5 py-2.5 text-right">Nominal Penarikan</th>
                            <th class="px-5 py-2.5 text-center">Status Pemrosesan</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y font-body md:text-sm text-ink-900 divide-border-200">
                        @forelse($withdrawals as $wdr)
                            <tr class="h-12 transition-colors hover:bg-surface-100/60">
                                <!-- Ref No -->
                                <td class="px-5 py-3 font-mono text-xs font-semibold text-ink-900">
                                    {{ $wdr->reference_number }}
                                </td>

                                <!-- Date -->
                                <td class="px-5 py-3 font-mono text-xs text-ink-400">
                                    {{ \Carbon\Carbon::parse($wdr->created_at)->format('d M Y, H:i') }} WIB
                                </td>

                                <!-- Bank Info -->
                                <td class="px-5 py-3">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-semibold text-ink-900">{{ $wdr->bank_name }}</span>
                                        <span class="font-mono text-[11px] text-ink-400">
                                            {{ $wdr->account_number }} (a.n {{ $wdr->account_name }})
                                        </span>
                                    </div>
                                </td>

                                <!-- Amount -->
                                <td class="px-5 py-3 font-mono font-semibold text-right text-primary-600">
                                    Rp {{ number_format($wdr->amount, 0, ',', '.') }}
                                </td>

                                <!-- Status Badge (Pill Shape: radius-full) -->
                                <td class="px-5 py-3 text-center">
                                    @if ($wdr->status === 'pending')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-semibold text-accent-700 bg-accent-100 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-accent-500 animate-pulse"></span>
                                            Menunggu Transfer
                                        </span>
                                    @elseif($wdr->status === 'approved')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-primary-700 bg-primary-100 rounded-full">
                                            Selesai / Ditransfer
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-semantic-danger bg-red-50 rounded-full">
                                            Ditolak
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <!-- Empty State -->
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="flex items-center justify-center w-12 h-12 mb-2 rounded-full bg-primary-50 text-primary-600">
                                            <x-icon class="text-xl fa-solid fa-receipt" />
                                        </div>
                                        <p class="text-sm font-semibold font-heading text-ink-900">Belum ada riwayat
                                            penarikan dana</p>
                                        <p class="font-body text-xs text-ink-700 mt-0.5 max-w-xs">
                                            Semua aktivitas penarikan saldo toko ke rekening bank penampung akan
                                            tercatat di sini.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal 1: Form Penarikan Dana (Max-Width 480px / max-w-modal-sm) -->
    <div data-dialog id="withdrawModal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden p-4 bg-ink-900/40 backdrop-blur-[2px]">
        <div class="w-full p-6 border rounded-lg shadow-lg bg-surface-0 max-w-modal-sm border-border-200">

            <div class="flex items-center justify-between pb-3 mb-4 border-b border-border-200">
                <h3 class="text-lg font-semibold font-heading text-ink-900">Form Penarikan Dana</h3>
                <button type="button" onclick="closeWithdrawModal()" class="p-1 text-ink-400 hover:text-ink-900">
                    <x-icon class="text-base fa-solid fa-xmark" />
                </button>
            </div>

            @if (!isset($wallet->account_number) || $wallet->account_number == '')
                <div class="p-3 mb-4 bg-red-50 border border-red-100 rounded-md flex items-start gap-2.5">
                    <x-icon class="fa-solid fa-triangle-exclamation text-semantic-danger text-sm mt-0.5 shrink-0" />
                    <p class="text-xs leading-relaxed font-body text-semantic-danger">
                        Anda belum mengatur nomor rekening bank penampung. Silakan lengkapi informasi rekening Anda
                        terlebih dahulu.
                    </p>
                </div>
                <button onclick="closeWithdrawModal(); openBankSettingsModal();"
                    class="w-full text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 font-body">
                    Atur Rekening Sekarang
                </button>
            @else
                <form id="withdrawForm" class="space-y-4">
                    @csrf

                    <div class="p-3 space-y-1 text-xs border rounded-md bg-surface-100 border-border-200 font-body">
                        <div class="flex justify-between">
                            <span class="text-ink-700">Bank Tujuan:</span>
                            <span class="font-semibold text-ink-900">{{ $wallet->bank_name }}</span>
                        </div>
                        <div class="flex justify-between font-mono">
                            <span class="text-ink-700 font-body">No. Rekening:</span>
                            <span class="font-semibold text-ink-900">{{ $wallet->account_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink-700">Nama Pemilik:</span>
                            <span class="font-semibold text-ink-900">{{ $wallet->account_name }}</span>
                        </div>
                        <div class="flex justify-between pt-1 font-mono border-t border-border-200">
                            <span class="text-ink-700 font-body">Maksimal Penarikan:</span>
                            <span class="font-bold text-primary-600">Rp
                                {{ number_format($wallet->balance, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div>
                        <label for="inputAmount" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                            Jumlah Penarikan <span class="text-semantic-danger">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute font-mono text-base font-semibold -translate-y-1/2 left-3 top-1/2 text-ink-400">Rp</span>
                            <input type="text" id="inputAmount" oninput="formatCurrency(this)" required
                                placeholder="50.000"
                                class="w-full pl-10 pr-3 font-mono text-base font-bold transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-3">
                        <button type="button" onclick="closeWithdrawModal()"
                            class="flex-1 text-xs font-semibold transition-colors rounded-md h-11 bg-surface-100 hover:bg-border-200 text-ink-900 font-body">
                            Batal
                        </button>
                        <button type="submit" id="btnSubmitWithdraw"
                            class="flex-1 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body">
                            Kirim Pengajuan
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Modal 2: Pengaturan Rekening Bank (Max-Width 480px / max-w-modal-sm) -->
    <div data-dialog id="bankSettingsModal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden p-4 bg-ink-900/40 backdrop-blur-[2px]">
        <div class="w-full p-6 border rounded-lg shadow-lg bg-surface-0 max-w-modal-sm border-border-200">

            <div class="flex items-center justify-between pb-3 mb-4 border-b border-border-200">
                <h3 class="text-lg font-semibold font-heading text-ink-900">Pengaturan Rekening Bank</h3>
                <button type="button" onclick="closeBankSettingsModal()"
                    class="p-1 text-ink-400 hover:text-ink-900">
                    <x-icon class="text-base fa-solid fa-xmark" />
                </button>
            </div>

            <form id="bankSettingsForm" class="space-y-4">
                @csrf

                <div>
                    <label for="bank_name" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                        Nama Bank <span class="text-semantic-danger">*</span>
                    </label>
                    <select id="bank_name" required
                        class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                        <option value="" disabled selected>-- Pilih Bank --</option>
                        <option value="BCA" {{ ($wallet->bank_name ?? '') == 'BCA' ? 'selected' : '' }}>Bank
                            Central Asia (BCA)</option>
                        <option value="Mandiri" {{ ($wallet->bank_name ?? '') == 'Mandiri' ? 'selected' : '' }}>Bank
                            Mandiri</option>
                        <option value="BRI" {{ ($wallet->bank_name ?? '') == 'BRI' ? 'selected' : '' }}>Bank Rakyat
                            Indonesia (BRI)</option>
                        <option value="BNI" {{ ($wallet->bank_name ?? '') == 'BNI' ? 'selected' : '' }}>Bank Negara
                            Indonesia (BNI)</option>
                        <option value="BSI" {{ ($wallet->bank_name ?? '') == 'BSI' ? 'selected' : '' }}>Bank
                            Syariah Indonesia (BSI)</option>
                        <option value="CIMB Niaga" {{ ($wallet->bank_name ?? '') == 'CIMB Niaga' ? 'selected' : '' }}>
                            CIMB Niaga</option>
                    </select>
                </div>

                <div>
                    <label for="account_number" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                        Nomor Rekening <span class="text-semantic-danger">*</span>
                    </label>
                    <input type="text" id="account_number" required value="{{ $wallet->account_number ?? '' }}"
                        placeholder="Masukkan nomor rekening tanpa spasi/strip"
                        class="w-full px-3 font-mono text-xs transition-all border rounded-sm outline-none h-11 text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                </div>

                <div>
                    <label for="account_name" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                        Nama Pemilik Rekening <span class="text-semantic-danger">*</span>
                    </label>
                    <input type="text" id="account_name" required value="{{ $wallet->account_name ?? '' }}"
                        placeholder="Nama lengkap sesuai buku tabungan"
                        class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                </div>

                <div class="flex items-center gap-3 pt-3">
                    <button type="button" onclick="closeBankSettingsModal()"
                        class="flex-1 text-xs font-semibold transition-colors rounded-md h-11 bg-surface-100 hover:bg-border-200 text-ink-900 font-body">
                        Batal
                    </button>
                    <button type="submit" id="btnSaveBank"
                        class="flex-1 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body">
                        Simpan Rekening
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script Handling Modals & AJAX Actions -->
    <script>
        const withdrawalStorageKey = 'growpos:withdrawal:{{ auth()->user()->tenant_id }}:{{ auth()->id() }}';
        let withdrawalKey = sessionStorage.getItem(withdrawalStorageKey) || crypto.randomUUID();
        sessionStorage.setItem(withdrawalStorageKey, withdrawalKey);
        function openWithdrawModal() {
            document.getElementById('withdrawModal').classList.remove('hidden');
        }

        function closeWithdrawModal() {
            document.getElementById('withdrawModal').classList.add('hidden');
            if (document.getElementById('withdrawForm')) document.getElementById('withdrawForm').reset();
        }

        function openBankSettingsModal() {
            document.getElementById('bankSettingsModal').classList.remove('hidden');
        }

        function closeBankSettingsModal() {
            document.getElementById('bankSettingsModal').classList.add('hidden');
        }

        function formatCurrency(input) {
            let value = input.value.replace(/\D/g, "");
            input.value = value !== "" ? new Intl.NumberFormat('id-ID').format(value) : "";
        }

        // AJAX Submit 1: Pengaturan Rekening Bank
        document.getElementById('bankSettingsForm').onsubmit = async function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSaveBank');
            btn.disabled = true;
            btn.innerText = "MENYIMPAN...";

            const data = {
                bank_name: document.getElementById('bank_name').value,
                account_number: document.getElementById('account_number').value,
                account_name: document.getElementById('account_name').value
            };

            try {
                const response = await fetch('/finance/settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });

                const res = await response.json();
                if (res.success) {
                    alert('Informasi rekening bank berhasil disimpan!');
                    location.reload();
                } else {
                    alert('Gagal: ' + res.message);
                    btn.disabled = false;
                    btn.innerText = "Simpan Rekening";
                }
            } catch (error) {
                alert('Terjadi kesalahan sistem internal.');
                btn.disabled = false;
                btn.innerText = "Simpan Rekening";
            }
        };

        // AJAX Submit 2: Penarikan Dana
        if (document.getElementById('withdrawForm')) {
            document.getElementById('withdrawForm').onsubmit = async function(e) {
                e.preventDefault();
                const btn = document.getElementById('btnSubmitWithdraw');
                const rawAmount = document.getElementById('inputAmount').value.replace(/\D/g, "") || 0;
                const amount = parseInt(rawAmount);

                if (amount <= 0) return alert('Jumlah penarikan harus lebih dari Rp 0');

                btn.disabled = true;
                btn.innerText = "MEMPROSES...";

                try {
                    const response = await fetch('/finance/withdraw', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            idempotency_key: withdrawalKey,
                            amount: amount
                        })
                    });

                    const res = await response.json();
                    if (res.success) {
                        withdrawalKey = crypto.randomUUID();
                        sessionStorage.setItem(withdrawalStorageKey, withdrawalKey);
                        alert('Pengajuan penarikan dana berhasil dikirim!');
                        location.reload();
                    } else {
                        alert('Gagal: ' + res.message);
                        btn.disabled = false;
                        btn.innerText = "Kirim Pengajuan";
                    }
                } catch (error) {
                    alert('Terjadi kesalahan koneksi sistem.');
                    btn.disabled = false;
                    btn.innerText = "Kirim Pengajuan";
                }
            };
        }
    </script>
</x-app-layout>
