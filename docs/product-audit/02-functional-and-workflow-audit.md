# 02 — Audit fungsional dan workflow

Temuan berikut menggabungkan route/service/source dan dokumen QA lama. Severity UX mengikuti Blocker/High/Medium/Low; prioritas bisnis mengikuti P0–P3. Confidence menyebut basis bukti secara eksplisit.

## Onboarding hingga transaksi pertama

**Alur yang ada (teramati dari route dan halaman):** daftar/verifikasi → buat tenant/profil bisnis → setup jenis bisnis/fungsi → mulai berjualan/katalog → atur pembayaran/pajak/pegawai sesuai kebutuhan → masuk POS → transaksi.

**Kekuatan:** getting-started, konfigurasi jenis usaha, katalog awal dan POS tersedia. **Risiko usability:** Pengaturan mengandung “Mulai berjualan” bersama preferensi/pengelolaan akun; pengguna baru dapat membaca halaman sebagai daftar konfigurasi umum, bukan setup progres. Belum ada live state yang membuktikan checklist menyelesaikan kondisi masing-masing bisnis.

**Rekomendasi:** checklist berbasis prasyarat dengan CTA satu langkah selanjutnya, status selesai yang berasal dari data, pilihan “lewati untuk sekarang”, indikator transaksi uji/pertama, dan jalur pemulihan untuk verifikasi email/invite gagal. **P2 / Medium / Inferred.**

## Checkout dan pesanan

**Alur utama yang ditemukan:** buka POS → cari/pilih item → varian/tambahan bila ada → atur qty/catatan → pelanggan/promo bila perlu → pilih metode bayar → simpan pesanan → struk / transaksi berikutnya. Ada tunai, transfer/manual QRIS, Midtrans otomatis, offline queue, bon/partial settlement, pembatalan, dan retur menurut route/services serta dokumentasi. `OrderPricing`, `OrderPaymentService`, `OrderReturnService`, `OrderStock` menunjukkan pemisahan domain.

**Risiko utama:** pembatalan/retur, partial payment dan payment callback mengubah beberapa ledger/stock/report downstream; status yang pengguna lihat harus sama dengan state yang menjadi sumber laporan. Audit ini tidak mengirim transaksi nyata atau mereproduksi callback.

**Rekomendasi verifikasi** (lihat [11 test plan](11-acceptance-and-test-plan.md)): klik submit dua kali, callback duplikat, jaringan putus di commit, item yang habis saat ada di keranjang, perubahan harga sebelum checkout, diskon plus pajak, pembayaran campuran/sebagian, batal setelah provider sukses, retur cash vs non-cash, share/cetak gagal. Prioritas **P0** untuk cacat yang menduplikasi uang/order atau bocor tenant; status sekarang **Inferred** sampai concurrency/integrasi diuji.

## Produk, stok, pembelian

Alur terlihat: produk/kategori/varian/addon → stok/adjustment/stocktake → purchase order dan supplier → receive → stock movements → penjualan/retur konsumsi atau pengembalian stok. Food dapat menautkan bahan/recipe dan dapur; goods memakai kuantitas retail.

**Perlu dikunci:** satuan dasar vs pecahan, batas stok negatif, partial receiving, biaya/modal setelah purchase, hasil stocktake bersamaan dengan checkout, bahan resep saat void/return, serta alasan adjustment. Jangan menampilkan “profit” jika cost basis kosong atau tidak konsisten. `RecipeStock`, `OrderStock`, `RetailQuantity`, `StockAdjustment` adalah bukti domain service; bukan bukti semua edge behavior lolos.

## Shift, kas, pembayaran dan rekonsiliasi

Kasir memulai shift dengan opening cash, menjalankan penjualan dan cash in/out, menutup dengan actual count; owner melihat selisih dan riwayat. `CashLedger` dan `CashPeriodSummary` menunjukkan domain cash dan agregasi periode. Pastikan dashboard close shift menjelaskan expected cash hanya dari aliran cash, menyingkirkan pembayaran noncash, dan menampilkan void/refund/cash movement terpisah. Selisih wajib meminta catatan/alasan dan bisa ditinjau dengan audit trail.

## Pelanggan, bon, loyalty

Pelanggan dapat ditautkan ke POS/riwayat; bon menambahkan status piutang dan pelunasan; loyalty poin/stamp dan public card memakai token share. Pertanyaan produk: apakah cashier boleh membuat pelanggan cepat tanpa pindah layar? Apakah pembayaran bon mengurangi piutang idempoten? Bisakah pemilik mencabut link kartu? Jangan minta data pelanggan lebih banyak dari kebutuhan transaksi dan loyalty.

## Dashboard, laporan, AI

Data report disiapkan lewat `SalesDataset`, cash/finance controllers, export jobs, dan AI service. Risiko adalah metrik berlabel “profit” di atas biaya kosong, refund tidak dikurangkan dengan cara konsisten, tanggal/bisnis aktif berbeda antara job dan halaman, serta angka AI tanpa rentang/filter sumber. `ExportReportJob` meng-capture tenant pada permintaan; verifikasi bahwa job tetap di tenant itu saat user mengganti tenant.

Acceptance setiap kartu angka: definisi tertulis; timezone/periode; net/gross; refund/void; cost coverage; empty state; export sama dengan halaman; tenant filter. AI perlu mencantumkan rentang, metrik sumber, dan membedakan estimasi dari fakta.

## Kegagalan dan recovery

| Kondisi | Perilaku yang harus diverifikasi | Recovery yang direkomendasikan |
|---|---|---|
| Payment provider lambat/callback ulang | order pending, tidak jadi dua transaksi/ledger | status pending yang bisa direkonsiliasi; callback idempoten; retry aman |
| Koneksi putus saat checkout offline | local queue vs server commit | tampilkan queued/synced/failed per order; jangan kirim ulang tanpa idempotency key |
| Stok berubah setelah item masuk cart | final stock validation | tahan/beritahu perubahan; izinkan ganti qty atau hapus item sebelum charge |
| Submit/receive/return gagal | transaksi parsial lintas tabel | rollback domain transaction; tampilkan tindakan berikutnya dan kode referensi |
| CSV/backup/export gagal | job status dan file visibility | retry job, pertahankan filter dan tenant; beri masa berlaku unduhan |
| User kehilangan permission | status saat halaman terbuka | 403 bermakna, kembali ke area berizin, simpan draft aman bila tidak sensitif |
| Dataset kosong/terlalu besar | blank state, pagination, timeout | filter yang membantu, agregat/pagination, progress dan pembatalan export |

State dinamis di tabel belum disimulasikan karena aplikasi tidak dapat dijalankan pada browser. Ini adalah acceptance targets, bukan klaim bug teramati.

