<x-app-layout>
    @section('title', 'Invoice Pembayaran - ' . $invoice->invoice_number)

    <div class="w-full max-w-4xl px-4 py-8 mx-auto space-y-6 sm:px-6 lg:px-8">

        <div class="flex items-center justify-between">
            <a href="{{ route('billing.index') }}"
                class="inline-flex items-center gap-2 text-xs font-semibold transition text-ink-600 hover:text-primary-600">
                <x-icon class="fa-solid fa-arrow-left" />
                <span>Kembali ke Billing</span>
            </a>
            <span class="font-mono text-xs text-ink-400">ID Invoice: #{{ $invoice->id }}</span>
        </div>

        <div class="overflow-hidden border shadow-sm bg-surface-0 border-border-200 rounded-2xl">

            <div
                class="flex flex-col gap-4 p-6 border-b sm:flex-row sm:items-center sm:justify-between bg-amber-50/50 border-amber-200">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Nomor Tagihan</span>
                    <h1 class="text-xl font-bold font-mono text-ink-900 mt-0.5">{{ $invoice->invoice_number }}</h1>
                </div>

                <div>
                    @if ($invoice->status === 'pending')
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                            <x-icon class="fa-solid fa-clock text-[10px]" /> Menunggu Pembayaran
                        </span>
                    @elseif ($invoice->status === 'paid')
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                            <x-icon class="fa-solid fa-circle-check text-[10px]" /> Lunas
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                            <x-icon class="fa-solid fa-circle-xmark text-[10px]" /> {{ ucfirst($invoice->status) }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="p-6 space-y-8 md:p-8">
                <div class="grid grid-cols-2 gap-6 text-xs md:grid-cols-4">
                    <div>
                        <span class="block mb-1 font-medium text-ink-400">Diterbitkan Untuk:</span>
                        <p class="text-sm font-bold text-ink-900">{{ auth()->user()->tenant->name ?? 'Toko Saya' }}</p>
                        <p class="text-ink-600">{{ auth()->user()->email }}</p>
                    </div>
                    <div>
                        <span class="block mb-1 font-medium text-ink-400">Tanggal Terbit:</span>
                        <p class="font-semibold text-ink-900">
                            {{ \Carbon\Carbon::parse($invoice->created_at)->translatedFormat('d F Y (H:i)') }}</p>
                    </div>
                    <div>
                        <span class="block mb-1 font-medium text-ink-400">Jatuh Tempo:</span>
                        <p class="font-semibold text-amber-700">
                            {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->translatedFormat('d F Y (H:i)') : '-' }}
                        </p>
                    </div>
                    <div>
                        <span class="block mb-1 font-medium text-ink-400">Metode Pembayaran:</span>
                        <p class="font-semibold uppercase text-ink-900">
                            {{ $invoice->payment_method ?? 'Belum dipilih' }}</p>
                    </div>
                </div>

                <div class="overflow-hidden border border-border-200 rounded-xl">
                    <table class="w-full text-xs text-left">
                        <thead class="font-bold border-b bg-surface-100 border-border-200 text-ink-700">
                            <tr>
                                <th class="p-4">Deskripsi Item</th>
                                <th class="p-4 text-center">Durasi</th>
                                <th class="p-4 text-right">Total Tagihan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-100 text-ink-900">
                            <tr>
                                <td class="p-4">
                                    <p class="text-sm font-bold text-primary-900">
                                        Langganan GrowPOS — {{ $invoice->plan?->name ?? $invoice->subscription?->plan?->name ?? 'Paket SaaS' }}
                                    </p>
                                </td>
                                <td class="p-4 font-semibold text-center">
                                    {{ $invoice->duration_days ?? $invoice->plan?->duration_days ?? 30 }} Hari
                                </td>
                                <td class="p-4 text-sm font-bold text-right">
                                    Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col items-center justify-center pt-4 space-y-4 text-center">
                    @if ($invoice->status === 'pending')
                        @if ($invoice->snap_token)
                            <p class="text-xs font-bold text-ink-900">
                                Pilih metode pembayaran: QRIS, Transfer Bank/VA, GoPay, ShopeePay, atau Kartu
                            </p>
                            <button id="btnPay"
                                class="px-8 py-3 text-sm font-bold text-white transition rounded-lg bg-primary-600 hover:bg-primary-700">
                                Bayar Sekarang
                            </button>
                            <button id="btnCheckStatus" onclick="checkInvoiceStatus()"
                                class="px-4 py-2 text-xs font-semibold underline text-ink-500 hover:text-ink-700">
                                Sudah bayar? Cek status manual
                            </button>
                        @else
                            <div class="p-4 text-xs border bg-rose-50 border-rose-200 text-rose-800 rounded-xl">
                                Gagal memuat metode pembayaran. Silakan refresh halaman.
                            </div>
                        @endif
                    @elseif ($invoice->status === 'paid' && $invoice->subscription_id)
                        <div class="w-full p-4 border bg-emerald-50 text-emerald-800 rounded-xl border-emerald-200">
                            <x-icon class="mb-2 text-3xl fa-solid fa-circle-check text-emerald-600" />
                            <p class="text-sm font-bold">Pembayaran Berhasil / Lunas</p>
                            <a href="{{ route('dashboard') }}"
                                class="inline-block px-4 py-2 mt-3 text-xs font-bold text-white rounded-lg bg-emerald-600">
                                Kembali ke Dashboard
                            </a>
                        </div>
                    @elseif ($invoice->status === 'paid')
                        <div role="status" class="w-full p-4 border rounded-xl bg-amber-50 border-amber-200 text-amber-900">Pembayaran diterima. Aktivasi paket sedang diperiksa; jangan membayar ulang. <button id="btnCheckStatus" onclick="checkInvoiceStatus()" class="block mt-3 font-semibold underline">Periksa aktivasi</button></div>
                    @else
                        <div role="status" class="w-full p-4 border rounded-xl bg-red-50 border-red-200 text-red-800">Tagihan {{ $invoice->status === 'expired' ? 'kedaluwarsa' : 'dibatalkan atau gagal' }}. Jika saldo sudah terpotong, periksa status sebelum membuat tagihan baru. <button id="btnCheckStatus" onclick="checkInvoiceStatus()" class="block mt-3 font-semibold underline">Periksa pembayaran</button></div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    @if ($invoice->status === 'pending' && $invoice->snap_token)
        <script
            src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
            data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    @endif
        <script>
            const snapToken = @json($invoice->snap_token);
            let pollInterval = null;

            function openSnap() {
                snap.pay(snapToken, {
                    onSuccess: function() {
                        checkInvoiceStatus(true);
                    },
                    onPending: function() {
                        // Status resmi diupdate via webhook server Midtrans.
                        // Polling ini cuma fallback biar UI ikut update tanpa refresh manual.
                        startPollingFallback();
                    },
                    onError: function() {
                        alert('Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.');
                    },
                    onClose: function() {
                        startPollingFallback();
                    }
                });
            }

            document.getElementById('btnPay')?.addEventListener('click', openSnap);

            function startPollingFallback() {
                if (pollInterval) return;
                pollInterval = setInterval(() => checkInvoiceStatus(true), 5000);
            }

            async function checkInvoiceStatus(isAuto = false) {
                const btn = document.getElementById('btnCheckStatus');
                if (!isAuto && btn) {
                    btn.disabled = true;
                    btn.innerText = 'Checking...';
                }

                try {
                    const response = await fetch("{{ route('billing.check-status', $invoice->id) }}", {headers: {Accept: 'application/json'}});
                    const data = await response.json();

                    if (!response.ok) {
                        if (!isAuto) alert(data.message || 'Status belum dapat diperiksa. Jangan membayar ulang.');
                        return;
                    }
                    if (data.status === 'paid') {
                        if (pollInterval) clearInterval(pollInterval);
                        window.location.href = "{{ route('billing.index') }}?status=success";
                    } else if (!isAuto) {
                        alert('Pembayaran belum terdeteksi. Silakan selesaikan pembayaran terlebih dahulu.');
                    }
                } catch (e) {
                    if (!isAuto) alert('Koneksi terputus. Coba periksa lagi sebelum membayar ulang.');
                } finally {
                    if (!isAuto && btn) {
                        btn.disabled = false;
                        btn.innerText = 'Sudah bayar? Cek status manual';
                    }
                }
            }
        </script>
</x-app-layout>
