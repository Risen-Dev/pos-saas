# 11 — Acceptance criteria dan test plan

**Ini rencana, bukan hasil eksekusi.** Audit ini tidak menjalankan suite atau transaksi test. Artefak QA lama di docs/SIMPLIFICATION_2026-11-01.md adalah catatan historis. Pakai MySQL staging untuk lock/concurrency, gateway sandbox/fakes dan role fixture terisolasi.

## Product flows

| ID | Skenario dan kriteria lulus |
|---|---|
| AC-ONB-01 | Owner membuat tenant dan memilih jenis usaha, melihat prasyarat tersisa, melewati langkah opsional, dan mendapat CTA selanjutnya. Checklist berasal dari state backend. |
| AC-ONB-02 | Verifikasi expired/duplicate/invalid menunjukkan recovery tanpa kehilangan input valid. |
| AC-POS-01 | Kasir scan/search, pilih variant/add-on, ubah qty/catatan, pelanggan/promo opsional, lihat total final dan menerima pembayaran; search focus sesuai alur. |
| AC-POS-02 | Double-click, reload setelah timeout dan callback replay tidak membuat order/charge/ledger/movement kedua. UI membedakan pending/success/failure. |
| AC-POS-03 | Produk habis/harga berubah saat cart terbuka dikonfirmasi sebelum bayar; total server dan UI sama. |
| AC-POS-04 | Cash change, transfer/manual QRIS, provider, bon, partial pay dan settlement menunjukkan status/saldo/referensi benar; noncash tak masuk expected cash. |
| AC-POS-05 | Offline queue memberi ID/status per transaksi; retry mempertahankan idempotency; cart tidak hilang sebelum server confirm; konflik bisa direview. |
| AC-RET-01 | Retur penuh/sebagian dibatasi item/qty sisa; refund dan keputusan stok sesuai kebijakan; double submit/callback tidak gandakan refund. Ledger, detail, movement, report cocok. |
| AC-STK-01 | Receive sebagian menambah qty aktual; PO remainder terbuka; duplicate receive aman; supplier/cost/movement terlacak. |
| AC-STK-02 | Stocktake bersamaan penjualan menampilkan cutoff/delta; adjustment beralasan dan actor/value tercatat; failed post tidak membuat partial movement. |
| AC-SHF-01 | Open/close shift dengan cash/noncash, in/out, void/refund dan discrepancy menghasilkan expected cash sesuai definisi; retry aman; alasan dapat diaudit. |
| AC-CRM-01 | Customer/bon ter-scope; poin/stamp dibalik saat return sesuai aturan; public link dapat dicabut bila kebijakan ditetapkan. |
| AC-RPT-01 | Dashboard/report/CSV/AI memakai tenant, periode/timezone, refund dan definisi total eksplisit; profit menunjukkan cost coverage; export cocok layar. |
| AC-TEN-01 | Route/detail/export/job menolak tenant lain; switch tidak mengubah context job yang dibuat; roll-up hanya jika parent scope disepakati. |

## Navigation dan usability

- Role matrix owner, kasir, platform admin dan pegawai terbatas; nav hanya menampilkan izin dan direct URL tetap ditolak server-side.
- Task-finding: transaksi kemarin, pelanggan dengan bon, tutup shift, stocktake, terima purchase sebagian, pajak, tambah staff, lihat sales/margin. Catat success tanpa bantuan, first-click, waktu, dan salah tafsir label.
- Link target tepat saja yang mendapat aria-current=page; parent bukan current page untuk semua descendant.
- Back navigation menjaga query/filter/pagination/tenant.
- Satu primary Buka Kasir per viewport.

## Accessibility dan interaction

- Semua fungsi keyboard; tab order logis; focus terlihat; modal mengelola dan mengembalikan focus.
- Label terhubung; error terkait ke field dan diumumkan; summary error dapat difokuskan.
- Status bukan warna saja; contrast dan focus diuji pada default/hover/focus/disabled/error.
- Touch controls penting 44–48 CSS px sesuai konteks; zoom 200% usable; reduced motion dihormati.
- Screen reader mengumumkan cart, payment pending/success/error, stockout, saving dan shift status tanpa pengulangan.

## Viewport dan content

Render jual, transaksi, produk, stok, purchase, shift, pelanggan, reports dan settings pada 320, 360, 390, 768, 1024, 1440 CSS px. Gunakan nama/SKU panjang, angka Rupiah besar, empty/full/error data, Bahasa Indonesia panjang, keyboard virtual dan zoom. Tidak ada label/action kunci terpotong; page horizontal scroll hanya bila tabel memang memerlukannya dan affordance terlihat.

## Technical suites

1. PHP unit/integration: money arithmetic, pricing, loyalty reversal, cash summary, stock, refund, tenant scoping.
2. Laravel feature: route authorization dan tenant boundary tiap role/resource.
3. MySQL concurrency: lock item terakhir, double order, receiving vs sale, return vs close.
4. JS tests: cart, offline queue, form state, focus/navigation.
5. Browser E2E: fake/sandbox payment, print/share, responsive/error states.
6. Security/operations: invalid/replayed webhook, import validation, public link lifecycle, export authorization, backup restore, deploy rollback.
7. Dependency audit di CI/network-enabled environment; catat tanggal/tool version/allowlist.

## Release gates

- Tidak ada known P0 invariant failure pada order/payment/tenant/stock.
- Semua P1 selesai atau accepted mitigation punya owner.
- Staging restore/deploy rollback rehearsal sukses dan runbook sesuai deployment.
- Task-based navigation test memiliki target yang ditetapkan sebelum uji dan baseline.
- Screenshot build candidate dicatat dengan viewport, browser, commit dan fixture; screenshot 1 Nov bukan bukti kondisi baru.

