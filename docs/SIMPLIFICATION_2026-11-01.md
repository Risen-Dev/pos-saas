# Penyederhanaan GrowPOS untuk UMKM — 1 November 2026

Perubahan diterapkan pada working tree lokal di atas perubahan audit yang sudah ada. Fitur khusus barber, laundry, bengkel, dan bidang jasa lain tetap ditunda. Tidak ada commit, push, deployment, pembayaran live, atau migrasi database aplikasi yang dilakukan dalam pekerjaan ini. Migrasi pengujian hanya menggunakan SQLite in-memory dan fixture sementara.

## Ruang lingkup 12 poin

| Poin | Implementasi |
|---|---|
| 1. Fokus UMKM | Menggunakan jenis usaha dan modul yang sudah ada. Fitur barang, jasa, dan dapur muncul sesuai konfigurasi, tanpa arsitektur plugin atau layanan baru. |
| 2. Bahasa dan indikator | Istilah kasir, pelanggan, pemasok, hitung stok, dan sesi penjualan disederhanakan. Indikator jaringan mengikuti browser, tidak mengklaim kesehatan server. POS menunjukkan antrean dan proses sinkronisasi. |
| 3. Menu | Sidebar dipadatkan menjadi Ringkasan, Kasir, Transaksi, Katalog, Stok, Laporan, dan Pengaturan; dapur/jasa muncul sesuai modul. Fitur pendukung dipindah ke navigasi kontekstual dan halaman Pengaturan. Menu terkunci dan shortcut AI di header dihilangkan. Sidebar mobile diberi lebar dan backdrop. |
| 4. Mulai berjualan | Setup pertama dan tambah bisnis memiliki halaman serta route GET yang berbeda. Isian bisnis dibagi melalui partial yang sama. Pengguna baru melihat onboarding; pemilik bisnis melihat kembali/batal, informasi kuota, dan penjelasan langganan terpisah. Istilah buka/tutup shift pada POS diganti mulai jualan/tutup penjualan. |
| 5. Katalog | Tambah katalog menonjolkan nama, harga, kategori, dan stok. Kategori Umum dan SKU dibuat server bila kosong; kategori baru bisa dibuat saat menyimpan produk. Modal, kode, foto, dan satuan berada di detail lanjutan. Validasi kategori lintas tenant tetap ditolak. |
| 6. Kasir | Item biasa bisa masuk keranjang dengan satu klik; item dengan pecahan/varian/tambahan/kemasan tetap menggunakan dialog. Transfer bank dan QRIS toko manual membutuhkan konfirmasi petugas bahwa dana sudah masuk. Identitas transaksi dan bukti pemeriksaan tersimpan. QRIS otomatis tetap memakai provider dan paket berbayar. |
| 7. Stok | Tambah stok cepat tersedia di halaman Stok dengan pencarian barang dan modal opsional per satuan dasar. Modal dihitung rata-rata tertimbang. Pengiriman ulang dengan kunci operasi sama tidak menambah stok lagi; perubahan isi dengan kunci sama ditolak. Produk bervarian menggunakan alur penyesuaian varian yang sudah ada. |
| 8. Pelanggan dan bon | Pelanggan serta bon dasar tersedia untuk paket aktif termasuk Starter. Keanggotaan, pengaturan poin, dan pemberian poin tetap dibatasi Growth/Scale; field loyalitas disembunyikan pada Starter. Promo tetap dapat diakses lewat Transaksi. |
| 9. Laporan dan keuangan | Laporan menonjolkan penjualan, kas aktual, pembayaran manual, dan bon. Modal, estimasi laba, dan biaya berada dalam rincian. Kas & Pengeluaran dipisahkan dari Saldo QRIS & Pencairan. Label metode manual tidak disamakan dengan QRIS otomatis. |
| 10. Koneksi, perangkat, struk | Antrean offline dan retry tetap memakai referensi yang sama. Metode manual memerlukan koneksi dan tidak dikirim ulang otomatis di latar belakang. Struk/detail nota menunjukkan pemeriksaan manual. Panduan menjelaskan batas offline serta cetak 58/80 mm. Pengujian perangkat fisik tetap pekerjaan lapangan. |
| 11. Paket | Sesuai persetujuan pengguna, batas 100 transaksi Starter dihapus, harga dan kapasitas produk/pengguna/outlet dipertahankan, pelanggan/bon dasar dibuka, loyalitas dan QRIS otomatis tetap berbayar. Copy Scale berfokus pada UMKM beberapa outlet. Tampilan manfaat dinormalisasi untuk paket lama tanpa mengubah harga database. |
| 12. Perlindungan data | Kontrol tenant, role, paket, total server, ledger tunai, stok, pembayaran provider, dan tes sebelumnya dipertahankan. Ditambahkan regresi setup bisnis, katalog minimal, Starter, pembayaran manual, dan tambah stok cepat. |

## Perubahan halaman bisnis

- `GET /setup-business` (`tenants.create`): onboarding bisnis pertama. Pemilik yang sudah memiliki bisnis diarahkan ke halaman tambah bisnis.
- `GET /tenants/create` (`tenants.add`): tambah bisnis untuk pemilik yang sudah mempunyai bisnis. Pengguna tanpa bisnis diarahkan ke onboarding.
- `POST /setup-business` dan `POST /tenants`: menggunakan validasi, pemeriksaan kepemilikan/kuota, serta transaksi database yang sama.
- Tautan tambah bisnis dari daftar bisnis/profil menggunakan halaman pengelolaan. Redirect registrasi, verifikasi email, dan guard tanpa tenant tetap menuju onboarding.
- Membuat bisnis baru menjadikannya bisnis aktif; data dan langganan bisnis sebelumnya tidak digabung atau dipindahkan.

## Pembayaran dan stok

- Metode `transfer` dan `qris_manual` adalah pencatatan pembayaran yang diperiksa petugas toko. Tidak ada verifikasi bank otomatis, charge provider, komisi platform, atau penambahan saldo dompet GrowPOS.
- Nominal pembayaran manual harus persis total server, status harus lunas, konfirmasi wajib, dan checkout key wajib. Nota menyimpan waktu pemeriksaan, petugas, dan referensi opsional. Retry berbeda isi ditolak.
- Uang laci tetap hanya mengikuti pembayaran tunai dan operasi kas. Pembayaran manual tidak masuk ledger tunai.
- Retur/refund yang sudah tersedia tetap untuk transaksi tunai; pencairan/refund bank atau QRIS manual tidak diotomatisasi dalam perubahan ini.
- Tambah stok cepat tidak mencatat pembayaran pembelian atau memotong uang laci. UI menjelaskan pencatatan pengeluaran tunai secara terpisah.
- Offline tetap berupa draft/antrean pada layar yang sudah terbuka, bukan jaminan aplikasi dapat dibuka dari awal tanpa internet. Jangan hapus penyimpanan browser sebelum seluruh antrean selesai.

## Persiapan rollout

Dua migration tambahan perlu diterapkan pada lingkungan tujuan setelah backup dan review:

1. `2026_11_01_000001_add_manual_payment_verification_to_orders`: metadata pemeriksaan pembayaran manual.
2. `2026_11_01_000002_add_stock_operation_key`: kunci idempotensi stok beserta hash permintaan.

Build aset baru harus disertakan bersama kode dan schema. PlanSeeder sudah diperbarui untuk instalasi baru; tampilan paket lama juga mengikuti aturan baru melalui model. Tidak perlu menjalankan seluruh DatabaseSeeder pada database merchant. Ikuti runbook operasi yang sudah ada untuk rollout dan restore.

Pengujian browser memakai `scripts/prepare-e2e.php` yang menolak database selain fixture SQLite dalam temporary directory. Fixture direset hanya setelah guard lulus. Server tes menggunakan `--no-reload` agar variabel database fixture tidak diganti oleh `.env` aplikasi.

## Verifikasi

- PHP: **131/131 tes lulus**, 1.253 assertions. Suite sebelumnya tetap lulus, ditambah 8 tes regresi penyederhanaan.
- JavaScript: **22/22 tes lulus**, mencakup pembayaran manual, konfirmasi wajib, kegagalan jaringan, dan retry eksplisit.
- Browser: **5/5 skenario lulus** menggunakan Google Chrome melalui Playwright pada database fixture terisolasi. Transaksi tunai pecahan dan transfer manual selesai sampai tautan struk.
- Enam halaman (Ringkasan, Stok, Laporan, Pengaturan, Tambah Bisnis, Tambah Katalog) diperiksa pada lebar **320, 390, dan 1440 px**, tanpa error JavaScript halaman atau overflow dokumen.
- Setup pertama dan tambah bisnis diuji sebagai dua pengguna berbeda. Halaman tambah bisnis tidak memiliki penanda langkah registrasi atau ajakan masuk kembali.
- Production build, Prettier, dan `git diff --check` lulus. PHP syntax diperiksa pada 140 file tanpa kesalahan.
- Bukti hasil tes dan screenshot berada di [simplification-2026-11-01](simplification-2026-11-01/).

Batas verifikasi: tidak ada uji printer/scanner fisik, bank/provider live, pengiriman email nyata, benchmark/concurrency MySQL baru, restore offsite, atau deploy production. Gunakan gate dan runbook operasional sebelum rollout. Tidak ada klaim bahwa antrean offline menggantikan penyimpanan server atau backup.
