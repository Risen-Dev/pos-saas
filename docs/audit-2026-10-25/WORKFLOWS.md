# Peta workflow pos-saas — audit 25 Oktober 2026

Pemetaan source dan bukti pengujian; keberadaan kode tidak dianggap bukti E2E. Setiap alur memuat sebelas unsur yang diminta. Catatan bukti membedakan browser, feature test dan inspeksi source.

## W01 — Registrasi, verifikasi dan login

1. **Titik awal:** Landing atau /register.
2. **Tujuan:** Memiliki akun terverifikasi.
3. **Navigasi:** Daftar → verifikasi email → login → setup bisnis.
4. **Input:** Nama, email, password/konfirmasi; atau OAuth Google.
5. **Validasi/frontend:** Required/email/password; toggle tampilan password.
6. **Validasi/backend:** RegisteredUserController dan LoginRequest; unique email, hashing, throttle login; signed verification link.
7. **Proses database/storage:** users, sessions; notifikasi email.
8. **Perubahan status:** email_verified_at null → timestamp; sesi diregenerasi.
9. **Hasil UI/response:** Redirect ke verifikasi/setup/dashboard atau POS sesuai role.
10. **Error/recovery:** Validation redirect/422; throttle; OAuth error log dan kembali login.
11. **Kondisi akhir:** Sesi terautentikasi dengan role dan tenant yang valid.

**Bukti/batas:** Auth feature tests lolos; login/logout browser fixture; pengiriman email/OAuth nyata belum diuji. F06/F18.

## W02 — Setup dan switch bisnis/outlet

1. **Titik awal:** Pemilik terverifikasi tanpa tenant atau halaman tenant.
2. **Tujuan:** Memilih bisnis aktif.
3. **Navigasi:** Setup business / Manajemen Tenant → buat/edit/switch.
4. **Input:** Identitas bisnis, tipe, kontak, alamat, logo.
5. **Validasi/frontend:** Required, file chooser dan form; browser visual halaman daftar belum semua detail diuji.
6. **Validasi/backend:** Admin, ownership user_id, validasi file dan kuota outlet di controller.
7. **Proses database/storage:** tenants, users.tenant_id; logo public storage.
8. **Perubahan status:** tenant dibuat active; tenant_id user berubah.
9. **Hasil UI/response:** Redirect getting-started / kembali dengan pesan.
10. **Error/recovery:** Ownership 403, validation; kuota menolak; race count-then-create masih risiko.
11. **Kondisi akhir:** Konteks tenant aktif untuk operasi selanjutnya.

**Bukti/batas:** Source + smoke halaman; role tests; multi-outlet payment/subscription antaroutlet belum E2E.

## W03 — Onboarding, sample dan impor katalog

1. **Titik awal:** /getting-started.
2. **Tujuan:** Menyiapkan item jualan.
3. **Navigasi:** Tambah contoh / unduh template → upload → katalog → POS.
4. **Input:** CSV/XLSX maksimal 2 MB dan 500 item; tujuh kolom.
5. **Validasi/frontend:** File accept dan checklist kesiapan.
6. **Validasi/backend:** Admin; MIME/reader allowlist, batas worksheet, validasi tiap baris, kuota dengan lock tenant.
7. **Proses database/storage:** categories dan products dalam transaksi.
8. **Perubahan status:** Katalog kosong → berisi; tipe product/service tersimpan.
9. **Hasil UI/response:** Pesan jumlah item/validation per baris.
10. **Error/recovery:** File rusak/header salah/kuota: tidak menulis sebagian.
11. **Kondisi akhir:** Katalog siap dikonfigurasi dan dijual.

**Bukti/batas:** Import atomic/sample tests lolos; parsing file eksternal tidak dijalankan di browser. Dependency advisory F03.

## W04 — Produk, harga, kemasan dan barcode

1. **Titik awal:** /products.
2. **Tujuan:** Membuat/mengubah produk yang dapat dijual.
3. **Navigasi:** Katalog → tambah/edit → simpan → POS; label barcode.
4. **Input:** Kategori, SKU, barcode, nama, harga/modal, stok, unit, tier, gambar.
5. **Validasi/frontend:** Form, formatter rupiah, stock toggle; labels belum semuanya terhubung.
6. **Validasi/backend:** ProductRequest, tenant category/barcode, unique SKU, fractional rule, quota lock.
7. **Proses database/storage:** products, product_units, stock_movements dan image storage.
8. **Perubahan status:** is_active/manage_stock; price tiers dan unit snapshot saat sale.
9. **Hasil UI/response:** Redirect katalog; label SVG barcode.
10. **Error/recovery:** 422/redirect errors; hapus produk berhistori ditolak.
11. **Kondisi akhir:** Produk aktif ada di POS; riwayat tetap tersimpan.

**Bukti/batas:** Tes checkout/retail/management + visual; F13 pagination dan F14 stale stock.

## W05 — Kategori dan supplier

1. **Titik awal:** Katalog / menu persediaan.
2. **Tujuan:** Mengelompokkan produk dan pemasok.
3. **Navigasi:** Kategori/Supplier → tambah → pakai pada produk/PO.
4. **Input:** Nama; supplier dapat punya kontak/alamat.
5. **Validasi/frontend:** Modal/form dengan required; sebagian dialog legacy.
6. **Validasi/backend:** Manager/admin, tenant scope dan validasi string/email.
7. **Proses database/storage:** categories, suppliers; referensi produk/PO/movement.
8. **Perubahan status:** Record dibuat; delete kategori berproduk ditolak.
9. **Hasil UI/response:** Redirect dan pesan.
10. **Error/recovery:** Validation/403; supplier delete mengandalkan aturan FK; tidak semua race delete diuji.
11. **Kondisi akhir:** Master data tersedia untuk operasi.

**Bukti/batas:** Source, smoke, inventory/management tests. Nama supplier menjadi salah satu sumber F02.

## W06 — Varian, addon dan resep

1. **Titik awal:** /menu/configure.
2. **Tujuan:** Mengatur pilihan jual dan konsumsi bahan.
3. **Navigasi:** Cari item → panel varian/addon/resep → simpan.
4. **Input:** Nama, harga, stok varian; biaya addon; bahan dan quantity resep.
5. **Validasi/frontend:** Form per item, required dan formatter.
6. **Validasi/backend:** Manager/admin, validasi kind/ID tenant, lock product; mencegah delete varian berhistori.
7. **Proses database/storage:** product_variants, product_addons, product_material.
8. **Perubahan status:** Pilihan aktif pada produk; stok varian terpisah.
9. **Hasil UI/response:** Kembali dengan feedback; POS memuat pilihan.
10. **Error/recovery:** Invalid variant/addon/recipe ditolak; rollback checkout bila bahan tidak cukup.
11. **Kondisi akhir:** Harga dan konsumsi dihitung server saat penjualan.

**Bukti/batas:** CafeWorkflowTest menguji integrasi; resep memakai unit kecil gram/ml; UI diperiksa.

## W07 — Promo/diskon

1. **Titik awal:** /discounts.
2. **Tujuan:** Menerapkan diskon terjadwal.
3. **Navigasi:** Buat/edit diskon → pilih produk → simpan → checkout.
4. **Input:** Jenis/nilai, produk, tanggal/jam/hari dan aktif.
5. **Validasi/frontend:** Form dan daftar; tidak semua kondisi jadwal diuji browser.
6. **Validasi/backend:** DiscountRequest, tenant product IDs, percentage max100; isValidNow; diskon dipilih deterministik.
7. **Proses database/storage:** discounts dan discount_product dalam transaksi.
8. **Perubahan status:** Aktif dan berlaku sekarang menentukan harga.
9. **Hasil UI/response:** Redirect daftar; POS menampilkan harga hasil.
10. **Error/recovery:** Validasi jadwal/produk asing menolak; invalid amount tidak disimpan.
11. **Kondisi akhir:** Checkout memakai diskon sah dengan snapshot per line.

**Bukti/batas:** Tes discount/checkout lolos; source OrderPricing; jadwal real-time lintas tengah malam belum diuji.

## W08 — Buka dan tutup shift

1. **Titik awal:** Header POS.
2. **Tujuan:** Mencatat kas laci per kasir.
3. **Navigasi:** Buka shift → transaksi → ringkasan → tutup.
4. **Input:** Modal awal, kas aktual, catatan.
5. **Validasi/frontend:** Formatter rupiah, busy state, modal dialog.
6. **Validasi/backend:** Validasi nominal; lock tenant dan shift aktif milik user.
7. **Proses database/storage:** shifts dan session active_shift_id; CashLedger menghitung expected.
8. **Perubahan status:** open → closed; difference disimpan.
9. **Hasil UI/response:** JSON success/current/summary; notice pada POS.
10. **Error/recovery:** Shift ganda ditolak; shift tidak ada 404; request gagal tampil error.
11. **Kondisi akhir:** Satu shift ditutup dengan expected/actual/difference.

**Bukti/batas:** Browser buka shift dan feature tests; real race MySQL belum dijalankan.

## W09 — Keranjang, favorit, draft dan scanner

1. **Titik awal:** POS terminal.
2. **Tujuan:** Menyusun pesanan dengan cepat.
3. **Navigasi:** Cari/filter/scan → pilih opsi → jumlah → keranjang/draft.
4. **Input:** Kode produk, varian/addon/unit, jumlah, customer, note.
5. **Validasi/frontend:** Exact match scanner, fractional quantity, totals, storage availability.
6. **Validasi/backend:** Validasi final dilakukan ulang oleh PosController saat submit.
7. **Proses database/storage:** Cart/favorit/draft di localStorage scoped tenant+user; belum ada sale DB.
8. **Perubahan status:** Draft tersimpan/diambil; belum paid.
9. **Hasil UI/response:** Cart total, error, daftar draft, floating cart di mobile.
10. **Error/recovery:** Kode ambigu/tidak ada, jumlah tidak valid; penyimpanan gagal memblokir submit.
11. **Kondisi akhir:** Keranjang siap bayar atau draft tetap di perangkat.

**Bukti/batas:** 17 JS tests dan browser fractional cart; tanpa service worker full offline reload tidak diklaim.

## W10 — Penjualan tunai

1. **Titik awal:** Keranjang dengan shift aktif.
2. **Tujuan:** Mencatat penjualan dan menerima uang tunai.
3. **Navigasi:** Lanjut pembayaran → uang pas/nominal → konfirmasi → receipt.
4. **Input:** Item, qty, customer opsional, cash; checkout key stabil.
5. **Validasi/frontend:** Uang kurang ditolak; busy; queue disimpan sebelum request.
6. **Validasi/backend:** Harga server, tenant ownership, kuota, shift, stok gabungan/bahan, tax dan total; transaction.
7. **Proses database/storage:** orders, order_items, products/variants/materials, movements, cash_entries, points.
8. **Perubahan status:** paid/completed; cash_tracked true.
9. **Hasil UI/response:** Receipt server dengan invoice dan link print.
10. **Error/recovery:** 422 rollback; network/500 mempertahankan key dalam queue.
11. **Kondisi akhir:** Sale konsisten dengan stok, kas dan customer points.

**Bukti/batas:** Browser 1,5 kg/Rp22.500 sukses; fixture database stock18,5, kas22500; banyak feature/JS tests lolos.

## W11 — QRIS dan rekonsiliasi POS

1. **Titik awal:** Keranjang dengan entitlement QRIS.
2. **Tujuan:** Membayar digital tanpa kredit ganda.
3. **Navigasi:** Konfirmasi QRIS → scan → check status / pending list / review owner.
4. **Input:** Order reference dan total; respons provider.
5. **Validasi/frontend:** Online wajib; QR pending tidak diperlakukan paid; explicit retry digital.
6. **Validasi/backend:** Order commit sebelum charge; signed callback; amount/order validation; OrderPaymentService.
7. **Proses database/storage:** Order pending, reserved stock, wallet credit, points/movements.
8. **Perubahan status:** pending unpaid → paid completed atau cancelled; attention untuk ambiguity.
9. **Hasil UI/response:** QR/receipt atau status/error.
10. **Error/recovery:** Timeout: referensi dipertahankan, stok dilepas, reconcilable; late stock conflict membutuhkan review.
11. **Kondisi akhir:** Provider status direkonsiliasi ke order/wallet.

**Bukti/batas:** Payments/Reconciliation tests memakai mock; tidak ada transaksi gateway nyata; billing SaaS berbeda dan bermasalah F01/F04/F05.

## W12 — Offline queue dan recovery

1. **Titik awal:** POS sudah termuat lalu koneksi terganggu.
2. **Tujuan:** Menyimpan intent tunai dan mengirim ulang aman.
3. **Navigasi:** Konfirmasi → antrean → reconnect / retry / backup JSON.
4. **Input:** Payload immutable dengan checkout key, shift_id, sold_at.
5. **Validasi/frontend:** Persist sebelum kirim; queueOnce; session401/419 arahkan login; QR tidak auto-charge.
6. **Validasi/backend:** Idempotency key/hash; timestamp/shift dan harga/stok divalidasi ulang.
7. **Proses database/storage:** Queue localStorage; order DB hanya setelah diterima.
8. **Perubahan status:** Belum server → tersinkron atau tetap error.
9. **Hasil UI/response:** Antrean menampilkan error dan ekspor backup.
10. **Error/recovery:** Stok/harga/shift berubah dapat 422; tidak ada UI server override yang diverifikasi.
11. **Kondisi akhir:** Order diterima satu kali atau antrean tetap perlu tindakan.

**Bukti/batas:** JS conflict/network tests lolos; replay setelah shift tutup perlu prosedur recovery, belum E2E browser offline sesungguhnya.

## W13 — Riwayat dan cetak struk

1. **Titik awal:** Riwayat POS / orders.
2. **Tujuan:** Meninjau nota dan mencetak.
3. **Navigasi:** Riwayat → detail → print58/80.
4. **Input:** ID order, paper58/80.
5. **Validasi/frontend:** Riwayat cepat dibatasi20; print popup/link.
6. **Validasi/backend:** Tenant scope; kasir hanya nota sendiri untuk show/print; paper allowlist.
7. **Proses database/storage:** Read orders/items/customer/user/returns.
8. **Perubahan status:** Tidak mengubah payment state.
9. **Hasil UI/response:** Halaman detail/receipt printable.
10. **Error/recovery:** 403/404 lintas akses; paper invalid422.
11. **Kondisi akhir:** Nota historis terbaca dan siap printer.

**Bukti/batas:** PrinterLayout/role tests; browser receipt dilihat, hardware fisik tidak diuji.

## W14 — Bon dan cicilan

1. **Titik awal:** Backend checkout bon / detail order.
2. **Tujuan:** Mencatat dan melunasi piutang.
3. **Navigasi:** Order unpaid pelanggan → detail → terima cicilan → lunas.
4. **Input:** customer_id, amount, reason dan operation_key.
5. **Validasi/frontend:** Detail menampilkan form pembayaran; POS submit saat ini selalu paid sehingga pembuatan bon belum menjadi pilihan utama UI.
6. **Validasi/backend:** CashOperationController: cash-only unpaid, remaining, current receiver shift, replay validation.
7. **Proses database/storage:** orders.paid_amount, payments, cash_entries, customer.total_debt, points.
8. **Perubahan status:** unpaid pending → paid completed setelah sisa nol.
9. **Hasil UI/response:** Redirect sukses; ledger mengikuti shift penerima.
10. **Error/recovery:** Overpayment, cancelled/paid, replay beda payload ditolak.
11. **Kondisi akhir:** Piutang berkurang tepat sesuai penerimaan.

**Bukti/batas:** CashOperationsTest lolos; gap discoverability bon perlu keputusan product, bukan mengklaim UI bon tersedia penuh.

## W15 — Cancel, retur dan refund

1. **Titik awal:** Admin di order detail.
2. **Tujuan:** Membatalkan bon atau mengembalikan uang/barang.
3. **Navigasi:** Pilih cancel bon tanpa bayar / retur per line → alasan/qty/restock.
4. **Input:** Operation key, item, qty, restock, alasan.
5. **Validasi/frontend:** Form per line dan konfirmasi; tidak semua kasus UI diuji.
6. **Validasi/backend:** Admin, tenant/order locks, cash-only paid return, snapshot fraction/unit, cumulative entitlement, kas cukup.
7. **Proses database/storage:** order_returns, cash_entries negatif, stock_movements; debt/points saat relevan.
8. **Perubahan status:** Bon → cancelled; retur parsial tidak menulis ulang invoice asli.
9. **Hasil UI/response:** Pesan sukses dan riwayat retur.
10. **Error/recovery:** Qty berlebih/insufficient cash/digital refund ditolak.
11. **Kondisi akhir:** Refund dan stok/points tercatat sekali.

**Bukti/batas:** OrderReturns/CashOperations tests lolos; digital refund bukan fitur yang terverifikasi.

## W16 — Inventory, bahan dan histori

1. **Titik awal:** Inventory / materials.
2. **Tujuan:** Mencatat stock in/out/adjustment dengan jejak.
3. **Navigasi:** Pilih item → update stok → isi alasan → simpan → histori.
4. **Input:** Produk/varian/bahan, qty, jenis, supplier/biaya opsional.
5. **Validasi/frontend:** Form format quantity; history async loading/empty.
6. **Validasi/backend:** Manager/admin; StockAdjustment tenant-first lock, nonnegative/boundary, valid supplier.
7. **Proses database/storage:** Stock sumber yang sama dipakai checkout + StockMovement before/after.
8. **Perubahan status:** Stok berubah, movement immutable secara flow.
9. **Hasil UI/response:** Toast/redirect dan history JSON.
10. **Error/recovery:** Validation dan stock-out melebihi stok ditolak; F02/F16 pada history modal.
11. **Kondisi akhir:** Inventory sesuai ledger mutasi.

**Bukti/batas:** Feature tests + browser history inert marker; biaya manual belum selalu memperbarui weighted average seperti receive PO.

## W17 — Purchase order dan receiving

1. **Titik awal:** /purchases/create.
2. **Tujuan:** Menerima barang dan memperbarui HPP.
3. **Navigasi:** Buat PO → detail → input penerimaan parsial → terima.
4. **Input:** Supplier opsional, SKU, qty/unit_cost, expected date.
5. **Validasi/frontend:** Baris tambah/hapus; label qty/biaya perlu diperjelas.
6. **Validasi/backend:** Manager/admin, tenant product, no injected received_qty, remaining bound dan transaction locks.
7. **Proses database/storage:** purchase_orders/items, products stock/cost weighted average, stock_movements.
8. **Perubahan status:** ordered → partial → received.
9. **Hasil UI/response:** Detail dengan status dan pesan penerimaan.
10. **Error/recovery:** Empty receipt/over-receive/invalid precision ditolak.
11. **Kondisi akhir:** Stok/HPP bertambah sesuai kuantitas diterima.

**Bukti/batas:** Management tests + visual form; varian tidak tersedia sebagai target receiving PO pada implementasi ini.

## W18 — Stock opname

1. **Titik awal:** /stocktakes.
2. **Tujuan:** Menyelaraskan hitung fisik.
3. **Navigasi:** Mulai → isi semua count → complete.
4. **Input:** Count per item, note.
5. **Validasi/frontend:** Form semua item; snapshot sistem ditampilkan.
6. **Validasi/backend:** Tenant dan stocktake lock; whole/fraction rule; complete sekali.
7. **Proses database/storage:** stocktakes/items, product stock dan movement adjustment.
8. **Perubahan status:** open → completed.
9. **Hasil UI/response:** Redirect detail dan pesan.
10. **Error/recovery:** Missing count/complete ulang ditolak; perubahan stok selama hitung belum terlindungi.
11. **Kondisi akhir:** Rekonsiliasi perlu kebijakan F08 sebelum dipercaya.

**Bukti/batas:** Management test stale completion lolos; probe interleaving sale menunjukkan bug F08.

## W19 — Kitchen dan pengerjaan jasa

1. **Titik awal:** POS food/service order.
2. **Tujuan:** Menyelesaikan pemenuhan pesanan.
3. **Navigasi:** Queue → update tahap; jasa tetapkan staf/due date.
4. **Input:** Order, next status; staf dan estimasi jasa.
5. **Validasi/frontend:** Tabs status, empty state dan lateness label.
6. **Validasi/backend:** Tenant scope dan transisi berurutan; tidak mengubah status pembayaran.
7. **Proses database/storage:** orders.kitchen_status/service_status/assigned_user_id/service_due_at.
8. **Perubahan status:** Jasa queued→working→ready→completed; kitchen mengikuti next-state map controller.
9. **Hasil UI/response:** Kembali queue dengan pesan.
10. **Error/recovery:** Foreign order/staff invalid, skipped transition409.
11. **Kondisi akhir:** Pengerjaan selesai dengan pembayaran tetap terpisah.

**Bukti/batas:** Cafe/BusinessProfile/OperationalDetails tests; service empty state diperiksa, real team concurrent UX belum diuji.

## W20 — CRM dan loyalitas

1. **Titik awal:** /customers atau POS customer picker.
2. **Tujuan:** Mencatat pelanggan, poin dan piutang.
3. **Navigasi:** Daftar/tambah → pilih di transaksi → detail pelanggan.
4. **Input:** Nama/phone, is_member; settings aturan poin.
5. **Validasi/frontend:** Modal tambah; customer selection; labels sebagian belum terhubung.
6. **Validasi/backend:** feature-crm, tenant, admin-only delete, prevent debt/history delete.
7. **Proses database/storage:** customers dan snapshot points_awarded pada order.
8. **Perubahan status:** Member/points/debt berubah saat paid/cancel/refund.
9. **Hasil UI/response:** Daftar/JSON customer; detail saat ini500.
10. **Error/recovery:** Required/foreign IDs ditolak; F09 memutus detail.
11. **Kondisi akhir:** Pelanggan terhubung sale; peninjauan detail perlu diperbaiki.

**Bukti/batas:** Customer API/points tests lolos; detail probe gagal sesuai F09.

## W21 — Kas, expense dan wallet withdrawal

1. **Titik awal:** /cash /finance.
2. **Tujuan:** Mengelola arus tunai dan pengajuan saldo QRIS.
3. **Navigasi:** Kas → expense; finance → rekening → withdraw; platform approve/reject.
4. **Input:** Amount, reason/key untuk cash; bank snapshot; amount withdraw.
5. **Validasi/frontend:** Formatter, busy dan alert; wallet/cash dua domain berbeda.
6. **Validasi/backend:** manage-finance/platform-admin terpisah; cash balance check; wallet lock.
7. **Proses database/storage:** cash_entries; tenant_wallets; withdrawal_requests.
8. **Perubahan status:** Withdraw pending→approved/rejected; reject mengembalikan saldo.
9. **Hasil UI/response:** Daftar pengajuan dan pesan.
10. **Error/recovery:** Saldo kurang/account kosong ditolak; F15 replay withdrawal belum aman.
11. **Kondisi akhir:** Jejak kas dan pengajuan saldo tersedia; transfer bank aktual tidak diotomasi pada kode approval.

**Bukti/batas:** Authorization tests dan extended replay probe; tidak melakukan withdraw nyata.

## W22 — Dashboard, report dan export

1. **Titik awal:** /dashboard /reports.
2. **Tujuan:** Memahami penjualan dan kas per periode.
3. **Navigasi:** Pilih tanggal → filter → export → laci unduhan → download.
4. **Input:** start/end date; ID export.
5. **Validasi/frontend:** Date forms/chart/table, export polling.
6. **Validasi/backend:** Manager/admin; paid completed, date validation, private tenant/user download.
7. **Proses database/storage:** orders/items/cash_entries; report_exports + Excel file private storage.
8. **Perubahan status:** pending→processing→completed/failed.
9. **Hasil UI/response:** Ringkasan/Excel; histori sales sebelum retur diberi label.
10. **Error/recovery:** File belum siap/not found, failed job status.
11. **Kondisi akhir:** Laporan periodik dapat diunduh.

**Bukti/batas:** ReportAccuracy/Isolation tests; browser report dilihat; judul AI export F25, N/A load besar.

## W23 — AI analysis/chat/recommendation

1. **Titik awal:** Scale back-office.
2. **Tujuan:** Mendapat insight berbasis data.
3. **Navigasi:** AI analysis → chat / follow-up.
4. **Input:** Data agregat dan pesan; external API key server.
5. **Validasi/frontend:** Chat text, suggestions, loading/fallback.
6. **Validasi/backend:** feature-ai + access-backoffice, chat tenant daily limit; provider timeout/fallback.
7. **Proses database/storage:** Cache per tenant dan agregat read-only; tidak menulis sale.
8. **Perubahan status:** Cached report atau fallback lokal.
9. **Hasil UI/response:** HTML report/chat JSON/rekomendasi.
10. **Error/recovery:** Provider429/error fallback; HTML sanitizer belum ada F11, dataset F10.
11. **Kondisi akhir:** Insight perlu konsisten dan aman sebelum dinyatakan akurat.

**Bukti/batas:** Source review; provider nyata dan MySQL DAYNAME belum diuji, tidak menilai kualitas respons model.

## W24 — Staf, profil dan konfigurasi

1. **Titik awal:** Owner employee/settings/profile.
2. **Tujuan:** Mengatur akses dan aturan bisnis.
3. **Navigasi:** Tambah staf → verifikasi → login; settings pajak/poin; profile edit.
4. **Input:** Role/email/password; tax/point settings; profile/password.
5. **Validasi/frontend:** Form dan modal; sebagian missing labels; tidak mengubah credential lewat browser audit.
6. **Validasi/backend:** Admin/staff bounds, current_password untuk deletion; allowlist setting keys.
7. **Proses database/storage:** users, settings, notifications; image bila relevan.
8. **Perubahan status:** Role/status/verification, settings baru untuk sale berikutnya.
9. **Hasil UI/response:** Pesan atau redirect.
10. **Error/recovery:** Invalid tax/foreign access ditolak; kuota staf count-then-create masih risiko concurrency.
11. **Kondisi akhir:** Aturan tersimpan dan izin mengikuti role; F06 status inactive perlu enforcement.

**Bukti/batas:** Auth/InventorySafety/ManagerAccess tests + UI settings/employees/profile; email nyata belum diuji.

## W25 — Subscription, upgrade dan renewal

1. **Titik awal:** /billing.
2. **Tujuan:** Mendapat layanan sesuai pembayaran.
3. **Navigasi:** Pilih plan → invoice Snap → callback/poll → subscription.
4. **Input:** plan_id; invoice reference/provider response.
5. **Validasi/frontend:** Plan cards/modal/status polling.
6. **Validasi/backend:** Admin; tenant invoice ownership; eligibility dan idempotency belum konsisten.
7. **Proses database/storage:** invoices dan subscriptions serta tenant.status.
8. **Perubahan status:** pending invoice→paid; active subscription seharusnya terbuat/extended.
9. **Hasil UI/response:** Invoice/status/redirect dashboard.
10. **Error/recovery:** Provider error dilog; F01 paid tanpa aktivasi; pending invoice reuse juga salah relasi.
11. **Kondisi akhir:** Belum reliable sampai F01/F04/F05/F07 ditutup.

**Bukti/batas:** Probe khusus; sandbox/live provider tidak dipanggil.

## W26 — Scheduler, worker, backup dan deployment

1. **Titik awal:** Server ops.
2. **Tujuan:** Menjaga recovery dan operasi rutin.
3. **Navigasi:** Deploy → install/build/migrate/optimize → worker restart; schedule heartbeat/reconcile/backup.
4. **Input:** Konfigurasi environment dan trusted backup file.
5. **Validasi/frontend:** Tidak ada UI pengelolaan ops yang terverifikasi.
6. **Validasi/backend:** Command guards untuk restore/konkurensi, checksum/path checks; backup permissions.
7. **Proses database/storage:** jobs/failed_jobs, operational_heartbeats, private backup/exports.
8. **Perubahan status:** Heartbeat baru; backup success/fail; deployment in-place.
9. **Hasil UI/response:** Launch-check PASS/FAIL dan command output.
10. **Error/recovery:** Tidak mengakui backup sukses saat dump gagal; rollback release/offsite belum terverifikasi.
11. **Kondisi akhir:** Perlu staging gates, restore proof dan monitoring sebelum go-live.

**Bukti/batas:** Source only untuk infra; tidak menjalankan restore/drop/create database MySQL atau deploy.

