<x-app-layout>
    @section('title', 'Manajemen Pegawai & Akses')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-desktop" x-data="{ showAddModal: false, showDeleteModal: false, deleteUrl: '', employeeName: '' }">

        @php
            $employeeCount = $employees->count();
            $currentPlan = auth()->user()->tenant?->currentPlan();
            $maxUsers = $currentPlan?->max_users ?? 1;
            $isUserFull = $employeeCount >= $maxUsers;
        @endphp

        <!-- Flash Notification -->
        @if (session('success'))
            <div
                class="flex items-center gap-3 p-4 mb-6 text-sm font-medium border-l-4 rounded-md shadow-sm bg-primary-50 border-primary-600 text-ink-900">
                <x-icon class="text-base fa-solid fa-circle-check text-primary-600" />
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div
                class="flex items-center gap-3 p-4 mb-6 text-sm font-medium border-l-4 rounded-md shadow-sm bg-red-50 border-semantic-danger text-ink-900">
                <x-icon class="text-base fa-solid fa-circle-exclamation text-semantic-danger" />
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Banner Kuota Pegawai -->
        <div
            class="flex flex-col justify-between gap-3 p-4 mb-6 border rounded-lg shadow-sm sm:flex-row sm:items-center bg-surface-0 border-border-200">
            <div class="flex items-center gap-3">
                <div
                    class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-50 text-primary-600 shrink-0">
                    <x-icon class="fa-solid fa-id-card" />
                </div>
                <div>
                    <h4 class="text-xs font-bold text-ink-900">Kuota Pegawai & Kasir (Paket
                        {{ $currentPlan?->name ?? 'Starter' }})</h4>
                    <p class="text-[11px] text-ink-700 mt-0.5">
                        Mendaftarkan <strong
                            class="{{ $isUserFull ? 'text-semantic-danger' : 'text-primary-600' }}">{{ $employeeCount }}</strong>
                        dari maksimal <strong class="text-ink-900">{{ $maxUsers }}</strong> pengguna/kasir toko.
                    </p>
                </div>
            </div>

            @if ($isUserFull)
                <a href="{{ route('billing.index') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-md transition shrink-0">
                    <x-icon class="fa-solid fa-arrow-up-right-from-square text-[10px]" />
                    <span>Tambah Kuota Pegawai</span>
                </a>
            @endif
        </div>

        <!-- Header Halaman & Action Button -->
        <div
            class="flex flex-col justify-between gap-4 pb-6 mb-8 border-b sm:flex-row sm:items-center border-border-200">
            <div>
                <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                    Manajemen Pegawai & Akses
                </h1>
                <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                    Kelola akun tim operasional toko, pendaftaran kasir baru, dan kewenangan akses manajer.
                </p>
            </div>

            @if ($isUserFull)
                <button disabled title="Kuota pegawai paket {{ $currentPlan?->name }} sudah penuh"
                    class="inline-flex items-center justify-center gap-2 px-5 text-xs font-semibold border rounded-md cursor-not-allowed text-ink-400 bg-surface-100 border-border-200 h-11 opacity-60 font-body md:text-sm shrink-0">
                    <x-icon class="text-xs fa-solid fa-lock" />
                    <span>Kuota Pegawai Penuh</span>
                </button>
            @else
                <button @click="showAddModal = true"
                    class="inline-flex items-center justify-center gap-2 px-5 text-xs font-semibold text-white transition-colors rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body md:text-sm shrink-0">
                    <x-icon class="text-xs fa-solid fa-user-plus" />
                    <span>Tambah Pegawai Baru</span>
                </button>
            @endif
        </div>

        <!-- Metric Stat Summary Cards -->
        <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-3 md:gap-6">
            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Total Anggota
                        Tim</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-ink-900">{{ $employees->count() }}
                    </p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-primary-50 text-primary-600 shrink-0">
                    <x-icon class="fa-solid fa-id-card" />
                </div>
            </div>

            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Kasir
                        Operasional</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-primary-600">
                        {{ $employees->where('role', 'kasir')->count() }}</p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-primary-100 text-primary-700 shrink-0">
                    <x-icon class="fa-solid fa-cash-register" />
                </div>
            </div>

            <div
                class="flex items-center justify-between p-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">
                <div>
                    <span class="text-xs font-semibold tracking-wider uppercase font-body text-ink-700">Manajer &
                        Admin</span>
                    <p class="mt-2 font-mono text-2xl font-semibold md:text-3xl text-accent-500">
                        {{ $employees->whereIn('role', ['admin', 'manager'])->count() }}</p>
                </div>
                <div
                    class="flex items-center justify-center text-lg rounded-md w-11 h-11 bg-accent-100 text-accent-700 shrink-0">
                    <x-icon class="fa-solid fa-user-shield" />
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="mb-6 overflow-hidden border rounded-lg shadow-sm bg-surface-0 border-border-200">
            <div class="w-full overflow-x-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-surface-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-ink-400">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr
                            class="h-12 text-xs font-semibold tracking-wider uppercase border-b bg-surface-100 border-border-200 font-heading text-ink-700">
                            <th class="px-5 py-3">Nama Lengkap</th>
                            <th class="px-5 py-3">Email Login</th>
                            <th class="px-5 py-3 text-center">Hak Akses (Role)</th>
                            <th class="px-5 py-3">Tanggal Bergabung</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y font-body md:text-sm text-ink-900 divide-border-200">
                        @foreach ($employees as $emp)
                            <tr class="h-12 transition-colors hover:bg-surface-100/60">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center w-8 h-8 text-xs font-bold rounded-full bg-primary-100 text-primary-700 font-heading shrink-0">
                                            {{ substr($emp->name, 0, 2) }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="font-semibold leading-tight text-ink-900">{{ $emp->name }}</span>
                                            @if ($emp->id === auth()->id())
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold bg-surface-100 text-ink-400 border border-border-200 rounded-full">
                                                    Sesi Anda
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-3 font-mono text-xs text-ink-700">{{ $emp->email }}<span class="block mt-1 text-xs">{{ $emp->status === 'active' ? 'Akses aktif' : 'Akses nonaktif' }}</span></td>

                                <td class="px-5 py-3 text-center">
                                    @if ($emp->role === 'admin')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-semantic-danger bg-red-50 rounded-full">Owner
                                            / Admin</span>
                                    @elseif($emp->role === 'manager')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-accent-700 bg-accent-100 rounded-full">Manager</span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-primary-700 bg-primary-100 rounded-full">Kasir
                                            Store</span>
                                    @endif
                                </td>

                                <td class="px-5 py-3 font-mono text-xs text-ink-700">
                                    {{ $emp->created_at->format('d M Y') }}</td>

                                <td class="px-5 py-3 text-center">@if ($emp->id !== auth()->id() && $emp->id !== auth()->user()->tenant->user_id)<form action="{{ route('employees.status', $emp->id) }}" method="POST" class="mb-2">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $emp->status === 'active' ? 'inactive' : 'active' }}"><button class="min-h-11 px-3 text-xs font-semibold underline">{{ $emp->status === 'active' ? 'Nonaktifkan akses' : 'Aktifkan akses' }}</button></form>@endif
                                    @if ($emp->id !== auth()->id())
                                        <button type="button"
                                            @click="showDeleteModal = true; deleteUrl = '{{ route('employees.destroy', $emp->id) }}'; employeeName = '{{ addslashes($emp->name) }}'"
                                            class="inline-flex items-center gap-1.5 h-8 px-3 text-xs font-semibold text-semantic-danger bg-red-50 hover:bg-semantic-danger hover:text-white rounded-md transition-colors">
                                            <x-icon class="text-xs fa-solid fa-user-slash" />
                                            <span>Hapus Akses</span>
                                        </button>
                                    @else
                                        <span class="text-xs italic font-body text-ink-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Tambah Akun Pegawai -->
        <div data-dialog x-show="showAddModal" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="[&[x-cloak]]:!hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink-900/40 backdrop-blur-[2px]">

            <div @click.away="showAddModal = false"
                class="w-full p-6 border rounded-lg shadow-lg bg-surface-0 max-w-modal-sm border-border-200">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-border-200">
                    <h3 class="text-lg font-semibold font-heading text-ink-900">Tambah Akun Pegawai</h3>
                    <button @click="showAddModal = false" class="p-1 text-ink-400 hover:text-ink-900">
                        <x-icon class="text-base fa-solid fa-xmark" />
                    </button>
                </div>

                <form action="{{ route('employees.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="field-name" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Nama Lengkap Pegawai
                            <span class="text-semantic-danger">*</span></label>
                        <input id="field-name" type="text" name="name" required placeholder="Contoh: Andi Wijaya"
                            class="w-full px-3 text-xs border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                    </div>

                    <div>
                        <label for="field-email" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Email Login System
                            <span class="text-semantic-danger">*</span></label>
                        <input id="field-email" type="email" name="email" required placeholder="andi@tokokita.com"
                            class="w-full px-3 text-xs border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                    </div>

                    <div>
                        <label for="field-role" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Tingkat Hak Akses
                            (Role) <span class="text-semantic-danger">*</span></label>
                        <select id="field-role" name="role" required
                            class="w-full px-3 text-xs border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600">
                            <option value="kasir">Kasir (Hanya Penjualan POS & Shift Operasional)</option>
                            <option value="manager">Manager (POS + Kelola Produk, Stok & Laporan)</option>
                            <option value="admin">Admin / Partner (Akses Penuh Seluruh Sistem)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="field-password" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Kata Sandi <span
                                    class="text-semantic-danger">*</span></label>
                            <div class="relative" x-data="{ passwordVisible: false }"><input id="field-password" type="password" :type="passwordVisible ? 'text' : 'password'" name="password" required placeholder="Min 8 karakter"
                                class="pr-12 w-full px-3 text-xs border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600"><x-password-toggle control="field-password" /></div>
                        </div>

                        <div>
                            <label for="field-password_confirmation" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">Konfirmasi Sandi
                                <span class="text-semantic-danger">*</span></label>
                            <div class="relative" x-data="{ passwordVisible: false }"><input id="field-password_confirmation" type="password" :type="passwordVisible ? 'text' : 'password'" name="password_confirmation" required placeholder="Ulangi sandi"
                                class="pr-12 w-full px-3 text-xs border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600"><x-password-toggle control="field-password_confirmation" /></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-3">
                        <button type="button" @click="showAddModal = false"
                            class="flex-1 text-xs font-semibold rounded-md h-11 bg-surface-100 hover:bg-border-200 text-ink-900">Batal</button>
                        <button type="submit"
                            class="flex-1 text-xs font-semibold text-white rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700">Daftarkan
                            Pegawai</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Confirm Hapus Akses -->
        <div data-dialog x-show="showDeleteModal" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="[&[x-cloak]]:!hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink-900/40 backdrop-blur-[2px]">

            <div class="w-full p-6 border rounded-lg shadow-lg max-w-modal-sm bg-surface-0 border-border-200"
                @click.away="showDeleteModal = false">

                <div
                    class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-50 text-semantic-danger">
                    <x-icon class="text-xl fa-solid fa-user-xmark" />
                </div>

                <div class="mb-6 text-center">
                    <h3 class="text-lg font-semibold font-heading text-ink-900">Hapus Akses Pegawai?</h3>
                    <p class="mt-2 text-xs leading-relaxed font-body text-ink-700">
                        Apakah Anda yakin ingin menghapus akun pegawai <span class="font-semibold text-ink-900"
                            x-text="employeeName"></span>? Pegawai tersebut tidak akan bisa login kembali ke sistem
                        kasir GrowPOS.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" @click="showDeleteModal = false"
                        class="flex-1 text-xs font-semibold rounded-md h-11 bg-surface-100 hover:bg-border-200 text-ink-900">Batal</button>
                    <form :action="deleteUrl" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full text-xs font-semibold text-white rounded-md h-11 bg-semantic-danger hover:bg-red-700">Ya,
                            Hapus Akses</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
