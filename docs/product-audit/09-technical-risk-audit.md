# 09 — Audit risiko teknis dan business logic

**Batas:** source/docs static review. Tidak ada penetration test, production access, live gateway, concurrency run, database restore, atau credential inspection. Tidak terlihat bukan bukti tidak ada kontrol.

## Ringkasan risiko

| ID | Area | Severity / prioritas | Confidence | Temuan dan dampak |
|---|---|---|---|---|
| TECH-01 | Deployment/recovery | High / P1 | Observed (script/runbook) | deploy.sh menjalankan git pull dan build/migrate pada checkout aktif. Tidak membuat release directory atau rollback symlink. Kegagalan dapat meninggalkan campuran kode/asset/schema. Runbook menyebut in-place checkout. |
| TECH-02 | Payment callback/idempotency | Blocker bila cacat terbukti / P0 gate | Inferred; belum diuji | Payment/reconciliation services ada; belum dibuktikan bahwa signature verification, replay protection, dan duplicate callback aman pada semua route. |
| TECH-03 | Checkout dan stock atomicity | Blocker bila invariant gagal / P0 gate | Inferred | OrderStock, OrderPaymentService, pricing/quantity service ditemukan. Race pada checkout item terakhir, offline retry dan callback perlu dibuktikan di MySQL; SQLite tidak mewakili lock MySQL. |
| TECH-04 | Tenant isolation/report jobs | Blocker bila lintas tenant / P0 gate | Source review parsial | Tenant switch dan scoping ditemukan; export job menyimpan tenant saat request. Uji detail, nested relation, download, AI context, cache, search, dan async job dengan dua tenant. |
| TECH-05 | Refund/cash/stock reconciliation | High / P1 | Inferred | Return, ledger dan stock flows ada; refund otomatis noncash/provider dan reversal rules belum dibuktikan. |
| TECH-06 | Multi-outlet semantics | High / P1 | Observed semantics; gap inferred | max_outlets membatasi owned tenants; route mengubah tenant aktif. Roll-up parent tidak ditemukan; copy “outlet/cabang” berisiko menciptakan ekspektasi salah. |
| TECH-07 | Dependency/security posture terkini | Medium / P1 gate | Tidak terverifikasi | Composer/npm audit gagal karena registry DNS/network. Klaim nol advisories pada remediasi Oktober bersifat historis. |
| TECH-08 | Backup/restore | High / P1 | Inferred | BackupArchive dan prosedur ada, tetapi restore drill, checksum/encryption, RTO/RPO dan isolasi tenant belum dibuktikan. |
| TECH-09 | Money precision/rounding | High / P1 | Inferred | Pricing/cash/payment services ada; perlu test rounding, diskon/pajak, fractional quantity, nilai besar, retur dan format ekspor. |
| TECH-10 | Audit trail dan akses | High / P1 | Source partial | Activity log dan gates ada. Uji sensitive mutation (price, role, refund, cash adjust, tenant change) serta before/after dan pembatasan role. |
| TECH-11 | Public loyalty link | Medium / P2 | Observed code; policy gap inferred | Card URL memakai random bearer token, no-store/no-referrer; halaman menunjukkan nama, poin/stamp dan aktivitas terakhir. Perlu expiry/revoke/consent dan minimisasi data. Bukan klaim token bocor. |
| TECH-12 | Query/performance scale | Medium / P2 | Belum diverifikasi | Reports, lists, exports dan tenant query perlu EXPLAIN/p95 pada dataset representatif. Tidak ada staging DB/perf trace. |

## Isu deploy yang terverifikasi

deploy.sh berpindah ke /var/www/pos-saas, menarik branch pada direktori aktif, menjalankan Composer install, npm build, optimize:clear, migrate, optimize, lalu restart queue. Tidak ada langkah build calon release di direktori terpisah, asset release versioning, symlink switch, health check pascadeploy, atau rollback. docs/OPERATIONS_RUNBOOK.md juga menggambarkan in-place checkout. Ini tidak sesuai dengan klaim docs/REMEDIATION_2026-10-26.md bahwa atomic release sudah resolved. Koreksi status F20 menjadi open sampai strategi rilis/rollback benar-benar diterapkan atau dokumentasi diperbaiki.

## Bukti historis dan batas verifikasi

- docs/SIMPLIFICATION_2026-11-01.md merekam 131/131 PHP, 22/22 JS, 5/5 browser scenarios dan beberapa viewport pada saat itu. Tidak dijalankan ulang dan tidak menguji semua invariant produksi.
- docs/REMEDIATION_2026-10-26.md merekam jumlah test lebih lama dan F01–F26 resolved; F20 perlu dikoreksi berdasarkan file aktif.
- Database sementara bukan bukti MySQL correctness. Laravel server tidak dapat bind karena Operation not permitted.
- Composer/npm audit saat ini berhenti karena DNS/registry unavailable; status dependency terkini tidak dapat dipastikan.

## Pengujian teknis prioritas

1. Tenant isolation: dua tenant, route langsung, exports/download, queued report/AI, cache/search, role owner/kasir.
2. Payment idempotency: signature valid/invalid, replay/sequence race, timeout setelah capture, review manual, partial pay.
3. Stock invariants: checkout bersamaan item terakhir, receive + checkout, return dua kali, offline reconciliation.
4. Cash/shift: cash/noncash, refund sebelum/sesudah close, cash in/out, timezone boundary.
5. Deploy/restore: gagal sebelum switch, health gagal sesudah switch, rollback dengan migration kompatibel, restore totals/permissions.
6. Dependency refresh: audit lockfile di CI dengan network; catat tanggal, versi alat, allowlist dan owner.

Gunakan tenant data terisolasi dan payment sandbox; jangan membuat transaksi finansial produksi.

