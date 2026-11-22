# 03 — Audit UX dan usability

## Cakupan dan evidence

Ditinjau: shell/sidebar/tab role-specific, pusat Pengaturan, ringkasan dashboard, halaman stok/laporan mobile, route map, dokumentasi simplifying pass tanggal 1 November, serta sampel screenshot QA yang tersimpan. Tidak ada sesi live yang berhasil; screenshot merupakan kondisi statis dari tanggal tersebut. Temuan yang hanya berasal dari source tidak mengklaim bagaimana animasi, loading atau server response dirender.

Severity: **Blocker** menghentikan atau membahayakan tugas inti; **High** menghambat pekerjaan rutin/menimbulkan risiko besar; **Medium** menambah beban/friksi; **Low** polish. Confidence: **Observed** (source/screenshot ada), **Inferred** (implikasi kuat), **Hypothesis** (perlu uji).

## Ringkasan temuan

### NAV-01 · Satu taxonomy menggabungkan domain kerja yang berbeda

| | |
|---|---|
| **Dimensi / severity / prioritas** | Navigation (D2), High / P1 |
| **Confidence / effort** | Observed (source + screenshot historis), M–L |
| **Lokasi** | `resources/views/layouts/sidebar.blade.php:52–61`; `components/workspace-tabs.blade.php:5–9`; `layouts/app.blade.php:90–98` |

**Bukti.** Sidebar owner merangkum pelanggan, promo, shift, dan review pembayaran di “Transaksi”; inventory, supplier, purchase order, stocktake di “Stok”; cash dan finance di “Laporan”; onboarding, bisnis/tenant, pegawai, tax/loyalty, subscription, activity log, help, profil dan platform withdrawal di “Pengaturan”. Halaman non-kasir juga menampilkan tab konteks tambahan. Bukti QA historis menunjukkan dua tingkat nav. Audit simplifikasi lama memang menyatakan pola sidebar + contextual nav disengaja, tetapi itu tidak membuktikan pengelompokan sesuai mental model tiap role.

**Dampak.** Owner perlu menafsirkan nama kategori sebelum menemukan aksi, sementara kasir dan operator bekerja dengan objek/urgensi berbeda. Tab konteks yang sama saat area berubah dapat mengaburkan “di mana saya berada”. Ini mendukung concern bahwa konsep unified dipaksakan, walau bukan berarti semua konsistensi perlu dihapus.

**Rekomendasi.** Gunakan satu visual shell/komponen, lalu susun nav berdasar role dan pekerjaan: Kasir (Jual, Transaksi, Shift); Owner (Ringkasan, Penjualan, Katalog, Persediaan, Pelanggan, Laporan); Pengaturan usaha dan Akun terpisah; admin platform di app boundary tersendiri. Jadikan subnav terbatas pada halaman dalam satu pekerjaan, bukan taxonomy kedua yang mengulang top-level. Uji findability dengan 5–7 tugas per role. Alternatif murah: rename/reorder kategori tanpa merubah shell; trade-off-nya grouping lintas fungsi masih tinggal.

**Kriteria penerimaan.** Setiap role melihat hanya entry point yang diizinkan; label area menjawab “apa yang dikerjakan”; halaman aktif tepat; tugas cari pelanggan, tutup shift, stok opname dan ubah pajak dapat ditemukan tanpa menebak lebih dari satu parent label.

### NAV-02 · Pengaturan menjadi catch-all dan mencampur tujuan

| | |
|---|---|
| **Dimensi / severity / prioritas** | Navigation, Medium / P2 |
| **Confidence / effort** | Observed (source + screenshot historis), S–M |
| **Lokasi** | `resources/views/workspace/settings.blade.php:8–19` |

**Bukti.** Pusat Pengaturan memuat mulai berjualan, jenis usaha/fitur, bisnis, pegawai, pajak/loyalty, billing, activity log, help, akun, dan persetujuan withdrawal platform. Screenshot `settings-1440.png` memperlihatkan kartu lintas tujuan dalam satu grid.

**Dampak.** Pengaturan jadi lokasi “apa saja yang belum terkelompok”; onboarding dan bantuan dicampur dengan kontrol akun/administrasi, dan platform operator punya kedekatan visual dengan pengaturan toko.

**Rekomendasi.** Bagi menjadi **Operasional usaha** (bisnis, pegawai, pajak/loyalty), **Paket & pembayaran** (langganan), **Akun & keamanan**, **Bantuan**, **Aktivitas**. Onboarding pindah menjadi checklist kontekstual sampai selesai. Withdrawal admin keluar dari workspace owner. Terima trade-off satu klik lebih banyak untuk menu berfrekuensi rendah demi kategori mudah dipahami.

### VIS-01 · Aksi kasir primer diduplikasi

| | |
|---|---|
| **Dimensi / severity / prioritas** | Visual hierarchy / consistency, Medium / P2 |
| **Confidence / effort** | Observed (source + screenshot 1440), S |
| **Lokasi** | `layouts/navbar.blade.php:58–64`; `dashboard/index.blade.php:27` |

**Bukti.** Navbar punya quick action “Buka Kasir”; dashboard juga menyediakan CTA dengan label yang sama. Screenshot dashboard historis menampilkan keduanya.

**Dampak.** Dua tindakan identik menarik perhatian sekaligus dan mengurangi ruang prioritas untuk insight dashboard.

**Rekomendasi.** Jadikan CTA pada dashboard sebagai aksi utama di konteks dashboard; pada halaman owner lainnya tampilkan quick action hanya jika observasi usage menunjukkan dibutuhkan. Jika dipertahankan di shell, hilangkan pengulangan di body. Ukur penggunaan dan pastikan kasir tetap mendapat entry point pada shell-nya.

### RWD-01 · Tab area dan tabel menjadi padat pada layar sempit

| | |
|---|---|
| **Dimensi / severity / prioritas** | Responsive / touch usability, High / P1 |
| **Confidence / effort** | Observed (screenshot QA 1 Nov; tidak live), M |
| **Lokasi** | `components/workspace-tabs.blade.php:13`; screenshot `stock-320.png`, `reports-390.png` |

**Bukti.** Tab nav menggunakan `flex-wrap`. Screenshot stok 320 px menunjukkan tab menempati beberapa baris sebelum konten; nama produk dan SKU terhimpit/berbalut. Laporan 390 px menunjukkan tab berbaris dan kartu metrik sempit.

**Dampak.** Konten kerja terdorong ke bawah, label mudah salah baca, dan pengguna kasir/owner mobile perlu banyak scroll. Ukuran viewport pada screenshot adalah bukti layout lama, bukan jaminan kondisi HEAD terbaru.

**Rekomendasi.** Untuk area aktif, batasi tab ke 3–5 tindakan inti atau gunakan selector “Bagian” yang berlabel; navigasi katalog/stok tetap bisa dicapai dari area switcher. Tabel mobile harus berubah menjadi baris ringkas dengan nama + nilai utama, detail sekunder expandable, dan aksi terlihat. Uji 320, 360, 390, 768, 1024 px dengan teks panjang dan data panjang.

### A11Y-01 · Parent route dapat ditandai sebagai halaman aktif

| | |
|---|---|
| **Dimensi / severity / prioritas** | Accessibility / navigation, Medium / P2 |
| **Confidence / effort** | Observed (source), S |
| **Lokasi** | `layouts/sidebar.blade.php:60` |

**Bukti.** `aria-current="page"` ditetapkan ketika `request()->routeIs(...$patterns)` cocok dengan pola luas seperti `orders.*`, `customers.*`, `discounts.*`, `shifts.*`, sehingga item parent “Transaksi” dapat disebut halaman saat ini pada banyak route child. Pola serupa digunakan untuk kelompok lain.

**Dampak.** Screen reader dapat menyampaikan parent area sebagai halaman persis, walau link menuju index yang berbeda; pengguna kehilangan presisi orientasi.

**Rekomendasi.** Bedakan status current page (`aria-current=page`) hanya untuk link yang URL-nya mewakili route aktif; parent grouping diberi `aria-current=location` hanya bila disclosure/semantics sesuai, atau visual active state tanpa menyesatkan semantik. Verifikasi dengan screen reader dan keyboard.

### FLOW-01 · Tenant switch membuat identitas “outlet” ambigu

| | |
|---|---|
| **Dimensi / severity / prioritas** | Product clarity / workflow, High / P1 |
| **Confidence / effort** | Observed (model/copy), gap Inferred, M–L |
| **Lokasi** | `TenantController.php`; `routes/web.php:118–123`; `views/tenants/index.blade.php`; billing/plan copy |

**Bukti.** “Bisnis saya” menyebut outlet/cabang, billing membatasi `max_outlets`, dan route mengganti tenant aktif. Data report/job di-scope ke tenant. Dalam source yang diperiksa tidak ditemukan parent-company dashboard dengan agregasi lintas tenant.

**Dampak.** Pengguna paket multi-outlet dapat mengira laporan, produk, user atau kontrol tersedia terpadu, padahal alur yang tampak memindahkan konteks satu tenant ke tenant lain. Ini adalah celah definisi produk yang perlu keputusan, bukan bukti data leak.

**Rekomendasi.** Putuskan model: tenant independen (ubah copy menjadi “bisnis yang dikelola akun ini”) atau outlet di bawah satu parent business (implementasikan context parent, hak akses, roll-up dan filter eksplisit). Jangan menyatukan inventory atau angka ke laporan sampai semantiknya disetujui.

### DATA-01 · Laporan profit perlu menampilkan batas data biaya

**Severity / priority:** High / P1. **Confidence:** Inferred. **Effort:** M.

Produk/katalog dan reports memiliki sumber biaya dan sales dataset; prompt bisnis mengandalkan margin/profit. Jika cost tidak diisi atau stok material/recipe dihitung berbeda, nilai profit memberi kepastian palsu. Tetapkan formula dan coverage; labeli margin “belum lengkap” ketika harga modal tidak tersedia; pisahkan net sales, cash received, outstanding, refund, dan estimated gross profit. Audit source tidak memverifikasi seluruh query/metrik live sehingga ini menjadi acceptance gate, bukan klaim angka salah. Refs: `SalesDataset`, product/inventory cost models, report views/controllers.

### ERR-01 · Offline/payment recovery belum dibuktikan pada UI live

**Severity / priority:** High / P1 (jika hilangnya order/duplikasi terkonfirmasi), **Confidence:** Hypothesis untuk UX; mekanisme offline tercatat di code/docs. **Effort:** M–L.

POS memiliki offline queue dan payment paths. Tidak dapat diverifikasi apakah status “menunggu/sinkron/gagal” mudah ditemukan, apakah pengguna aman retry atau idempotency key bertahan setelah reload. Tampilkan status per transaksi beserta tindakan recovery; jangan dorong submit ulang yang dapat menduplikasi charge.

### FORM-01 · State form dan validasi belum dinilai menyeluruh

**Severity / priority:** Medium / P2. **Confidence:** Hypothesis. **Effort:** M.

Rute dan form tersedia luas (produk, supplier, pegawai, bisnis, pembayaran, retur). Server-side validations tidak memperlihatkan dengan sendirinya apakah fokus dipindah ke error, nilai tersimpan, field terkait dijelaskan, atau error yang tidak terkait tetap persisten. Audit live terhalang. Rencana verifikasi: satu invalid submit, input panjang, duplicate barcode/email, error server, dan pengiriman lambat per form kritis.

## Area tanpa klaim audit visual penuh

Onboarding step-by-step dinamis, empty/loading/server/network/permission states, gerak/transisi, kontras aktual pada semua state, semua ukuran dialog, layar desktop terkecil, input scanner, keyboard tab order dan performa dataset besar belum dapat diobservasi pada build live. Rencana pengujian ada di [11](11-acceptance-and-test-plan.md).

