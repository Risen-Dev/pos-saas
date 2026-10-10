# GrowPOS Launch Audit — 25 September 2026

## Status
Release candidate setelah audit role, navigasi, transaksi, service workflow, inventory, reporting, session/cache, dan asset pipeline. Launch publik tetap memerlukan verifikasi di environment produksi dan perangkat fisik.

## Perbaikan pada audit ini
- Kasir tidak lagi merender sidebar/navbar back-office pada halaman operasional bersama.
- Pengerjaan Jasa dan Dapur tetap berada dalam shell Mode Kasir.
- Riwayat shift tenant tidak dapat dibuka kasir; kasir tetap memakai shift aktif dari POS.
- Billing, tenant management, setup bisnis, legacy service-order CRUD, dan penghapusan akun staf dibatasi dari kasir.
- Legacy `/service-orders` dibatasi admin; kasir memakai workflow `/services` yang tervalidasi dan tenant-scoped.
- AI/report endpoint dibatasi ke back-office, sehingga kasir tidak dapat mengakses analitik bisnis hanya karena paket tenant mendukung fitur.
- Role manager kini dapat mengelola katalog, produk, kategori, inventori, supplier, diskon, menu configuration, dan laporan tanpa memperoleh akses owner/admin ke billing, settings, employee management, finance, business profile, cash/refund, atau tenant management.
- Navbar/sidebar diperbaiki agar tidak menawarkan menu yang role tersebut tidak boleh buka.
- Ditambahkan regression test `CashierWorkspaceTest` dan `ManagerAccessTest`.

## Validasi yang berhasil di environment audit
- 17/17 JavaScript tests PASS.
- PHP syntax check untuk file PHP yang diubah PASS.
- Prettier format check PASS.
- Static role/navigation review PASS untuk perubahan di atas.

## Validasi yang harus dijalankan di mesin deployment
Environment audit ini tidak memiliki ekstensi PHP `mbstring`, sehingga Laravel test suite tidak dapat boot (`mb_split` tidak tersedia). `node_modules` berasal dari platform berbeda sehingga Vite native Rolldown binding Linux tidak tersedia. Ini keterbatasan environment audit, bukan kegagalan kode aplikasi. Jalankan ulang di mesin proyek/deployment:

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan test
npm ci
npm test
npm run build
php artisan view:cache
php artisan growpos:launch-check
```

Target: seluruh PHP tests PASS, 17+ JS tests PASS, production build PASS, Blade cache PASS, launch-check PASS.

## Uji manual wajib sebelum membuka merchant publik
1. Admin: dashboard, produk, kategori, stok, supplier, diskon, customer, report, finance, billing, settings, employee, business settings.
2. Manager: dashboard + operasional; pastikan owner-only route mengarah kembali ke POS/ditolak.
3. Kasir: POS, shift sendiri, transaksi, history sendiri, receipt, service workflow, kitchen, customer basic; pastikan tidak ada sidebar back-office.
4. Pembayaran: cash, QRIS production/sandbox yang sesuai, pending, gagal, retry, callback, double submit.
5. Recovery: offline, refresh, browser back, session expired, queue 422/500, reconnect.
6. Retail: pcs, paket/dus, fractional kg, wholesale tier, addon, return/refund, stock reconciliation.
7. Hardware: scanner barcode, printer 58/80 mm, cash drawer/label printer bila dijanjikan.
8. Multi-tenant: ulangi direct-object access dari Tenant A ke ID milik Tenant B untuk order/customer/product/report/invoice.
9. Backup: buat backup lalu lakukan restore drill pada database staging.

## Batas klaim launch-ready
Jangan menyebut production-ready penuh sebelum payment production, email, queue/scheduler, backup restore, HTTPS, printer/scanner, dan smoke test deployment benar-benar lolos pada server/merchant pilot.
