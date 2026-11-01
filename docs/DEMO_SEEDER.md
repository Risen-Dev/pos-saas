# Data demo GrowPOS

Seeder demo menyiapkan tiga tenant contoh yang bisa dipakai untuk menjelajahi aplikasi:

- Kedai Senja: F&B, menu, opsi tambahan, transaksi, kas, pelanggan, poin, dan diskon.
- Toko Maju: retail, satuan kemasan, harga grosir, stok, pembelian, dan stok opname.
- Laundry Ceria: bisnis jasa, katalog layanan, pesanan layanan, pelanggan, dan transaksi.

## Cara menjalankan

Jalankan migrasi dan seeder dari root proyek pada environment `local`:

```bash
php artisan migrate --seed
```

Atau seed ulang data demo saja:

```bash
php artisan db:seed --class=GrowPosDemoSeeder
```

Seeder menolak berjalan di environment selain `local` dan `testing`. Seeder ini tidak menghapus data; data contohnya memakai email, slug, SKU, dan nomor dokumen khusus demo, sehingga bisa dijalankan ulang tanpa menggandakan fixture.

## Akun demo

Semua akun memakai password `GrowPOS-Demo-2026!` secara default. Atur `GROWPOS_DEMO_PASSWORD` di `.env` sebelum menjalankan seeder untuk memakai password lokal sendiri.

| Bisnis | Owner | Kasir |
| --- | --- | --- |
| Kedai Senja | `owner.kedai@growpos.test` | `kasir.kedai@growpos.test` |
| Toko Maju | `owner.toko@growpos.test` | `kasir.toko@growpos.test` |
| Laundry Ceria | `owner.laundry@growpos.test` | `kasir.laundry@growpos.test` |

Masing-masing tenant memakai paket Growth aktif agar fitur paket berbayar dapat dijelajahi. Data demo tidak menjalankan transaksi melalui endpoint POS, jadi fixture tidak dimaksudkan sebagai histori akuntansi produksi.
