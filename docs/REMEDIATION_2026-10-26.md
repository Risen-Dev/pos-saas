# Perbaikan audit GrowPOS — 26 Oktober 2026

Perbaikan ini menindaklanjuti [audit 25 Oktober](AUDIT_2026-10-25.md) setelah pemilik meminta implementasi. Kode sudah diubah di working tree; belum di-commit, dipush, atau dideploy. Database aplikasi/production tidak dimigrasi. Pengujian menggunakan SQLite in-memory, database fixture di direktori temporary, dan storage testing.

## Hasil utama

- Billing callback, polling dan reconciliation memakai satu service transaksional. Nominal, identitas, fraud status dan aktivasi diperiksa; callback berulang tidak memperpanjang paket dua kali. Paid tanpa subscription dapat dipulihkan melalui status provider.
- Edit katalog tidak menulis stok. Stock opname memeriksa snapshot stok dan mutasi sejak hitungan dimulai, termasuk mutasi yang secara neto kembali nol.
- Stored HTML bahan baku menjadi textContent; HTML laporan AI disanitasi saat respons pertama, cache dan render. Excel menyimpan nama sebagai teks, termasuk input berawalan `=`.
- Akun nonaktif kehilangan sesi; paket privat/nonaktif ditolak server. Owner dapat menonaktifkan staf. Tenant guard dan FK users.tenant_id diperkuat.
- Dataset penjualan menggunakan paid/completed serta tanggal transaksi; cash ledger tetap mengikuti penerimaan aktual. Ekspor berjudul Ringkasan Finansial, bukan mengklaim analisis AI.
- Katalog memiliki pencarian, filter tipe/status, pagination dan total seluruh katalog. Detail pelanggan tersedia, dengan riwayat dan piutang. POS mendukung bon yang mensyaratkan entitlement CRM dan pelanggan.
- Label/error form, dialog, pengembalian fokus, bahasa Indonesia dan laporan mobile diperbaiki. Grafik Chart.js dibundel lokal; gambar utama memakai WebP responsif.
- Deployment menggunakan release directory dan symlink atomik. Backup privat direplikasi ke disk terpisah, diverifikasi SHA-256 sebelum retention dijalankan. CI mencakup PHP, JavaScript, build, dependency audit, MySQL dan browser regression.

## Status setiap temuan

“Diperbaiki” berarti implementasi dan bukti lokal tersedia, bukan sertifikasi production atau jaminan bebas bug.

| ID | Status | Implementasi / bukti |
|---|---|---|
| F01 | Diperbaiki | BillingPaymentService; webhook membuat subscription; regresi paid tanpa aktivasi |
| F02 | Diperbaiki | Riwayat material memakai DOM/textContent; browser menunjukkan markup literal dan nol node injeksi |
| F03 | Diperbaiki pada lockfile | Composer dan npm audit akhir: nol advisory; dependency legacy Excel diganti PhpSpreadsheet langsung |
| F04 | Diperbaiki; concurrency MySQL menunggu CI | Lock tenant kemudian invoice, fresh read, marker aktivasi; tes replay/stale instance |
| F05 | Diperbaiki | Identitas/nominal cocok; capture hanya fraud accept; challenge tidak mengaktifkan paket |
| F06 | Diperbaiki | Active-account middleware, login aktif, Google guard, suspend staf; tes akses sesi |
| F07 | Diperbaiki | Server hanya menerima plan aktif/public; recheck kuota staf/outlet dalam transaksi |
| F08 | Diperbaiki | Stocktake snapshot movement_id + stock; konflik mengembalikan seluruh transaksi |
| F09 | Diperbaiki | customers.show tersedia; tes tenant isolation dan verifikasi browser |
| F10 | Diperbaiki | SalesDataset, paid/completed, sold_at fallback created_at, periode pembanding terpisah; tes unpaid/cancelled |
| F11 | Diperbaiki | SafeReportHtml; tes HTTP fake respons AI berbahaya pertama dan cache hit |
| F12 | Diperbaiki | Baseline landing expectation dan auth utility class; suite penuh lulus |
| F13 | Diperbaiki | Search nama/SKU/barcode, filter tipe/status, pagination, KPI global, base_unit asli; browser page 2/filter |
| F14 | Diperbaiki | Stock diabaikan saat update katalog, input readonly; tes penjualan di antara form edit dan simpan |
| F15 | Diperbaiki; concurrency MySQL menunggu CI | Intent UUID withdrawal, wallet lock, reuse reference, konflik nominal; tes retry |
| F16 | Perbaikan terarah selesai | Label input, error summary/association, generic dialog role/inert/focus trap/Escape; browser material dan POS. Audit WCAG menyeluruh belum dilakukan |
| F17 | Diperbaiki | Filter tanggal bertumpuk mobile; lebar dokumen sama dengan viewport pada tujuh breakpoint |
| F18 | Diperbaiki | lang=id, default locale id, copy Admin Toko dan Bisnis & Outlet |
| F19 | Diperbaiki | WebP 480/960/1440/ukuran asli; dimensi hero eksplisit; gambar tampil di browser |
| F20 | Implementasi selesai; rollout belum dilakukan | deploy.sh release atomik, backup sebelum migrate, health probe, rollback symlink; bash syntax lulus |
| F21 | Implementasi CI selesai; remote CI belum dijalankan | PHP 8.3/8.4, JS/build/audit, MySQL 8.4 invariants/restore, dua Playwright flows. Checkout/pelanggan diuji lokal via browser; belum mengklaim suite Playwright CI lulus |
| F22 | Implementasi selesai; storage/drill eksternal belum dikonfigurasi | BackupArchive private/encryption/checksum/retention, launch gate, dua tes fake storage; runbook konfigurasi dan restore |
| F23 | Diperkuat bertahap | HTTP auth tanpa tenant fail closed, check tenant exists, SalesDataset tenant eksplisit, FK nullable nullOnDelete; background/platform query tetap eksplisit sesuai kebutuhan |
| F24 | Mitigasi dan benchmark lokal selesai; kapasitas production belum diukur | Render katalog per 60 item, customer lookup 50 + selected, pending QR 50 + link riwayat. Snapshot katalog penuh dipertahankan untuk offline/scanner. MySQL EXPLAIN, p95 dan CWV staging masih diperlukan |
| F25 | Diperbaiki dengan adapter kompatibilitas | Order.service_status canonical; LegacyServiceProgress memetakan transisi dan accessor legacy mengikuti Order; judul ekspor diperbaiki |
| F26 | Perbaikan terarah selesai | Istilah bisnis, hierarchy form pembelian, toolbar katalog, dialog bersama, mobile navigation hint. Redesign total/refactor seluruh class bukan syarat perbaikan; pengujian usability dengan merchant masih diperlukan |

## Verifikasi

Bukti mesin dan screenshot berada di [fixes-2026-10-26](fixes-2026-10-26/).

- PHP: **123/123 tes**, **1.042 assertions**. Mencakup billing, stocktake, stok stale, authorization, laporan, ekspor file XLSX aktual, lookup pelanggan dan backup.
- JavaScript: **20/20 tes**, termasuk offline replay, bon dan balapan respons lookup pelanggan.
- PHP syntax: 247 file, nol kesalahan; Pint pada service/file perbaikan lulus.
- Production build dan Prettier check lulus; `bash -n deploy.sh` dan `git diff --check` lulus.
- Composer locked audit: nol advisory/abandoned. npm audit: nol vulnerability. Ini snapshot registry pada saat pemeriksaan.
- Browser fixture: login owner, katalog halaman dua/search, detail pelanggan, checkout tunai 1,5 kg Rp22.500 sampai tautan struk, bon Rp2.500 sampai saldo piutang, dialog material dengan teks markup literal dan Escape/fokus kembali.
- Laporan: viewport 320/375/390/768/1024/1440/1920, tanpa overflow dokumen; pada 320px date fields berada x=25..289 dan kedua canvas ter-render.
- Dua migration baru berhasil pada database fixture terisolasi. Tidak ada migration production yang dijalankan.

### Pengukuran kapasitas lokal

Command `growpos:benchmark-pos` menolak lingkungan selain local/testing, membuat SQLite temporary sendiri, kemudian membersihkannya. Pengukuran satu pass, render in-process, bukan HTTP/network p95, bukan benchmark MySQL dan bukan Core Web Vitals.

| SKU | Handler | Query | HTML | Peak proses |
|---:|---:|---:|---:|---:|
| 100 | 36,26 ms | 21 | 102.121 B | 46.661.632 B |
| 1.000 | 80,78 ms | 20 | 336.124 B | 55.050.240 B |
| 5.000 | 420,47 ms | 20 | 1.388.124 B | 90.701.824 B |

DOM awal dibatasi 60 produk. Payload katalog masih linear karena snapshot offline; jangan menyimpulkan kelayakan hardware kasir dari waktu handler tersebut. Jalankan pengukuran staging dengan volume transaksi dan perangkat sasaran sebelum menjanjikan kapasitas.

## Batas verifikasi dan pekerjaan lingkungan

1. MySQL Homebrew lokal 9.4 crash SIGSEGV saat inisialisasi database temporary; tidak ada server fixture yang berhasil dimulai. Job CI MySQL 8.4 sudah dibuat, tetapi belum dijalankan dari sini. Lock concurrency dan restore nyata masih harus lulus di CI/staging.
2. Tidak ada pembayaran provider live, panggilan AI live, pengiriman email nyata, transfer pencairan, konfigurasi bucket cloud atau deployment yang dilakukan. Respons provider dalam tes bersifat fixture/fake.
3. Pengujian printer/scanner fisik, failover, restore offsite sebenarnya, uptime dan WCAG/screen reader lengkap belum dilakukan.
4. Existing stocktake yang masih terbuka bisa ditolak setelah migration jika sudah memiliki riwayat mutasi. Hitung ulang dalam sesi baru; jangan memaksa hasil lama menimpa penjualan.
5. Migration FK berhenti jika terdapat users.tenant_id orphan. Periksa dan perbaiki ownership secara sadar setelah backup; migration tidak menghapus/mengganti data itu otomatis.
6. Belum dinyatakan siap production tanpa gate CI, konfigurasi operasional dan drill pada [runbook](OPERATIONS_RUNBOOK.md). Sisa ini merupakan verifikasi/konfigurasi eksternal, bukan alasan untuk menganggapnya sudah lulus.
