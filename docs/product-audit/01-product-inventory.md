# 01 — Inventaris produk

**Basis:** repository GrowPOS pada HEAD `c2b0187`, route map `php artisan route:list --json` (154 route pada pemeriksaan sebelumnya), model/controller/service/view/migration/test dan dokumentasi. Inventory ini memetakan implementasi yang ditemukan; “belum ditemukan” tidak berarti pasti tidak ada.

## Arsitektur yang terlihat

Laravel dengan server-rendered Blade, route web bernama, middleware/authorization Laravel, database migrations dan Eloquent; JavaScript ringan (Alpine/JS) untuk interaksi POS/admin; Tailwind CSS 3 dan Vite. Aset tampak terbagi antara layout workspace owner, shell kasir, dan halaman publik. Domain utama diproses di controller + service (pricing, stok, ledger, pembayaran, retur, loyalty, billing, export/backup, AI). Tidak ditemukan bukti bahwa seluruh transaksi menggunakan API terpisah.

## Inventaris modul

| Modul / tujuan | Pengguna & entry point | Implementasi / relasi | Kematangan dari bukti | Isu / rekomendasi awal |
|---|---|---|---|---|
| Akun dan autentikasi | calon owner, owner, pegawai; login/register/verification/OAuth routes | Auth controllers, tenant assignment, employee invitations/role flows | Implemented; jalur edge produksi perlu validasi | Uji onboarding gagal/ulang, email, invite, dan akses setelah role berubah. |
| Onboarding bisnis | owner baru; getting-started, business setup, catalog starter | `GettingStarted`, tenant/business setup, seeded/sample catalog | Implemented; beberapa langkah bersifat panduan | Berikan langkah berikutnya berbasis status (profil, item, pembayaran, kasir pertama), hindari kartu onboarding berulang di shell. |
| Tenant, profil & outlet | owner multi-tenant; Bisnis saya, ganti bisnis | `TenantController`, route `tenants.switch`, owned tenants dan batas paket | Implemented untuk tenant switch | Label paket memakai “outlet/cabang”, tetapi konsolidasi outlet bersama belum ditemukan. Tetapkan apakah entitas adalah outlet atau bisnis independen. |
| Role & pegawai | admin/owner dan kasir; Pegawai/settings | user role, tenant-bound employees, gates/policies | Implemented | Verifikasi least privilege, route-level authorization, dan efek pencabutan akses pada sesi aktif. |
| POS & checkout | kasir/owner; Kasir | POS view/JS, `OrderPricing`, `RetailQuantity`, `OrderPaymentService`, offline queue | Implemented; gateway/manual paths berbeda | High frequency; kurangi perpindahan halaman, ukur keyboard/touch, idempotency/offline retry. |
| Pesanan, pembayaran, bon, retur | kasir/owner; Transaksi, detail pesanan, payment review | orders, payments, returns, settlement/receivables; `OrderReturnService` | Implemented; refund provider otomatis tidak terbukti | Peta status/uang/stok satu lifecycle; validasi partial payment dan pengembalian lintas metode. |
| Katalog & harga | owner; Produk/Kategori/Varian/Pilihan | product/category/variant/addon/menu routes, import/export; retail catalog/pricing | Implemented; bisnis goods/food/service bervariasi | Definisikan pengalaman tambah item cepat dan validasi harga modal vs margin. |
| Persediaan & pembelian | owner/operator; Stok, purchase, supplier, stocktake | inventory movement, stocktake, materials/recipe, purchase receive, `RecipeStock`, `OrderStock`, `StockAdjustment` | Implemented; beberapa domain hanya untuk tipe usaha | Hubungkan pembelian sampai penerimaan, partial receive, adjustment reason, retur, dan jejak perubahan. |
| Dapur dan layanan | kru dapur/teknisi/owner; Antrean dapur, Pengerjaan jasa | module gates, kitchen/services routes/views | Implemented sebagai modul kondisional | Role/task navigation harus muncul hanya jika modul dan izin aktif; state antrean belum live diverifikasi. |
| Shift dan kas | kasir membuka/menutup; owner rekonsiliasi | shift routes; cash in/out; `CashLedger`, `CashPeriodSummary` | Implemented | Acceptance perlu bedakan uang tunai aktual, noncash, void, return, dan transaksi terlambat. |
| CRM dan loyalty | owner/kasir; pelanggan, poin/stamp/rewards | customer routes, `CustomerPoints`, `CustomerStamps`, `LoyaltyProgram`, public loyalty card | Implemented | Data card publik sensitif; jelaskan share/revoke/expiry dan minimisasi data. |
| Promo/diskon | owner/kasir; promo/discount routes dan checkout | discount model/validation, pricing integration | Implemented | Periksa stacking, rentang aktif, pembulatan, dan alasan diskon di audit trail. |
| Dashboard, laporan, ekspor, AI | owner; Ringkasan/Laporan | `SalesDataset`, cash/finance reports, exports jobs, AI analytics/Gemini | Implemented dengan data source berbeda | Setiap metrik perlu definisi periode, cakupan tenant, biaya dan refund; AI harus merujuk data dan memperlihatkan batas keyakinan. |
| Billing SaaS & QRIS | owner; Langganan; admin platform review/withdrawal | subscription, Midtrans/billing services, reconciliation, payout operations | Implemented tetapi dependency eksternal perlu staging evidence | Uji webhook signature, replay/idempotency, renewal/downgrade dan keterlambatan callback. |
| Admin platform | operator; withdrawal/review routes | gates `platform-admin`, approval payout | Implemented terpisah sebagian | Keluarkan dari workspace toko sepenuhnya; jangan sekadar sembunyikan dari kartu Pengaturan. |
| Backup, export, health, operational support | admin/operator | `BackupArchive`, heartbeat, health/support, runbook | Implemented sebagian/operasional | Runbook menggambarkan deploy in-place; restore MySQL dan failure drill belum dibuktikan di audit ini. |

## Modul dan fitur di luar bukti

API publik luas, event bus eksternal, queue worker production, backup restore yang pernah diuji, mekanisme multi-outlet konsolidasi, WCAG lengkap, performa dataset besar, real webhook/gateway failure, dan offline merge lintas perangkat tidak dapat dipastikan dari pemeriksaan statis/screenshot. Tandai sebagai **perlu verifikasi**, bukan hilang.

## Status implementasi yang dipakai

- **Implemented**: route/view/controller/model/service tersedia dan alur utama terbentuk.
- **Implemented, state belum diverifikasi**: kode tersedia, tetapi browser/live dependency tidak bisa diuji.
- **Potential gap**: ekspektasi produk/copy mengarah ke kemampuan yang tidak ditemukan dalam model/alur yang ditinjau.
- **Cleanup candidate**: route/controller/service tampak tak terpakai; belum cukup bukti untuk label orphan atau menghapus.

Controller `InvoiceController`, `PlanController`, dan `PaymentController` perlu referential check sebelum dianggap mati. Tidak direkomendasikan menghapusnya berdasarkan nama/route list saja.

