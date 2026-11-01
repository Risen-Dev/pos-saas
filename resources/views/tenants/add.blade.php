<x-app-layout>
@section('title', 'Tambah bisnis')
<div class="max-w-2xl px-4 py-6 mx-auto sm:px-6">
    <a href="{{ route('tenants.index') }}" class="text-sm font-semibold text-primary-600">← Kembali ke bisnis saya</a>
    <h1 class="mt-4 text-2xl font-bold">Tambah bisnis</h1>
    <p class="mt-2 text-sm text-ink-700">Kelola bisnis lain dari akun yang sama. Setelah disimpan, bisnis baru menjadi bisnis aktif. Katalog, transaksi, dan langganannya terpisah.</p>
    @php($plan = auth()->user()->tenant?->currentPlan())
    <p class="p-4 mt-4 text-sm rounded-md bg-primary-50">{{ auth()->user()->tenants()->count() }} bisnis dimiliki. {{ $plan ? 'Paket bisnis aktif mengizinkan maksimal '.$plan->max_outlets.' bisnis/outlet.' : 'Aktifkan paket bisnis saat ini sebelum menambah bisnis.' }}</p>
    <form action="{{ route('tenants.store-additional') }}" method="post" enctype="multipart/form-data" class="p-6 mt-5 space-y-5 bg-white border rounded-lg border-border-200" x-data="{ submitting: false }" @submit="submitting = true">
        @include('tenants.partials-form')
        <div class="flex flex-wrap items-center gap-4">
            <button :disabled="submitting" class="min-h-11 px-5 py-3 font-semibold text-white rounded-md bg-primary-600 disabled:opacity-50" x-text="submitting ? 'Menyimpan…' : 'Tambah bisnis'">Tambah bisnis</button>
            <a href="{{ route('tenants.index') }}" class="min-h-11 px-4 py-3 text-sm">Batal</a>
        </div>
    </form>
</div>
</x-app-layout>
