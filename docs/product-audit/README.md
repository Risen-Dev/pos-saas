# GrowPOS — audit produk, workflow, dan UX

Audit berbasis kode dan dokumentasi repository per 22 November 2026 (HEAD `c2b0187`). Cakupan meliputi struktur produk, alur operasional, UI/UX, information architecture, desain visual, reliabilitas, dan rencana penerimaan. Temuan yang menyebut implementasi mengacu pada route, Blade, controller, service, migration, test, atau dokumen yang ditautkan; screenshot QA yang tersedia bertanggal 1 November 2026. Audit browser live tidak berhasil karena server lokal ditolak saat bind `127.0.0.1:8765`; karena itu detail state dinamis yang belum ditangkap screenshot diberi confidence Inferred/Hypothesis.

## Ringkasan eksekutif

GrowPOS sudah memiliki fondasi operasional yang lebar untuk UMKM: POS, transaksi dan retur, katalog, stok dan pembelian, shift dan kas, pelanggan/loyalty, laporan, langganan, serta modul dapur/jasa. Namun, breadth produk itu kini dipetakan ke navigasi yang terlalu generik untuk pekerjaan berbeda: sidebar utama, tab kontekstual, quick action, dan pusat Pengaturan bertumpuk, sementara kategori “Transaksi” juga menampung pelanggan, promo, shift, dan review pembayaran. Ini sejalan dengan kesan bahwa satu sistem navigasi dipaksakan ke semua fungsi; rekomendasi adalah satu shell visual bersama dengan navigasi berbeda per role dan area kerja, bukan satu taxonomy seragam. Isu P1 lain adalah klaim outlet/cabang pada paket: model yang teramati membuat dan mengganti tenant aktif, sedangkan alur konsolidasi laporan atau katalog lintas outlet belum ditemukan di bukti yang diperiksa. Risiko deploy juga nyata: `deploy.sh` memperbarui checkout aktif langsung dan tidak menyediakan release directory/rollback atomik, berlawanan dengan status “fixed” pada remediation lama. Screenshot lama menunjukkan basis visual hijau-putih yang konsisten, tetapi juga CTA kasir duplikat dan tab yang membungkus menjadi beberapa baris pada viewport sempit. Tidak ada kode produk diubah, tidak ada test suite atau transaksi produksi dijalankan; dokumen ini memberi prioritas dan kriteria penerimaan untuk keputusan implementasi berikutnya.

## Keputusan utama

1. **Ubah model navigasi menjadi role/task-aware.** Pertahankan komponen dan token yang konsisten, tetapi tampilkan ruang kerja kasir, operasional owner, serta administrasi platform sesuai izin dan konteks. Lihat [05 Information architecture](05-information-architecture.md).
2. **Putuskan definisi “multi-outlet”.** Jika outlet berarti unit operasional dalam satu usaha, diperlukan model parent business dan tampilan data gabungan; jika satu akun mengelola bisnis independen, ubah copy paket agar ekspektasi jelas. Jangan menganggap tenant switch setara dengan konsolidasi outlet.
3. **Tutup risiko deploy sebelum menyebut rollback siap.** Buat rilis terpisah, validasi health check, pindah symlink atomik, dan dokumentasikan rollback termasuk migrasi kompatibilitas.
4. **Prioritaskan pencegahan selisih uang/stok.** Acceptance test harus meliputi concurrent checkout, duplicate callback, partial payment, return/refund, shift closure, dan tenant scoping pada MySQL staging.
5. **Terapkan mobile sebagai workflow, bukan sekadar responsif.** Perbaiki tab konteks yang wrap, pemindaian/pencarian produk, checkout touch/keyboard, serta hierarki tabel di layar kecil.

## Temuan tertinggi

| ID | Temuan | Prioritas | Severity UX | Confidence | Dampak |
|---|---|---:|---|---|---|
| NAV-01 | Taksonomi navigasi generik meratakan pekerjaan owner dan menumpuk sidebar + tab konteks | P1 | High | Observed (kode + screenshot) | Waktu temukan menu, konteks kerja |
| PROD-01 | “Multi-outlet” dibangun sebagai beberapa tenant aktif bergantian; konsolidasi tidak terbukti | P1 | High | Observed (kode/copy), Inferred (gap) | Ekspektasi paket, analisis lintas outlet |
| TECH-01 | Deploy memperbarui checkout aktif; tidak ada rollback atomik | P1 | High | Observed (script + runbook) | Pemulihan saat rilis gagal |
| NAV-02 | Pusat Pengaturan menggabungkan setup, konfigurasi, akun, bantuan, dan admin platform | P2 | Medium | Observed (kode + screenshot) | Beban pencarian dan batas role |
| VIS-01 | CTA “Buka Kasir” muncul di navbar dan dashboard | P2 | Medium | Observed (kode + screenshot) | Hierarki aksi berulang |
| RWD-01 | Tab dan tabel operasional padat pada viewport 320–390 px | P1 | High | Observed (screenshot 1 Nov) | Akses operasi dari ponsel |
| DATA-01 | Angka profit/margin perlu dibatasi pada data biaya yang valid dan definisi yang jelas | P1 | High | Inferred (model/report coverage) | Keputusan bisnis salah bila basis biaya parsial |
| A11Y-01 | Parent nav dapat diumumkan `aria-current=page` pada child route, bukan hanya halaman yang tepat | P2 | Medium | Observed (kode) | Orientasi pembaca layar |

Prioritas, dependensi, effort, risiko dan kriteria penerimaan lengkap ada di [10 roadmap](10-prioritized-improvement-roadmap.md). Temuan lengkap dibagi menurut dimensi di [03 UX audit](03-ux-usability-audit.md) dan [09 technical risk](09-technical-risk-audit.md).

## Batasan bukti

- Repository dianalisis pada HEAD `c2b0187`; file `.agents/` dan `skills-lock.json` tampak untracked saat audit dan tidak diubah.
- Screenshot UI yang dapat ditinjau adalah artefak QA lama bertanggal 1 November, bukan hasil render build saat ini. Tidak ada halaman live yang dapat dibuka selama audit ini.
- Klaim test dan browser scenario lama dirujuk sebagai catatan historis, bukan dijalankan ulang atau disertifikasi ulang. Audit dependency online tidak dapat mengambil registry karena DNS/network gagal.
- Tidak ada akses ke tenant produksi, MySQL staging dengan concurrency, payment gateway production, perangkat kasir, atau sesi pengguna. Out-of-frame states (loading/server/network/permission/large datasets) tercatat sebagai perlu verifikasi.
- Kompetitor dibandingkan dari dokumentasi produk resmi yang tersedia; tidak ada sesi uji aplikasi kompetitor.
- Ini audit sampled, bukan klaim WCAG conformance atau audit penetrasi.

## Peta dokumen

1. [Inventaris produk](01-product-inventory.md)
2. [Audit fungsional dan workflow](02-functional-and-workflow-audit.md)
3. [Audit UX dan usability](03-ux-usability-audit.md)
4. [Matriks relasi fitur](04-feature-relationship-matrix.md)
5. [Information architecture yang diusulkan](05-information-architecture.md)
6. [User flows utama dan recovery](06-user-flows.md)
7. [Spesifikasi interaksi](07-interaction-specifications.md)
8. [Review design system](08-design-system-review.md)
9. [Audit risiko teknis](09-technical-risk-audit.md)
10. [Roadmap prioritas](10-prioritized-improvement-roadmap.md)
11. [Rencana acceptance dan test](11-acceptance-and-test-plan.md)
12. [DESIGN.md yang diusulkan](DESIGN.md) — referensi token dan prinsip frontend, belum menjadi design system aktif.

## Skills yang benar-benar digunakan

- `using-superpowers`: menentukan alur discovery dan skill yang relevan.
- `ux-audit`: struktur laporan, severity, confidence, evidence log, dan pemisahan observasi dari hipotesis.
- `product-design-and-ux`: analisis IA, task flow, alternatif pola, trade-off, dan rekomendasi handoff.
- `ui-ux-pro-max`: pencarian referensi pola navigasi, POS mobile, dan stack yang relevan; hasilnya dipakai selektif.
- `web-design-guidelines` dan `fixing-accessibility`: review semantic navigation, state aktif, target touch, label, fokus, responsif.
- `frontend-design`: kritik hierarki visual dan sistem, tanpa membangun UI.
- `brainstorming`: membandingkan opsi IA sebelum merekomendasikan pendekatan.
- `verification-before-completion`: memeriksa keberadaan deliverable dan kebersihan diff dokumentasi tanpa menjalankan test suite.

`design-md` tersedia tetapi workflow skill-nya mensyaratkan proyek Stitch; koneksi/proyek Stitch tidak tersedia. `systematic-debugging` dan `test-driven-development` tidak digunakan karena tidak ada bug yang direproduksi atau implementasi perbaikan pada tahap audit. Rencana test di dokumen 11 adalah usulan, bukan test yang dijalankan.

## Log bukti ringkas

- Struktur nav dan grouping: [`sidebar.blade.php`](../../resources/views/layouts/sidebar.blade.php), [`workspace-tabs.blade.php`](../../resources/views/components/workspace-tabs.blade.php), [`app.blade.php`](../../resources/views/layouts/app.blade.php).
- Pengaturan dan CTA berulang: [`settings.blade.php`](../../resources/views/workspace/settings.blade.php), [`navbar.blade.php`](../../resources/views/layouts/navbar.blade.php), [`dashboard/index.blade.php`](../../resources/views/dashboard/index.blade.php).
- Tenant/outlet: [`routes/web.php`](../../routes/web.php), [`TenantController.php`](../../app/Http/Controllers/TenantController.php), [`tenants/index.blade.php`](../../resources/views/tenants/index.blade.php), [`PlanSeeder.php`](../../database/seeders/PlanSeeder.php).
- Deploy: [`deploy.sh`](../../deploy.sh), [`OPERATIONS_RUNBOOK.md`](../OPERATIONS_RUNBOOK.md).
- Screenshot QA historis: [`summary-1440.png`](../simplification-2026-11-01/summary-1440.png), [`settings-1440.png`](../simplification-2026-11-01/settings-1440.png), [`stock-320.png`](../simplification-2026-11-01/stock-320.png), [`reports-390.png`](../simplification-2026-11-01/reports-390.png).

