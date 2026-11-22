# 10 — Roadmap perbaikan yang diprioritaskan

Prioritas adalah urutan risiko/hasil, bukan persetujuan implementasi. Estimasi relatif: S <2 hari, M 2–5 hari, L 1–3 minggu, XL >3 minggu. Tidak ada perubahan produk selama audit.

| Urutan / prioritas | Inisiatif | Impact / effort / risk | Dependensi | Acceptance outcome |
|---|---|---|---|---|
| 0 · P0 gate | Buktikan invariants payment, tenant, stok dan order di MySQL/provider sandbox | H / L / H | Isolated staging | Retry/callback menghasilkan satu order; uang/stok konsisten; tidak ada akses tenant silang. |
| 1 · P1 | Perbaiki deploy/recovery dan status remediasi F20 | H / M–L / M | Infra, migration policy | Release gagal tidak merusak versi aktif; rollback diuji; runbook sesuai perilaku. |
| 2 · P1 | Putuskan tenant vs outlet contract | H / M keputusan + L engineering / M | Product/ops | Copy/plan/data scope sesuai; user tahu apakah data outlet digabung. |
| 3 · P1 | IA role/task-aware (NAV-01) | H / M–L / M | Usability tasks, permission map | Owner/kasir menemukan tugas inti; nav hanya menampilkan area berizin; subnav terkendali. |
| 4 · P1 | Mobile workflow stok/laporan/checkout | H / M / M | IA labels, device matrix | Nav tak wrap berlapis; data/action terlihat pada 320/360/390/768 px. |
| 5 · P1 | Rekonsiliasi retur/payment/shift/report | H / L / H | P0, definisi finance | Gross/net/cash/refund/profit/receivable sama dan terdokumentasi di POS, ledger, report, export. |
| 6 · P2 | Pisahkan Pengaturan, onboarding dan platform console | M / M / M | IA decision + role scope | Checklist kontekstual; settings fokus konfigurasi; platform admin terpisah. |
| 7 · P2 | Hapus CTA kasir duplikat dan atur hierarchy dashboard | M / S / L | Usage data optional | Satu primary Buka Kasir per viewport; dashboard menonjolkan insight/tindakan. |
| 8 · P2 | Accessibility, form dan error state pass | M / M / M | Component inventory | Keyboard, focus, labels, errors, contrast/target dan recovery memenuhi acceptance. |
| 9 · P2 | Restore, dependency dan query health gates | H / M / M | CI/network, staging MySQL | Restore drill, advisories, EXPLAIN/perf budgets direkam. |
| 10 · P3 | Visual polish/microinteraction | L / S–M / L | Design tokens, UX baseline | Motion/feedback konsisten; tidak mendahului task-flow issues. |

## Tahapan

**A — Evidence gates:** Jalankan test teknis P0 di staging, verifikasi restore, review security. Jika invariant gagal, perbaiki sebelum UX release.

**B — Keputusan produk:** Tetapkan kontrak multi-outlet dan permission map; validasi sitemap dengan owner/kasir.

**C — Prototype/implementasi:** Kerjakan nav dan POS bertahap; cashier dahulu untuk high-frequency checkout, lalu mobile inventory/owner. Pertahankan compatibility bila perubahan besar.

**D — Release:** Accessibility/device acceptance, baseline waktu/error/cancel, deploy rollback rehearsal, staged rollout dan monitor.

## KPI keberhasilan yang disarankan

- Median/p90 waktu item pertama ke checkout; abandonment dan retry per metode bayar.
- Duplicate order/charge 0 pada suite dan staging; inventory/return mismatch trend.
- Task success, time-to-find, dan first-click accuracy pada 8 tugas per role.
- Coverage cost yang terlihat untuk profit; report vs export reconciliation.
- Onboarding completion hingga sale pertama dan time-to-first-value.
- Shift variance rate dikelompokkan untuk mencari root cause, bukan menghukum kasir.

