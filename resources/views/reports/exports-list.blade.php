<x-app-layout>
    @section('title', 'Laci Unduhan Laporan')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop">

        <!-- Header Halaman -->
        <x-page-header split>
            <div>
                <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                    Laci Unduhan Laporan
                </h1>
                <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                    Daftar berkas laporan Excel yang diproses otomatis oleh sistem di latar belakang.
                </p>
            </div>
            <a href="{{ route('reports.index') }}"
                class="inline-flex items-center h-10 gap-2 px-4 text-xs font-semibold transition-colors border rounded-md shadow-sm bg-surface-0 border-border-200 hover:bg-surface-100 text-ink-900 font-body">
                <x-icon class="text-xs fa-solid fa-arrow-left" />
                <span>Kembali ke Laporan</span>
            </a>
        </x-page-header>

        <!-- Table Container -->
        <div class="mb-6 overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">
            <div class="w-full overflow-x-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr
                            class="h-12 text-xs font-semibold tracking-wider uppercase border-b bg-surface-100 border-border-200 font-heading text-ink-700">
                            <th class="px-5 py-3">Jenis Laporan</th>
                            <th class="px-5 py-3">Periode Data</th>
                            <th class="px-5 py-3">Tanggal Permintaan</th>
                            <th class="px-5 py-3 text-center">Status Pemrosesan</th>
                            <th class="px-5 py-3 text-center">Aksi Berkas</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y font-body md:text-sm text-ink-900 divide-border-200"
                        id="exports-table-body">
                        @forelse($exports as $export)
                            <tr class="h-12 transition-colors hover:bg-surface-100/60" data-id="{{ $export->id }}">

                                <!-- File Name Type -->
                                <td class="px-5 py-3 font-semibold text-ink-900">
                                    <div class="flex items-center gap-2.5">
                                        <x-icon class="text-base fa-solid fa-file-excel text-emerald-600" />
                                        <span>{{ $export->report_type }}</span>
                                    </div>
                                </td>

                                <!-- Period -->
                                <td class="px-5 py-3 font-mono text-xs text-ink-700">
                                    {{ \Carbon\Carbon::parse($export->start_date)->format('d M Y') }} s/d
                                    {{ \Carbon\Carbon::parse($export->end_date)->format('d M Y') }}
                                </td>

                                <!-- Created At -->
                                <td class="px-5 py-3 font-mono text-xs text-ink-400">
                                    {{ $export->created_at->translatedFormat('d M Y, H:i') }} WIB
                                </td>

                                <!-- Status Badge -->
                                <td class="px-5 py-3 text-center status-badge-area">
                                    @if ($export->status === 'pending')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold text-accent-700 bg-accent-100 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-accent-500 animate-pulse"></span>
                                            Antrean
                                        </span>
                                    @elseif($export->status === 'processing')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold text-primary-700 bg-primary-100 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary-600 animate-pulse"></span>
                                            Sedang Diproses
                                        </span>
                                    @elseif($export->status === 'completed')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-primary-700 bg-primary-100 rounded-full">
                                            Selesai
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-semantic-danger bg-red-50 rounded-full">
                                            Gagal
                                        </span>
                                    @endif
                                </td>

                                <!-- Download Button Action -->
                                <td class="px-5 py-3 text-center action-button-area">
                                    @if ($export->status === 'completed')
                                        <a href="{{ route('reports.download-file', $export->id) }}" data-turbo="false"
                                            class="inline-flex items-center gap-1.5 h-8 px-3 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-md transition-colors">
                                            <x-icon class="text-xs fa-solid fa-download" />
                                            <span>Unduh Excel</span>
                                        </a>
                                    @elseif(in_array($export->status, ['pending', 'processing'], true))
                                        <span aria-busy="true" class="inline-flex justify-center" role="status">
                                            <span class="sr-only">Berkas sedang disiapkan.</span>
                                            <x-skeleton variant="block" class="h-8 w-28 rounded-md" />
                                        </span>
                                    @else
                                        <button disabled
                                            class="h-8 px-3 text-xs font-semibold border rounded-md cursor-not-allowed text-ink-400 bg-surface-100 border-border-200">
                                            Gagal disiapkan
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-xs italic text-center font-body text-ink-400">
                                    Belum ada riwayat permintaan ekspor berkas laporan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $exports->links() }}
        </div>
    </div>

    <script>
        (() => {
            const page = document.getElementById('app-content');
            const controller = new AbortController();
            let timeoutId = null;

            async function refreshStatuses() {
                if (!page?.isConnected || !page.querySelector('.animate-pulse')) return;

                try {
                    const response = await fetch("{{ route('reports.exports-status-json') }}", {
                        headers: { Accept: 'application/json' },
                        signal: controller.signal,
                    });
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                    const data = await response.json();
                    if (controller.signal.aborted || !page.isConnected) return;
                    for (const item of data) {
                        const row = page.querySelector(`tr[data-id="${item.id}"]`);
                        const badgeArea = row?.querySelector('.status-badge-area');
                        const buttonArea = row?.querySelector('.action-button-area');
                        if (!badgeArea || !buttonArea) continue;

                        if (item.status === 'completed' && !buttonArea.querySelector('a')) {
                            badgeArea.innerHTML =
                                '<span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-primary-700 bg-primary-100 rounded-full">Selesai</span>';
                            const link = document.createElement('a');
                            link.href = item.download_url;
                            link.dataset.turbo = 'false';
                            link.className = 'inline-flex items-center gap-1.5 h-8 px-3 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-md transition-colors';
                            link.textContent = 'Unduh Excel';
                            buttonArea.replaceChildren(link);
                        } else if (item.status === 'processing') {
                            badgeArea.innerHTML =
                                '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold text-primary-700 bg-primary-100 rounded-full"><span class="w-1.5 h-1.5 rounded-full bg-primary-600 animate-pulse"></span> Sedang Diproses</span>';
                        } else if (item.status === 'failed') {
                            badgeArea.innerHTML =
                                '<span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-semantic-danger bg-red-50 rounded-full">Gagal</span>';
                            const button = document.createElement('button');
                            button.disabled = true;
                            button.className = 'h-8 px-3 text-xs font-semibold rounded-md cursor-not-allowed text-ink-400 bg-surface-100';
                            button.textContent = 'Gagal';
                            buttonArea.replaceChildren(button);
                        }
                    }
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.error('Gagal memperbarui status antrean:', error);
                    }
                }

                if (!controller.signal.aborted && page?.isConnected && page.querySelector('.animate-pulse')) {
                    timeoutId = window.setTimeout(refreshStatuses, 3000);
                }
            }

            if (page?.querySelector('.animate-pulse')) {
                timeoutId = window.setTimeout(refreshStatuses, 3000);
            }

            window.registerGrowposPageCleanup?.(() => {
                if (timeoutId !== null) window.clearTimeout(timeoutId);
                controller.abort();
            });
        })();
    </script>
</x-app-layout>
