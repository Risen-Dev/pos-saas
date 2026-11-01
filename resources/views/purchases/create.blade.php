<x-app-layout>
    @section('title', 'Buat Purchase Order')
    <div class="max-w-5xl p-4 mx-auto space-y-6 sm:p-6">
        <div><h1 class="text-2xl font-bold">Buat Purchase Order</h1><p class="mt-1 text-sm text-ink-700">Catat pembelian dalam satuan dasar produk. HPP rata-rata diperbarui saat barang diterima.</p></div>
        <form method="POST" action="{{ route('purchases.store') }}" class="space-y-5" x-data="{ rows: @js(old('items', [['product_id'=>'','quantity'=>1,'unit_cost'=>0]])), get total() { return this.rows.reduce((sum,row) => sum + Number(row.quantity || 0) * Number(row.unit_cost || 0), 0); }, formatRupiah(value) { return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(value || 0)); }, setUnitCost(row, event) { const digits = event.target.value.replace(/\D/g, ''); row.unit_cost = digits ? Number(digits) : 0; event.target.value = digits ? this.formatRupiah(row.unit_cost) : ''; } }">
            @csrf
            <div class="grid gap-4 p-5 bg-white border rounded-lg border-border-200 sm:grid-cols-2">
                <label class="text-sm font-semibold">Pemasok<select name="supplier_id" class="block w-full mt-1 rounded-md border-border-200"><option value="">Tanpa supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>@endforeach</select></label>
                <label class="text-sm font-semibold">Perkiraan tiba<input type="date" name="expected_at" value="{{ old('expected_at') }}" class="block w-full mt-1 rounded-md border-border-200"></label>
            </div>
            <div class="p-5 space-y-4 bg-white border rounded-lg border-border-200">
                <template x-for="(row,index) in rows" :key="index">
                    <div class="grid items-end gap-3 pb-4 border-b sm:grid-cols-[minmax(0,1fr)_100px_150px_44px] border-border-200">
                        <label class="min-w-0 text-sm font-semibold">Produk<select :name="`items[${index}][product_id]`" x-model="row.product_id" class="block w-full mt-1 rounded-md border-border-200" required><option value="">Pilih produk dan satuan</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->product_name }} · {{ $product->base_unit }}</option>@endforeach</select></label>
                        <label class="text-sm font-semibold">Jumlah<input :name="`items[${index}][quantity]`" x-model="row.quantity" type="number" step="0.001" min="0.001" max="1000000000" class="block w-full mt-1 rounded-md border-border-200" required></label>
                        <label class="text-sm font-semibold">Harga beli/satuan (Rp)<input type="hidden" :name="`items[${index}][unit_cost]`" :value="row.unit_cost"><input :value="formatRupiah(row.unit_cost)" @input="setUnitCost(row, $event)" type="text" inputmode="numeric" class="block w-full mt-1 rounded-md border-border-200" required></label>
                        <button type="button" :disabled="rows.length===1" @click="rows.splice(index,1)" aria-label="Hapus baris pembelian" class="h-11 rounded-md bg-red-50 text-red-700 disabled:opacity-40">×</button>
                    </div>
                </template>
                <button type="button" @click="rows.push({product_id:'',quantity:1,unit_cost:0})" class="min-h-11 font-semibold text-primary-700">+ Tambah baris</button>
                <p class="font-semibold">Total pembelian: <span x-text="new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR'}).format(total)"></span></p>
            </div>
            <label class="block text-sm font-semibold">Catatan pembelian<textarea name="note" class="block w-full mt-1 rounded-md border-border-200">{{ old('note') }}</textarea></label>
            <div class="flex flex-wrap gap-3"><button class="px-5 min-h-11 font-semibold text-white rounded-md bg-primary-600">Simpan Purchase Order</button><a href="{{ route('purchases.index') }}" class="inline-flex items-center px-4 underline min-h-11">Batal</a></div>
        </form>
    </div>
</x-app-layout>
