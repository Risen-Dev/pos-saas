@extends('layouts.app')

@section('title', 'Kelola Penarikan Dana')

@section('content')
    <div class="px-4 py-6 mx-auto space-y-6 max-w-desktop md:px-6">

        <x-page-header>
            <h1 class="font-sans text-xl font-bold text-ink-900">Pengajuan Penarikan Dana</h1>
            <p class="text-sm text-ink-700 mt-0.5">Tinjau dan proses permintaan penarikan dana dari seluruh tenant.</p>
        </x-page-header>

        <div class="overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase bg-surface-100 text-ink-700">
                        <tr>
                            <th class="px-5 py-3 font-semibold text-left">Referensi</th>
                            <th class="px-5 py-3 font-semibold text-left">Tenant</th>
                            <th class="px-5 py-3 font-semibold text-left">Bank Tujuan</th>
                            <th class="px-5 py-3 font-semibold text-right">Nominal</th>
                            <th class="px-5 py-3 font-semibold text-left">Diajukan</th>
                            <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-200">
                        @forelse ($requests as $req)
                            <tr x-data="{ rejecting: false }" class="align-top transition hover:bg-surface-100/60">
                                <td class="px-5 py-3.5 font-mono text-xs text-ink-900">{{ $req->reference_number }}</td>
                                <td class="px-5 py-3.5">
                                    <div class="font-medium text-ink-900">{{ $req->tenant->name ?? '-' }}</div>
                                    <div class="text-xs text-ink-400">ID: {{ $req->tenant_id }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-ink-700">
                                    <div>{{ $req->bank_name }}</div>
                                    <div class="font-mono text-xs text-ink-400">{{ $req->account_number }} —
                                        {{ $req->account_name }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-semibold text-ink-900">
                                    Rp {{ number_format($req->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-ink-700 text-xs">
                                    {{ $req->created_at->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex justify-end gap-2" x-show="!rejecting">
                                        <form action="{{ route('admin.withdrawals.approve', $req->id) }}" method="POST"
                                            onsubmit="return confirm('Setujui penarikan {{ $req->reference_number }} sebesar Rp {{ number_format($req->amount, 0, ',', '.') }}?')">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 bg-primary-600 text-white text-xs font-semibold px-3 py-2 rounded-md hover:bg-primary-700 transition">
                                                <x-icon class="fa-solid fa-check" /> Setujui
                                            </button>
                                        </form>
                                        <button @click="rejecting = true"
                                            class="inline-flex items-center gap-1.5 bg-surface-0 border border-border-200 text-ink-700 text-xs font-semibold px-3 py-2 rounded-md hover:bg-red-50 hover:text-semantic-danger hover:border-semantic-danger transition">
                                            <x-icon class="fa-solid fa-xmark" /> Tolak
                                        </button>
                                    </div>

                                    <form x-show="rejecting" action="{{ route('admin.withdrawals.reject', $req->id) }}"
                                        method="POST" class="w-56">
                                        @csrf
                                        <textarea name="admin_note" required placeholder="Alasan penolakan..."
                                            class="w-full p-2 text-xs border rounded-sm border-border-200 focus:border-primary-600 focus:ring focus:ring-primary-100 focus:outline-none"
                                            rows="2"></textarea>
                                        <div class="flex justify-end gap-2 mt-1.5">
                                            <button type="button" @click="rejecting = false"
                                                class="px-2 text-xs text-ink-400 hover:text-ink-700">Batal</button>
                                            <button type="submit"
                                                class="text-xs bg-semantic-danger text-white font-semibold px-3 py-1.5 rounded-md hover:opacity-90">
                                                Kirim Penolakan
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-sm text-center text-ink-400">
                                    Tidak ada pengajuan penarikan yang menunggu diproses.
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
    </div>
@endsection
