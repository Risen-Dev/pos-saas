# Brief produksi — GrowPOS Reels

## Peran dan tujuan

Bertindak sebagai creative director dan motion designer untuk membuat video promosi vertikal GrowPOS yang terasa premium, mudah dipahami, dan setia pada aplikasi sebenarnya. GrowPOS adalah POS SaaS untuk UMKM yang menghubungkan kasir, stok, transaksi, kas, pelanggan, dan laporan. Pesan brand yang diprioritaskan: **“Satu Sistem, Tumbuh Bersama.”**

Tujuan video adalah menunjukkan bahwa GrowPOS membantu pemilik dan tim usaha mencatat penjualan serta memahami operasional hariannya. Utamakan alur produk yang nyata. Jangan menjanjikan hasil bisnis tertentu.

## Konteks project yang wajib diikuti

- Aplikasi utama menggunakan Laravel, Blade, Tailwind CSS, dan Alpine.js. Remotion/React hanya boleh dipakai sebagai proyek video mandiri; jangan memindahkan atau mengubah aplikasi utama demi video.
- Brand memakai logo GrowPOS dan warna hijau/putih yang sudah ada. Gunakan copy Indonesia sebagai default.
- Alur yang tersedia: ringkasan usaha, kasir/katalog/keranjang, pilihan pembayaran termasuk tunai, QRIS otomatis (bila fitur dan demo yang sah tersedia), QRIS toko manual, stok, transaksi, dan laporan operasional.
- QRIS otomatis hanya dianggap selesai setelah konfirmasi provider/server. QRIS manual hanya dicatat setelah diverifikasi oleh toko. Jangan menggambarkan QRIS tertunda sebagai pembayaran berhasil.
- Laporan menampilkan metrik transaksi dan operasional dengan definisi khusus (misalnya penjualan lunas termasuk pajak, sebelum retur). Jangan menyederhanakan penjualan menjadi laba bersih.
- Mobile-responsive tidak otomatis membuktikan semua alur kasir atau laporan tersedia/nyaman di ponsel. Tampilkan ponsel hanya jika capture viewport mobile yang nyata tersedia; selain itu akhiri dengan satu layar desktop dan brand.
- Gunakan data demo fiktif saja. Video demo lokal yang disertakan project boleh dipakai setelah memeriksa isinya; jangan tampilkan kredensial atau data pelanggan sungguhan.
- CTA/URL hanya boleh ditampilkan setelah alamat publik dikonfirmasi dari konfigurasi atau aset project.

## Art direction dan output

- Instagram Reels, 9:16, 1080×1920, 60 fps, durasi sekitar 35 detik, MP4 H.264/AAC bila aset audio yang boleh dipakai tersedia.
- Visual SaaS premium yang tenang: komposisi editorial, ruang kosong, bayangan lembut, perangkat realistis bila tersedia, transisi halus, dan UI yang terbaca di ponsel.
- Pertahankan identitas warna dan tipografi GrowPOS. Hindari neon, HUD generik, efek 3D berlebihan, serta efek yang menutupi UI.
- Pertahankan safe area Reels. Gerak kamera dan angka harus deterministik dan berbasis frame.
- Rasio kreatifnya tetap mengutamakan produk. Pakai UI capture asli; jangan menggambar ulang dashboard atau mengarang komponen produk.

## Storyboard yang sesuai materi project

1. **Pembuka (0–4 dtk):** logo dan pesan “Satu Sistem, Tumbuh Bersama.” Bangun konteks usaha Indonesia dengan aset brand yang tersedia.
2. **Kasir (4–13 dtk):** tampilkan katalog, kategori, keranjang, dan total dari alur demo yang terekam. Copy: “Penjualan tercatat, alur kasir tetap sederhana.” Jangan mengklaim transaksi selesai jika rekaman tidak membuktikannya.
3. **Operasional (13–20 dtk):** tampilkan layar stok/produk yang benar-benar ada. Copy: “Stok ikut terpantau.” Jangan menyiratkan stok berubah kecuali perubahan itu terlihat.
4. **Laporan (20–29 dtk):** gunakan layar laporan aktual. Copy: “Lihat penjualan dan aktivitas usaha.” Angka demo harus tetap jelas sebagai data demo; jangan sebut sebagai hasil pelanggan.
5. **Penutup (29–35 dtk):** logo, “GrowPOS”, slogan “Satu Sistem, Tumbuh Bersama”, dan CTA/URL hanya bila terkonfirmasi. Jangan menampilkan mockup ponsel kecuali UI mobile riil sudah ditangkap.

Sesuaikan pemenggalan waktu jika rekaman aktual membutuhkan ritme berbeda. Jangan menambah adegan sukses pembayaran, grafik tertentu, KPI, atau interaksi yang tidak tampak pada sumber.

## Batasan teknis dan validasi

- Video adalah proyek mandiri di `growpos-motion/`; sumber UI diambil dari aset demo lokal atau environment demo yang diizinkan.
- Pertahankan aset sumber dan jelaskan asalnya. Jangan memasukkan rahasia, data pelanggan, atau koneksi produksi.
- Render hasil MP4 sungguhan, periksa resolusi, rasio, frame rate, durasi, audio, dan beberapa frame representatif sebelum menyatakan selesai.
- Jika mobile capture, pembayaran sukses, audio berlisensi, atau URL publik tidak tersedia, hilangkan elemen tersebut dan tulis keterbatasannya dengan jelas.
