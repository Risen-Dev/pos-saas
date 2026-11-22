# 08 — Review design system dan tampilan visual

## Yang sudah bekerja

- Palette memakai hijau untuk identitas/aksi primer, putih dan permukaan netral; cocok untuk konteks operasional tanpa dekorasi berlebih.
- Tailwind config menyebut font heading Plus Jakarta Sans, body Inter, angka JetBrains Mono/IBM Plex Mono; palet, spacing dasar 4 px, radius dan elevation jelas.
- Screenshot QA historis menunjukkan keterbacaan umum cukup baik pada desktop dan ada perhatian pada varian mobile.
- Komponen shell/tab menggunakan minimum height `min-h-11` untuk tab context; tombol sidebar mobile memiliki accessible label.

## Risiko konsistensi dan hierarchy

1. **Semantik warna bercampur.** Hijau primary sekaligus brand, success, nav aktif. Jangan gunakan hanya hue untuk mengartikan status; token semantik perlu state background/border/text dengan kontras yang diuji.
2. **Breakpoint `sm` dimulai 360 px.** Perangkat 320–359 tidak mendapat responsive `sm` utilities; screenshot 320 memperlihatkan pressure pada nav/tabel. Perlakukan 320 px sebagai supported or explicitly unsupported based on audience evidence, bukan incidental fallback.
3. **Terlalu banyak lapis card/nav.** Settings grid dan contextual tabs menambah framing visual tanpa selalu membedakan tugas/urgensi. Gunakan heading/section/list rows untuk link sederhana, card untuk unit kerja mandiri dengan status/action.
4. **Angka dan tabel.** Pakai tabular/monospaced untuk nominal dan align right; jangan memaksa data tabular desktop ke kolom sempit mobile.
5. **Konsistensi antar workspace.** Kasir perlu densitas dan target sentuh yang berbeda dari back office. Token dasar bisa satu, layout shell dan density harus task-specific.
6. **Focus, hover dan motion.** Code sample hanya menilai struktur terbatas; audit belum memverifikasi visible focus di seluruh komponen, reduced-motion, focus restoration atau contrast semua state.

## Rekomendasi visual

- Pertahankan basis identitas hijau, tetapi ubah aplikasi dari “grid kartu untuk semua” menjadi hierarki konten: satu judul kerja, satu aksi dominan, summary ringkas, tabel/daftar prioritas, detail progressive disclosure.
- Pisahkan kasir menjadi permukaan transaksi fokus, high contrast, angka total dominan, status shift terkunci terlihat, dan target kontrol yang aman disentuh. Owner back office dapat lebih padat tetapi tetap satu primary action.
- Gunakan semantic tokens untuk success/warning/danger/info selain color name; token menentukan foreground/background/border dan disabled/focus state.
- Sediakan grid mobile berpola, bukan wrap tak terkontrol: nav pendek/picker, summary stack, row layout, sticky action bila mencegah scroll berulang.
- Tetapkan aturan visual density: kasir touch 48 px controls; desktop data grid 40 px; destructive actions tidak diberi warna primer.
- Review screenshot setelah setiap perubahan pada 320/360/390/768/1024/1440; termasuk teks panjang Bahasa Indonesia, data kosong, error dan status.

## Benchmark eksternal (pola, bukan skor kompetitif)

Dokumentasi resmi Square mendukung item grid yang dapat dikustomisasi, shortcut dan pencarian SKU/barcode; Shopify POS mendokumentasikan pencarian/penambahan item dan sync antar channel; Lightspeed mendokumentasikan layar sales untuk cart dan pembayaran serta penerimaan parsial purchase order. Ini mendukung evaluasi alur POS dan receiving yang terintegrasi, bukan klaim GrowPOS kalah/menang pada UI. Tidak ada aplikasi competitor yang dibuka langsung.

- [Square item grid setup](https://squareup.com/help/us/en/article/8334-set-up-item-grid)
- [Square build customer cart](https://squareup.com/help/us/en/article/8238-build-your-customer-s-cart-in-the-square-retail-pos-app)
- [Shopify POS overview](https://help.shopify.com/en/manual/sell-in-person/shopify-pos)
- [Shopify process sales transaction](https://help.shopify.com/en/manual/sell-in-person/shopify-pos/process-sales-transaction)
- [Lightspeed sales screen](https://retail-support.lightspeedhq.com/hc/en-us/articles/360035206534-Using-the-Retail-POS-R-Series-Sales-screen)
- [Lightspeed receiving PO](https://retail-support.lightspeedhq.com/hc/en-us/articles/8153229791131-Receiving-items-in-purchase-orders)
- Moka dan Olsera ditinjau dari halaman produk/pricing, tidak cukup detail untuk menilai perilaku layar: [Moka pricing](https://www.mokapos.com/harga), [Olsera stock](https://www.olsera.com/id/stokbarang).

## Keputusan

Gunakan [DESIGN.md](DESIGN.md) sebagai **proposal** yang perlu dikaji/diadopsi; nilai sekarang menyalin token existing agar tidak mengarang sistem. Dokumen ini belum menjadi SSOT aktif dan belum dihubungkan ke build. Skill `design-md` tidak dapat menjalankan workflow Stitch tanpa project/connection Stitch.

