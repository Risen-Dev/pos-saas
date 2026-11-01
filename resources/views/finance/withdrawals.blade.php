@extends('layouts.app')

@section('title', 'Dompet & Penarikan Dana')

@section('content')
    <div x-data="withdrawalPage()" class="px-4 py-6 mx-auto space-y-6 max-w-desktop md:px-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-sans text-xl font-bold text-ink-900">Dompet & Penarikan Dana</h1>
                <p class="text-sm text-ink-700 mt-0.5">Kelola saldo toko dan ajukan penarikan ke rekening bank Anda.</p>
            </div>
        </div>

        {{-- Kartu Saldo --}}
        <div class="p-6 text-white rounded-lg shadow-md bg-gradient-to-br from-primary-600 to-primary-700">
            <p class="text-sm font-medium text-primary-100">Saldo Siap Ditarik</p>
            <p class="mt-1 font-mono text-3xl font-semibold">
                Rp {{ number_format($wallet->balance ?? 0, 0, ',', '.') }}
            </p>

            <div class="flex flex-wrap items-center gap-3 mt-4 text-xs text-primary-50">
                <div class="flex items-center gap-1.5">
                    <x-icon class="fa-solid fa-building-columns" />
                    <span>{{ $wallet->bank_name ?? 'Belum diatur' }} — {{ $wallet->account_number ?? '-' }}</span>
                </div>
                <span class="w-1 h-1 rounded-full bg-primary-100/50"></span>
                <span>a.n. {{ $wallet->account_name ?? '-' }}</span>
            </div>

            <button @click="openModal()" {{ ($wallet->balance ?? 0) <= 0 ? 'disabled' : '' }}
                class="mt-5 inline-flex items-center gap-2 bg-white text-primary-700 font-semibold text-sm px-4 py-2.5 rounded-md hover:bg-primary-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
                <x-icon class="fa-solid fa-money-bill-transfer" />
                Ajukan Penarikan
            </button>
        </div>

        {{-- Riwayat Penarikan --}}
        <div class="overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">
            <div class="px-5 py-4 border-b border-border-200">
                <h2 class="font-sans text-sm font-semibold text-ink-900">Riwayat Penarikan</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase bg-surface-100 text-ink-700">
                        <tr>
                            <th class="px-5 py-3 font-semibold text-left">Referensi</th>
                            <th class="px-5 py-3 font-semibold text-left">Tanggal</th>
                            <th class="px-5 py-3 font-semibold text-right">Nominal</th>
                            <th class="px-5 py-3 font-semibold text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-200">
                        @forelse ($requests as $req)
                            <tr class="transition hover:bg-surface-100/60">
                                <td class="px-5 py-3 font-mono text-xs text-ink-900">{{ $req->reference_number }}</td>
                                <td class="px-5 py-3 text-ink-700">{{ $req->created_at->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="px-5 py-3 font-mono font-medium text-right text-ink-900">
                                    Rp {{ number_format($req->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3">
                                    @if ($req->status === 'pending')
                                        <span
                                            class="inline-flex items-center gap-1.5 bg-accent-100 text-accent-700 text-xs font-medium px-2.5 py-1 rounded-full">
                                            <x-icon class="fa-solid fa-clock text-[10px]" /> Menunggu
                                        </span>
                                    @elseif ($req->status === 'approved')
                                        <span
                                            class="inline-flex items-center gap-1.5 bg-primary-100 text-primary-700 text-xs font-medium px-2.5 py-1 rounded-full">
                                            <x-icon class="fa-solid fa-circle-check text-[10px]" /> Disetujui
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 bg-red-50 text-semantic-danger text-xs font-medium px-2.5 py-1 rounded-full">
                                            <x-icon class="fa-solid fa-circle-xmark text-[10px]" /> Ditolak
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-sm text-center text-ink-400">
                                    Belum ada riwayat penarikan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($requests->hasPages())
                <div class="px-5 py-4 border-t border-border-200">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Ajukan Penarikan --}}
        <div data-dialog x-show="modalOpen" x-cloak class="[&[x-cloak]]:!hidden fixed inset-0 z-[90] flex items-center justify-center px-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">

            <div class="absolute inset-0 bg-ink-900/40" @click="closeModal()"></div>

            <div class="relative w-full p-6 rounded-lg shadow-lg bg-surface-0 max-w-modal-sm"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-sans text-base font-semibold text-ink-900">Ajukan Penarikan Dana</h3>
                    <button @click="closeModal()" class="text-ink-400 hover:text-ink-700">
                        <x-icon class="fa-solid fa-xmark" />
                    </button>
                </div>

                <form :action="'{{ route('finance.withdrawals.store') }}'" method="POST" @submit="submitting = true">
                    @csrf
                    <input type="hidden" name="idempotency_key" :value="idempotencyKey">

                    <label class="block text-xs font-medium text-ink-700 mb-1.5">Nominal Penarikan</label>
                    <div class="relative">
                        <span class="absolute font-mono text-sm -translate-y-1/2 left-3 top-1/2 text-ink-400">Rp</span>
                        <input type="text" data-rupiah-input name="amount" min="10000"
                            max="{{ $wallet->balance ?? 0 }}" required
                            class="w-full pr-3 font-mono text-sm border rounded-sm pl-9 h-11 border-border-200 text-ink-900 focus:border-primary-600 focus:ring focus:ring-primary-100 focus:outline-none">
                    </div>
                    <p class="text-xs text-ink-400 mt-1.5">
                        Saldo tersedia: <span class="font-mono">Rp
                            {{ number_format($wallet->balance ?? 0, 0, ',', '.') }}</span>
                    </p>

                    <div class="bg-surface-100 rounded-md p-3.5 mt-4 text-xs text-ink-700 space-y-1">
                        <div class="flex justify-between">
                            <span>Bank Tujuan</span>
                            <span class="font-medium text-ink-900">{{ $wallet->bank_name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>No. Rekening</span>
                            <span class="font-mono font-medium text-ink-900">{{ $wallet->account_number ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" @click="closeModal()"
                            class="flex-1 text-sm font-medium transition border rounded-md h-11 border-border-200 text-ink-700 hover:bg-surface-100">
                            Batal
                        </button>
                        <button type="submit" :disabled="submitting"
                            class="flex-1 text-sm font-semibold text-white transition rounded-md h-11 bg-primary-600 hover:bg-primary-700 disabled:opacity-60">
                            <span x-show="!submitting">Ajukan Sekarang</span>
                            <span x-show="submitting"><x-icon class="fa-solid fa-spinner fa-spin" /> Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function withdrawalPage() {
            return {
                modalOpen: false,
                submitting: false,
                amount: '',
                idempotencyKey: '',
                openModal() {
                    // Idempotency key baru di-generate setiap modal dibuka,
                    // supaya submit ganda (misal double-click) dari 1 sesi form
                    // yang sama tetap dikenali sebagai request yang sama oleh backend.
                    this.idempotencyKey = crypto.randomUUID();
                    this.modalOpen = true;
                },
                closeModal() {
                    this.modalOpen = false;
                    this.submitting = false;
                }
            }
        }
    </script>
@endsection
