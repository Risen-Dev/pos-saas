<x-app-layout>
    @section('title', 'Edit Informasi Tenant')

    <div class="px-4 py-6 mx-auto md:px-6 lg:px-8 max-w-modal-lg">

        <!-- Breadcrumb & Title Section -->
        <div class="mb-6">
            <a href="{{ route('tenants.index') }}"
                class="inline-flex items-center gap-2 mb-2 text-xs font-semibold font-body text-primary-600 hover:text-primary-700">
                <x-icon class="text-xs fa-solid fa-arrow-left" />
                <span>Kembali ke Manajemen Tenant</span>
            </a>
            <h1 class="font-heading font-bold text-2xl md:text-[28px] text-ink-900 leading-tight">
                Edit Profil Tenant
            </h1>
            <p class="mt-1 text-xs font-body md:text-sm text-ink-700">
                Perbarui rincian profil & kontak operasional untuk outlet <span
                    class="font-semibold text-ink-900">{{ $tenant->name }}</span>.
            </p>
        </div>

        <div x-data="{ isSubmitting: false }">
            <form action="{{ route('tenants.update', $tenant->id) }}" method="POST" enctype="multipart/form-data"
                class="space-y-6" @submit="isSubmitting = true">
                @csrf
                @method('PUT')

                <!-- Card Content utama (Radius-lg = 16px, Padding = 24px) -->
                <div class="p-6 space-y-5 border rounded-lg shadow-sm bg-surface-0 border-border-200">

                    <!-- Logo Upload Field -->
                    <div x-data="{ imgPreview: '{{ $tenant->img_logo ? asset('storage/' . $tenant->img_logo) : null }}' }">
                        <label class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                            Logo Toko / Outlet (Opsional)
                        </label>

                        <div
                            class="flex items-center gap-4 p-4 border border-dashed rounded-md border-border-200 bg-surface-100">
                            <div
                                class="flex items-center justify-center w-16 h-16 overflow-hidden border rounded-md bg-surface-0 border-border-200 shrink-0">
                                <template x-if="imgPreview">
                                    <img :src="imgPreview" class="object-cover w-full h-full">
                                </template>
                                <template x-if="!imgPreview">
                                    <x-icon class="text-xl fa-solid fa-store text-ink-400" />
                                </template>
                            </div>

                            <div class="flex-1 min-w-0">
                                <input type="file" name="img_logo" accept="image/*"
                                    @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { imgPreview = e.target.result; }; reader.readAsDataURL(file); }"
                                    class="block w-full text-xs transition-all cursor-pointer text-ink-700 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-primary-100 file:text-primary-700 hover:file:bg-primary-600 hover:file:text-white" />
                                <p class="mt-1.5 text-[11px] text-ink-400">Format: JPG, PNG, WEBP. Maks. 2MB.</p>
                            </div>
                        </div>
                        @error('img_logo')
                            <p class="mt-1 text-xs font-semibold text-semantic-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Toko Field (Height 44px, Radius-sm = 6px) -->
                    <div>
                        <label for="field-name" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                            Nama Bisnis / Outlet <span class="text-semantic-danger">*</span>
                        </label>
                        <input id="field-name" type="text" name="name" value="{{ old('name', $tenant->name) }}"
                            class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 placeholder-ink-400 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100"
                            required />
                    </div>

                    <!-- Tipe Bisnis Field -->
                    <div>
                        <label for="field-business_type" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                            Kategori / Jenis Bisnis <span class="text-semantic-danger">*</span>
                        </label>
                        <select id="field-business_type" name="business_type" required
                            class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100">
                            @foreach(\App\Support\BusinessProfile::TYPES as $value => $label)
<option value="{{ $value }}" @selected(old('business_type', $tenant->businessType()) === $value)>{{ $label }}</option>
@endforeach
</select>
                    </div>

                    <!-- Email & Phone Grid -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="field-email" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                                Email Bisnis <span class="text-semantic-danger">*</span>
                            </label>
                            <input id="field-email" type="email" name="email" value="{{ old('email', $tenant->email) }}"
                                class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 placeholder-ink-400 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100"
                                required />
                        </div>

                        <div>
                            <label for="field-phone" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                                Nomor WhatsApp <span class="text-semantic-danger">*</span>
                            </label>
                            <input id="field-phone" type="text" name="phone" value="{{ old('phone', $tenant->phone) }}"
                                class="w-full px-3 text-xs transition-all border rounded-sm outline-none h-11 font-body text-ink-900 placeholder-ink-400 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100"
                                required />
                        </div>
                    </div>

                    <!-- Alamat Outlet -->
                    <div>
                        <label for="field-address" class="block font-body text-xs font-semibold text-ink-900 mb-1.5">
                            Alamat Operasional Outlet <span class="text-semantic-danger">*</span>
                        </label>
                        <textarea id="field-address" name="address" rows="3" required
                            class="w-full p-3 text-xs transition-all border rounded-sm outline-none font-body text-ink-900 placeholder-ink-400 bg-surface-0 border-border-200 focus:border-primary-600 focus:ring-2 focus:ring-primary-100"
                            placeholder="Alamat lengkap outlet bisnis Anda...">{{ old('address', $tenant->address) }}</textarea>
                    </div>
                </div>

                <!-- Action Button Submit -->
                <div class="pt-2">
                    <button type="submit" :disabled="isSubmitting"
                        class="inline-flex items-center justify-center w-full gap-2 px-6 text-xs font-semibold text-white transition-all rounded-md shadow-sm h-11 bg-primary-600 hover:bg-primary-700 active:bg-primary-900 font-body md:text-sm disabled:opacity-60 disabled:cursor-not-allowed">
                        <x-icon x-show="isSubmitting" class="[&[x-cloak]]:!hidden text-sm fa-solid fa-circle-notch fa-spin" x-cloak />
                        <span x-text="isSubmitting ? 'Menyimpan Perubahan...' : 'Simpan Perubahan'"></span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
