@csrf
<div class="space-y-5">
    <label class="block text-sm font-semibold" for="business-name">Nama bisnis
        <input id="business-name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="organization" placeholder="Contoh: Toko Senja" class="block w-full mt-2 border-border-200 rounded-md">
    </label>
    <label class="block text-sm font-semibold" for="business-type">Jenis usaha
        <select id="business-type" name="business_type" required class="block w-full mt-2 border-border-200 rounded-md">
            @foreach(\App\Support\BusinessProfile::TYPES as $value => $label)
                <option value="{{ $value }}" @selected(old('business_type', 'retail') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <p class="text-sm text-ink-700">Jenis usaha menyesuaikan katalog dan fitur awal. Anda bisa mengubahnya nanti.</p>
    <label class="block text-sm font-semibold" for="business-email">Email bisnis
        <input id="business-email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required maxlength="255" autocomplete="email" class="block w-full mt-2 border-border-200 rounded-md">
    </label>
    <label class="block text-sm font-semibold" for="business-phone">Nomor WhatsApp
        <input id="business-phone" type="tel" name="phone" value="{{ old('phone') }}" required maxlength="20" autocomplete="tel" class="block w-full mt-2 border-border-200 rounded-md">
    </label>
    <label class="block text-sm font-semibold" for="business-address">Alamat bisnis
        <textarea id="business-address" name="address" required rows="2" autocomplete="street-address" class="block w-full mt-2 border-border-200 rounded-md">{{ old('address') }}</textarea>
    </label>
    <details class="p-4 border border-border-200 rounded-md" @if($errors->has('img_logo')) open @endif>
        <summary class="cursor-pointer text-sm font-semibold">Tambahkan logo (opsional)</summary>
        <label for="business-logo" class="block mt-3 text-sm">Logo bisnis</label>
        <input id="business-logo" type="file" name="img_logo" accept="image/jpeg,image/png,image/webp" class="block w-full mt-2 text-sm">
        <p class="mt-2 text-xs text-ink-700">JPG, PNG, atau WebP, maksimal 2 MB.</p>
    </details>
</div>
