# GrowPOS — Creative Direction

## Gagasan utama

**GrowPOS — The Art of Selling.** Film memperlakukan transaksi sebagai rangkaian gestur yang tenang dan presisi: katalog dipilih, pesanan disusun, lalu bisnis terbaca melalui angka yang jelas. Produk selalu menjadi bagian dari lingkungan usaha nyata, bukan halaman web yang memenuhi kanvas.

## Audit materi yang tersedia

### Identitas GrowPOS

- Warna antarmuka POS: hijau utama `#17694D`, hijau gelap/teks `#18372D`, kanvas `#F5F7F3`, garis kartu `#E0E8DF`, bidang fallback produk krem `#F2EEE4`.
- Blade layout memuat **Plus Jakarta Sans** (judul, 600/700), **Inter** (teks UI, 400/500/600), dan **IBM Plex Mono** (angka teknis, 600). Font WOFF2 latin dan lisensi disimpan di `assets/fonts/`.
- Mark resmi tersedia sebagai hijau dan putih di `assets/branding/`. Ejaan wordmark ialah `GrowPOS`.
- Label nyata mencakup “TERMINAL KASIR”, “Ada pesanan apa?”, “Cari produk, layanan, atau barcode…”, “Pesanan sekarang”, “Subtotal”, “Diskon menu”, dan “Total”.

### UI dan aset produk yang benar-benar ada

- Rekaman `public/video/growpos-demo.mp4` menyediakan capture aplikasi nyata: katalog POS, dialog opsi produk, pesanan satu item, dashboard, stok, dan laporan operasional. Capture serta contact sheet audit ada di `assets/ui-captures/`.
- Nilai transaksi contoh konsisten dengan rekaman: Kopi Susu Gula Aren `Rp 19.800 / gelas`; subtotal `Rp 22.000`; diskon menu `-Rp 2.200`; total `Rp 19.800`. Opsi extra shot `+Rp 5.000` dan susu oat `+Rp 6.000`.
- Kartu produk memakai fallback inisial seperti `KO`, `AM`, `MA`, `RO`; tidak ditemukan foto produk terverifikasi. Aset marketing di `public/` tampak polished, tetapi UI/angka generik dan tidak dipakai sebagai UI autentik.

### Mengapa prototipe motion terasa datar

- Komposisi bertumpu pada kanvas UI pucat dan kartu placeholder seperti demonstrasi web; tidak ada lingkungan fotografis, konteks kasir, atau cahaya yang memisahkan objek.
- Perangkat, katalog, keranjang, dan teks terasa sebagai satu permukaan. Belum ada hierarki kedalaman atau gerak perhatian dari katalog ke pesanan.
- Ruang kosong tampak sebagai sisa layout, bukan negative space yang sengaja dibentuk untuk pesan dan cahaya.
- Screenshot layar lebar diperkecil tanpa crop yang fokus; label/harga kehilangan keterbacaan.
- Transisi mengandalkan pergeseran/zoom; belum ada sebab-akibat visual antara pilihan produk dan perubahan keranjang.
- Prototipe Art of Selling ditinjau melalui `growpos-motion/remotion/src/scene.jsx` dan contact sheet render saat ini. Build/render V1–V3 sudah tidak ada setelah pembersihan sebelumnya, jadi frame lama tidak dapat diaudit ulang.

## Bahasa visual

- **Latar:** fotografi kedai kopi malam, espresso/walnut, hijau botani, sumber cahaya tungsten. Gunakan bokeh jauh dengan objek foreground yang dikenali—gelas, struk, tangan, meja, perangkat.
- **Produk:** terminal, laptop, ponsel, dan tablet tampil sebagai objek fisik dengan tepi, bezel, pantulan, bayangan sentuh, dan perspektif kamera. Hindari geometri dekoratif yang tidak membantu membaca produk.
- **Layar:** UI GrowPOS asli diproyeksikan pada bidang layar. Layar terang dan tajam, tetapi tidak mengambil alih seluruh gambar.
- **Warna:** hijau GrowPOS muncul di UI dan aksen tipis. Fotografi memakai hutan hijau tua dan amber hangat; putih UI tetap netral.
- **Tipografi:** headline editorial singkat di area gelap yang tidak menutupi perangkat. Satu judul dominan, satu subjudul, garis hijau, folio kecil. Overlay keyframe memakai Avenir Next sistem sebagai proxy raster; UI layar mempertahankan font asli. Produksi perlu typeset dengan Plus Jakarta Sans/Inter berlisensi dan terverifikasi.
- **Kedalaman:** satu fokus utama per frame; foreground lembut, perangkat tajam, latar blur. 3D/parallax khusus untuk perangkat, tangan, meja, lapisan kaca, dan bayangannya.
- **Nada:** tenang, meyakinkan, presisi, manusiawi. Hindari neon, orbit abstrak, kilatan acak, dekorasi berlebihan, dan panel data yang tidak ada pada produk.

## Pemetaan kedalaman dan elemen UI

### Adegan yang memang mendapat manfaat dari 3D

1. Pembuka terminal di meja: perangkat, gelas, struk, dan lingkungan perlu jarak kamera nyata.
2. Interaksi kasir: tangan dan terminal perlu urutan foreground/background yang meyakinkan.
3. Laptop reveal: sudut layar, keyboard, tepi casing, dan pantulan membangun kehadiran produk.
4. Laptop dan ponsel: beda ukuran/jarak menyampaikan penggunaan lintas perangkat.
5. Analitik: tablet menjadi fokus; lapisan kaca boleh memberi kedalaman tipis tanpa HUD dekoratif.
6. Hero: perangkat, gelas, struk, dan meja membentuk still-life seimbang.

### Elemen yang tetap menjadi lapisan 2D independen

- Kartu/ubin produk, label, harga, indikator hover/fokus, kursor/titik sentuh.
- Baris item keranjang, kuantitas, badge item, subtotal, diskon, total, tombol tindakan.
- Jenis pesanan, pelanggan, dan opsi produk.
- KPI, angka stok, baris laporan, label grafik, dan tooltip.

Pisahkan lapisan itu untuk menunjukkan sebab-akibat. Jangan memiringkan angka/grafik sebagai objek 3D; cukup ikuti bidang layar.

## Batas hasil tahap ini

Enam gambar adalah keyframe pra-produksi, bukan render final atau bukti UI responsif lintas perangkat. Layar ponsel pada frame 4 dan 6 memakai crop transaksi desktop yang ditata ulang; belum ada capture mobile responsif dalam paket ini. Frame 2 memakai matte warna untuk mengembalikan tangan di atas layar; tepi matte masih perlu diperbaiki pada produksi.
