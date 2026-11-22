# 04 — Feature relationship matrix

Status: **Terhubung** = relasi terlihat pada route/service/model yang ditinjau; **Perlu validasi** = komponen ada tetapi invariants/runtime belum diuji; **Gap potensial** = ekspektasi/copy melampaui relasi yang ditemukan. Status tidak menyatakan seluruh edge case benar.

| Rantai kerja | Bukti implementasi | Status | Risiko / pertanyaan untuk acceptance |
|---|---|---|---|
| Product → variant/add-on → price → cart/checkout | product/category/menu routes; `RetailCatalog`, `OrderPricing`, POS | Terhubung; perlu validasi | Harga varian/addon, pembulatan diskon+pajak, harga berubah saat cart open; cost coverage. |
| Sale → payment → order status → receipt/settlement | `OrderPaymentService`, order/payment routes/views, Midtrans/manual/cash | Terhubung; perlu validasi | Duplicate submit/callback, partial payment, status pending/expired, retry dan bukti bayar. |
| Sale → stock decrement → movement history | `OrderStock`, inventory movement, order flow | Terhubung; perlu validasi | Concurrent stock, void/return, negative stock policy, multi-item rollback transaction. |
| Sale → cash ledger → shift expected cash → reports | `CashLedger`, `CashPeriodSummary`, shift/cash/report routes | Terhubung; definisi silang perlu validasi | Cash vs noncash, cash refund, cash in/out, closed shift adjustment, report timezone. |
| Return → refund → stock restoration → sales report | `OrderReturnService`, return routes, order/stock services | Terhubung sebagian; perlu validasi | Refund channel/provider, partial item return, reused item condition, cash ledger and tax reversal. |
| Customer → order → bon/receivable → settlement | customer/order payment features, customer and payment routes | Terhubung; perlu validasi | Settlement idempotent, overdue balance, customer merge/deletion, cashier access. |
| Customer → points/stamps → reward/discount | `CustomerPoints`, `CustomerStamps`, `LoyaltyProgram`, settings | Terhubung; perlu validasi | Earn/reverse on return, expiry, reward stacking, public card link revocation. |
| Supplier → purchase order → receive → inventory/cost | purchase/supplier/material routes and views; inventory movements | Terhubung; partial receive perlu dibuktikan | Duplicate receive, partial/over-receive, cost allocation and stock unit conversions. |
| Stocktake → counted quantity → adjustment → audit trail | stocktake routes/services, `StockAdjustment`, activity logs | Terhubung; perlu validasi | Race with sales/receiving, adjustment reason, approval, rollback. |
| Food order → recipe/material deduction → kitchen queue → ready | business module gates; `RecipeStock`, kitchen routes/views | Terhubung secara modul; status runtime perlu validasi | Void after prep, partial recipe shortage, queue ordering and sync with POS. |
| Service order → job queue → completion/payment | services module routes/views; order/payment services | Terhubung secara modul; lifecycle perlu validasi | Status transitions, deposit/partial payment, materials and cancellation. |
| Shift → cashier sales → close/reconciliation → owner report | shift controllers, cash ledger/summary, report views | Terhubung; perlu validasi | Late payment/refund after close; who can adjust; actual vs expected definition. |
| Tenant switch → scoped catalog/orders/reports/exports | tenant switch route, tenant-bound controllers; export job captures tenant | Terhubung untuk switch/scope; consolidated parent view tidak ditemukan | Distinguish separate businesses vs outlets; jobs must remain on requested tenant. |
| Subscription → payment callback/reconciliation → feature entitlement | billing services, subscription/gateway routes, `ReconcileBilling` | Terhubung; perlu validasi | Signature, replay, renewal/downgrade, grace period, plan update and concurrent callback. |
| Report → CSV/export → queued download | `SalesDataset`, report export routes and `ExportReportJob` | Terhubung; perlu validasi | Same filters/timezone/tenant as screen; failed/expired job and permissions. |
| Report → AI insight → supporting metrics | `GeminiService`, report AI view/routes | Terhubung; provenance UX perlu validasi | Source/period visibility, model uncertainty, prompt injection/data minimization, unavailable AI. |
| Backup → restore → tenant recovery | `BackupArchive`, health/support docs | Backup feature referenced; successful restore not evidenced | Restore rehearsal, checksum, encryption, tenant isolation, RPO/RTO. |
| Commit → deploy → health check → rollback | `deploy.sh`, runbook | **Gap: no atomic release/rollback** | Script changes active checkout in place; rollback playbook must include migration compatibility. |
| Multi-outlet plan → switching tenants → consolidated management | `max_outlets`, `TenantController`, copy says outlet/cabang | **Gap potensial** | No parent aggregation/shared operations found; decide semantics before promising feature parity. |

## Disconnected or inconsistent by inspection

1. **Role and task navigation:** domain routes are available under shared owner nav; users’ work models are not one-to-one with menu parents. This is an information scent/IA disconnection, see NAV-01.
2. **Dashboard and terminal entry:** same “Buka Kasir” action appears in shell and dashboard. One action has two top-level visual anchors.
3. **Multi-outlet expectation:** plan copy advertises outlet counts while app flow manages tenant switching; aggregate reporting/shared catalogs were not located in the reviewed route/model set.
4. **Deployment claims:** remediation history says atomic release resolved, but current deploy and runbook document in-place pull. Current script/runbook is operational source of truth until changed and re-verified.

## Relationship principles for redesign

- A transaction is the anchor record; payment, stock movement, cash, loyalty, and report effects should be traceable from it.
- Each downstream side effect needs an idempotency/reversal rule and audit history.
- Reports should share one canonical dataset or explicitly disclose why totals differ.
- Tenant context should be visible and immutable for an in-flight payment/export; cross-outlet aggregation requires a distinct parent business scope.

