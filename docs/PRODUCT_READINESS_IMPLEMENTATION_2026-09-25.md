# GrowPOS product-readiness implementation — 25 Sep 2026

This release builds on the launch-ready RC and adds two missing inventory workflows without weakening existing POS authorization.

## Implemented in this pass
- Purchase Order → partial/full receiving → stock movement audit trail.
- Weighted-average product cost update on receiving, so future order-item cost snapshots can support gross-profit reporting.
- Stock opname sessions with immutable starting system quantity, physical count, reconciliation and stock-movement trail.
- Manager/admin-only procurement and stocktake routes; cashier remains POS-only.
- POS keyboard shortcuts: F2 payment, F6 saved orders, F8 transaction history; Escape remains modal close.
- Existing role isolation, POS-only cashier workspace, service/kitchen cashier shell, tenant scoping and regression protections from the RC are retained.

## Deliberately not faked as “done”
The following require a separate data migration/product rollout or real external hardware/provider verification and therefore must not be claimed production-verified from source code alone:
- Full multi-outlet conversion of every transactional table and report (tenant is not an outlet).
- Shared-terminal cashier PIN switching and terminal/device registration.
- True offline-first PWA synchronization across browser restarts/devices.
- Physical 58/80mm printer, cash drawer, label printer and scanner certification.
- Live Midtrans/QRIS production settlement/refund/webhook verification.
- WhatsApp/SMS delivery integrations.

These are post-RC capabilities, not safe one-file UI additions. Launch the first pilot as a single-outlet online-first POS unless each item is completed and verified.

## Required validation on the developer Mac
```bash
composer install
npm ci
php artisan optimize:clear
php artisan migrate
php artisan test
npm test
npm run build
php artisan view:cache
php artisan growpos:launch-check
```

Then manually verify: purchase partial receive; duplicate receive limits; stocktake whole/fraction rules; cashier forbidden from procurement URLs; manager allowed; cross-tenant IDs return 404/403; sale after receiving snapshots the new cost; refund/shift/report totals remain correct.
