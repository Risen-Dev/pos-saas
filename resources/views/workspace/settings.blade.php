<x-app-layout>
@section('title', 'Pengaturan')
<div class="max-w-5xl px-4 py-6 mx-auto sm:px-6">
    <h1 class="text-2xl font-bold">Pengaturan</h1>
    <p class="mt-2 text-sm text-ink-700">Sesuaikan usaha dan kelola akun dari satu tempat.</p>
    @php
        $admin = auth()->user()->role === 'admin';
        $links = [
            ['getting-started', 'Mulai berjualan', 'Panduan katalog sampai transaksi pertama.', $admin],
            ['business.edit', 'Jenis usaha & fitur', 'Pilih fitur barang, jasa, atau dapur yang diperlukan.', $admin],
            ['tenants.index', 'Bisnis saya', 'Ganti bisnis aktif, ubah profil, atau tambahkan bisnis.', $admin],
            ['employees.index', 'Pegawai', 'Kelola akses pemilik, pengelola, dan kasir.', $admin],
            ['settings.index', 'Pajak & loyalitas', 'Atur pajak dan poin pelanggan sesuai paket.', $admin],
            ['billing.index', 'Langganan', 'Lihat paket, masa aktif, dan tagihan.', $admin],
            ['activity-logs.index', 'Riwayat aktivitas', 'Telusuri perubahan penting pada usaha.', $admin],
            ['help', 'Panduan singkat', 'Bantuan untuk pekerjaan sehari-hari.', true],
            ['profile.index', 'Akun saya', 'Profil dan keamanan akun.', true],
            ['admin.withdrawals.index', 'Persetujuan pencairan', 'Pengelolaan pencairan oleh operator platform.', \Illuminate\Support\Facades\Gate::allows('platform-admin')],
        ];
    @endphp
    <div class="grid gap-4 mt-6 sm:grid-cols-2">
        @foreach($links as [$target, $label, $description, $visible])
            @if($visible)<a href="{{ route($target) }}" class="block p-5 bg-white border rounded-lg border-border-200 hover:border-primary-600"><h2 class="font-semibold">{{ $label }} →</h2><p class="mt-2 text-sm text-ink-700">{{ $description }}</p></a>@endif
        @endforeach
    </div>
</div>
</x-app-layout>
