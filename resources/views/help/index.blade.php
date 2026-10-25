<x-app-layout>
    @section('title', 'Panduan usaha')
    <div class="[max-width:1280px] [margin:auto] p-7 [color:#18372d] max-[701px]:[padding:20px_16px]">
        <div class="[font-size:11px] font-bold [letter-spacing:0.14em] uppercase [color:#647c72]">TEMAN OPERASIONALMU
        </div>
        <h1
            class="[font-size:clamp(24px,_3vw,_34px)] font-bold [letter-spacing:-0.04em] [line-height:1.2] [margin:8px_0]">
            Panduan singkat GrowPOS.</h1>
        <p class="[color:#65796f] [font-size:13px] [line-height:1.6]">Jawaban yang bisa langsung dipakai saat usaha
            sedang beroperasi.</p>
        <div class="grid [gap:18px] mt-6 [max-width:850px]">
            @foreach ([
        ['Mulai berjualan', 'Masukkan produk/layanan atau impor Excel dari halaman Mulai berjualan. Periksa harga, modal, pajak, dan stok. Di kasir, buka shift dengan jumlah uang awal di laci.'],
        ['Varian, topping, dan resep', 'Admin membuka Menu & resep untuk menambahkan pilihan ukuran, harga topping, dan jumlah bahan per porsi. Stok bahan memakai satuan integer: gunakan gram atau ml. Resep dasar berlaku juga untuk varian. Modal varian belum dihitung; estimasi laba ditandai belum lengkap.'],
        ['Transaksi tunai saat koneksi putus', 'Selama layar kasir masih terbuka, transaksi tunai dapat disimpan sebagai antrean di perangkat. Nota lokal belum merupakan transaksi server. Jangan hapus data browser atau keluar akun sebelum antrean tersinkron. Buka kembali kasir dengan akun dan toko yang sama untuk mencoba sinkronisasi.'],
        ['Antrean gagal disinkronkan', 'Harga, stok, dan shift tetap diperiksa server. Perubahan harga, stok habis, atau shift yang sudah ditutup dapat menahan transaksi. Jangan menerima pembayaran kedua kali. Simpan unduhan antrean dan minta admin memeriksa penyebab, kemudian coba lagi dengan referensi yang sama.'],
        ['Pembayaran QRIS', 'QRIS membutuhkan koneksi dan konfirmasi server. Jangan menganggap pembayaran lunas hanya berdasarkan layar pelanggan. Setelah status lunas diterima, cetak struk. Jika terjadi gangguan, periksa transaksi yang sama; jangan membuat pembayaran baru sebelum statusnya jelas.'],
        ['Simpan pesanan sementara', 'Tombol Simpan pesanan menyimpan keranjang di perangkat tanpa memotong stok atau menerima pembayaran. Buka kembali pesanan, periksa harganya, lalu lanjutkan pembayaran. Draft tidak berpindah antarperangkat.'],
        ['Jenis usaha dan fitur', 'Admin dapat membuka Pengaturan usaha untuk memilih retail, kelontong, jasa, kuliner, atau campuran. Fitur dapur bersifat pilihan. Mengubah pengaturan tidak menghapus katalog atau transaksi.'],
        ['Barang dan jasa', 'Pilih tipe Barang Fisik atau Jasa saat mengisi katalog. Jasa tidak mengurangi stok barang. Keduanya bisa berada dalam satu nota. Untuk barcode, fokuskan kolom pencarian kasir lalu pindai kode dan Enter.'],
        ['Antrean dapur (kuliner)', 'Jika fitur kuliner diaktifkan, pesanan lunas masuk ke dapur. Gunakan tombol Mulai buat → Tandai siap → Sudah disajikan. Antrean memperbarui otomatis ketika halaman aktif. Hanya barang yang ditandai masuk dapur yang ditampilkan.'],
        ['Cetak struk', 'Hubungkan printer melalui perangkat dan driver yang tersedia. Klik Cetak struk untuk membuka dialog cetak browser. Pilih cetak 58 mm atau 80 mm di detail nota, sesuaikan ukuran kertas pada driver, gunakan margin minimum, dan matikan header/footer browser. Lakukan satu uji cetak sebelum membuka toko.'],
        ['Tutup shift', 'Selesaikan sinkronisasi di setiap perangkat terlebih dahulu. Hitung uang fisik, masukkan ke dialog tutup shift, dan periksa selisih. Kas shift memperhitungkan penjualan tunai, cicilan yang diterima, refund, dan pengeluaran.'],
        ['Data dan biaya langganan', 'Katalog dapat diekspor dari Mulai berjualan; laporan tersedia di menu Laporan. Harga, masa berlaku, dan batas paket tercantum pada halaman Langganan. Nilai penjualan QRIS dan saldo pencairan berbeda jika ada komisi platform.'],
    ] as [$title, $body])
                <details
                    class="bg-white [border:1px_solid_#e1e9e4] rounded-lg [padding:22px] [box-shadow:0_2px_5px_#17392c03] max-[701px]:[padding:18px]">
                    <summary class="font-semibold cursor-pointer">{{ $title }}</summary>
                    <p class="[color:#65796f] [font-size:13px] [line-height:1.6] mt-3">{{ $body }}</p>
                </details>
            @endforeach
        </div>
        <div class="flex flex-wrap items-center gap-3 mt-6"><a
                class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px] ![background:#17694d] !text-white ![border-color:#17694d] hover:![background:#12523d]"
                href="{{ route('getting-started') }}">Mulai berjualan</a><a
                class="inline-flex items-center justify-center gap-2 min-h-11 [padding:10px_17px] [border:1px_solid_#dae4dd] rounded-md bg-white [color:#234a3b] [font-size:13px] font-semibold cursor-pointer hover:[background:#f1f7f3] disabled:[opacity:0.45] disabled:cursor-wait focus-visible:[outline:3px_solid_#90b9a5] focus-visible:[outline-offset:3px]"
                href="{{ route('billing.index') }}">Lihat paket & batas penggunaan</a></div>
        <p class="[color:#65796f] [font-size:13px] [line-height:1.6] mt-6">Butuh bantuan? <a class="underline"
                href="mailto:{{ config('launch.support_email') }}">{{ config('launch.support_email') }}</a>. Sertakan
            nomor nota dan langkah masalah; jangan kirim password atau data kartu.</p>
    </div>
</x-app-layout>
