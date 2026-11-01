<x-guest-layout>
<div class="min-h-screen px-4 py-8 bg-surface-100">
    <div class="max-w-xl mx-auto">
        <a href="{{ route('landing') }}" class="text-xl font-bold text-primary-600">GrowPOS</a>
        <section class="p-6 mt-6 bg-white border rounded-xl border-border-200 sm:p-8">
            <p class="text-xs font-semibold text-primary-600">LANGKAH 2 DARI 2 · BISNIS PERTAMA</p>
            <h1 class="mt-3 text-2xl font-bold">Siapkan bisnis pertama Anda</h1>
            <p class="mt-2 mb-6 text-sm text-ink-700">Akun Anda sudah siap. Lengkapi bisnis, lalu tambahkan katalog dan mulai berjualan.</p>
            @if($errors->any())<div role="alert" class="p-4 mb-5 bg-red-50 text-red-800 rounded-md"><ul class="pl-4 list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form action="{{ route('tenants.store') }}" method="post" enctype="multipart/form-data" x-data="{ submitting: false }" @submit="submitting = true">
                @include('tenants.partials-form')
                <button :disabled="submitting" class="w-full min-h-11 px-4 py-3 mt-6 font-semibold text-white rounded-md bg-primary-600 disabled:opacity-50" x-text="submitting ? 'Menyimpan…' : 'Simpan & lanjutkan'">Simpan & lanjutkan</button>
            </form>
            <form method="post" action="{{ route('logout') }}" class="mt-4 text-center">@csrf<button class="min-h-11 px-4 text-sm text-ink-700">Keluar</button></form>
        </section>
    </div>
</div>
</x-guest-layout>
