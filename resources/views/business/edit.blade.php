<x-app-layout>
@section('title', 'Pengaturan usaha')
<div class="[max-width:1280px] [margin:auto] p-7 [color:#18372d] max-[701px]:[padding:20px_16px]">
    <x-page-header>
        <div>
            <div class="[font-size:11px] font-bold [letter-spacing:0.14em] uppercase [color:#647c72]">GROWPOS UNTUK USAHAMU</div>
            <h1 class="[font-size:clamp(24px,_3vw,_34px)] font-bold [letter-spacing:-0.04em] [line-height:1.2] [margin:8px_0]">Satu usaha, fitur sesuai kebutuhan.</h1>
            <p class="[color:#65796f] [font-size:13px] [line-height:1.6]">Jenis usaha mengatur istilah dan contoh katalog. Pilih fitur operasional secara terpisah; barang dan jasa tetap bisa dijual bersama. Perubahan ini tidak menghapus data lama.</p>
        </div>
    </x-page-header>
    <form method="post" action="{{ route('business.update') }}" class="bg-white [border:1px_solid_#e1e9e4] rounded-lg [padding:22px] [box-shadow:0_2px_5px_#17392c03] max-[701px]:[padding:18px] mt-6 grid [gap:18px]">
        @csrf @method('PUT')
        <label class="block [font-size:12px] font-semibold [color:#536b5e] [&_input]:block [&_input]:w-full [&_input]:[margin-top:7px] [&_input]:[border:1px_solid_#dce5de] [&_input]:[border-radius:9px] [&_input]:[padding:11px] [&_input]:bg-white [&_input]:[font-size:14px] [&_input]:[color:#18372d] [&_input]:min-h-11 [&_select]:block [&_select]:w-full [&_select]:[margin-top:7px] [&_select]:[border:1px_solid_#dce5de] [&_select]:[border-radius:9px] [&_select]:[padding:11px] [&_select]:bg-white [&_select]:[font-size:14px] [&_select]:[color:#18372d] [&_select]:min-h-11 [&_textarea]:block [&_textarea]:w-full [&_textarea]:[margin-top:7px] [&_textarea]:[border:1px_solid_#dce5de] [&_textarea]:[border-radius:9px] [&_textarea]:[padding:11px] [&_textarea]:bg-white [&_textarea]:[font-size:14px] [&_textarea]:[color:#18372d] [&_textarea]:min-h-11">Jenis usaha<select name="business_type" required>
            @foreach($types as $value => $label)<option value="{{ $value }}" @selected(old('business_type', $tenant->businessType()) === $value)>{{ $label }}</option>@endforeach
        </select></label>
        <fieldset class="grid [gap:18px]"><legend class="font-semibold mb-3">Fitur operasional</legend>
            @foreach(['goods' => ['Barang & persediaan', 'Stok, pengadaan dan supplier untuk barang fisik.'], 'services' => ['Layanan jasa', 'Katalog jasa tanpa pemotongan stok barang.'], 'food' => ['Kuliner & dapur', 'Meja, topping, resep bahan, dan antrean dapur.']] as $key => [$label, $description])
            <label class="bg-white [border:1px_solid_#e1e9e4] rounded-lg [padding:22px] [box-shadow:0_2px_5px_#17392c03] max-[701px]:[padding:18px]"><input type="checkbox" name="business_modules[]" value="{{ $key }}" @checked(in_array($key, old('business_modules', $tenant->businessModules())))> <strong>{{ $label }}</strong><p class="[color:#65796f] [font-size:13px] [line-height:1.6] mt-2">{{ $description }}</p></label>
            @endforeach
        </fieldset>
        <p class="[color:#65796f] [font-size:13px] [line-height:1.6]">Fitur pilihan tidak mengubah batas atau hak akses paket langganan.</p>
        <button class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] ![background:#17694d] !text-white ![border-color:#17694d] hover:![background:#12523d]">Simpan pengaturan usaha</button>
    </form>
</div>
</x-app-layout>
