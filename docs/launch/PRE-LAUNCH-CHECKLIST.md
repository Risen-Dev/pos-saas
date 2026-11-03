# GrowPOS pre-launch checklist

Status: working checklist. Items marked **Belum diverifikasi** need an owner and evidence before opening paid registration widely.

## Done in this preparation

- [x] Reworked the report HPP query to join `order_items` with filtered orders directly. This removes the problematic `WHERE IN (subquery)` pattern from the error previously reported on `/reports`.
- [x] Removed the personal email fallback from the support contact setting. The production launch check now requires a valid `GROWPOS_SUPPORT_EMAIL`.
- [x] Added internal privacy and service-terms drafts. They are not final customer-facing legal documents; fill every marked field and have the operator/legal reviewer approve them before publishing.

## Before a supervised pilot

- [ ] Deploy to a staging or pilot environment and confirm `/reports` for empty data, normal sales, and older transactions with missing `unit_cost`.
- [ ] Walk through registration, business setup, cashier shift, cash sale, receipt, stock update, refund/return, and subscription purchase with a pilot account.
- [ ] Confirm tenant isolation by checking that one tenant cannot access another tenant's customers, orders, reports, exports, and loyalty cards.
- [ ] Set a real support inbox in `GROWPOS_SUPPORT_EMAIL`; publish response hours and an escalation contact.
- [ ] Replace all `[LENGKAPI]` fields in the privacy and service-terms drafts. Review how GrowPOS and each merchant divide controller/processor responsibilities.
- [ ] Confirm email verification, password reset, subscription notifications, and receipt/support emails arrive at real inboxes.
- [ ] Confirm printer and QRIS/payment flows on the actual target devices and provider account.

## Before taking public payments

- [ ] Run `php artisan growpos:launch-check` on the production server and resolve every `FAIL`.
- [ ] Verify Midtrans production credentials and a complete real-value payment/refund/reconciliation path. Keep a small controlled amount and reconcile provider settlement with GrowPOS records.
- [ ] Configure an independent offsite backup disk, verify a fresh backup exists, then perform and document a restore rehearsal on an isolated database.
- [ ] Verify scheduler, queue worker, failed-job alerting, error monitoring, TLS, secure cookies, database access controls, and server patch ownership.
- [ ] Review data retention and deletion requests, incident/breach response contacts, and third-party processors (hosting, email, payment, object storage, and Gemini API).
- [ ] Confirm business identity, NIB/KBLI and applicable PSE registration with the responsible operator. The regulations identify private electronic system providers in relevant categories and require registration before user operation; verify the current procedure and applicability with the official portal/adviser.
- [ ] Publish approved privacy policy, service terms, pricing, cancellation/refund rules, support contact, and a plain-language data notice before checkout.
- [ ] Review deploy and rollback procedure. Take a pre-deploy backup and verify post-deploy health before declaring a release complete.

## Suggested release sequence

1. Internal walkthrough with demo data.
2. Supervised pilot with a small number of UMKM and direct support.
3. Fix pilot blockers and review operational evidence.
4. Open paid registration gradually; monitor incidents, payments, queue health, and backup status daily at first.

## Evidence log

| Date | Check | Environment | Result / evidence | Owner |
|---|---|---|---|---|
| [LENGKAPI] | `/reports` error resolution | [LENGKAPI] | [LENGKAPI] | [LENGKAPI] |
| [LENGKAPI] | Payment and refund flow | [LENGKAPI] | [LENGKAPI] | [LENGKAPI] |
| [LENGKAPI] | Backup restore rehearsal | [LENGKAPI] | [LENGKAPI] | [LENGKAPI] |
| [LENGKAPI] | Production launch check | [LENGKAPI] | [LENGKAPI] | [LENGKAPI] |

## Official references to re-check before launch

- PSE Privat portal and FAQ: <https://pse.komdigi.go.id/pertanyaan-umum>
- Permen Kominfo No. 5 Tahun 2020, JDIH Komdigi: <https://jdih.komdigi.go.id/produk_hukum/view/id/759/t/peraturan%2Bmenteri%2Bkomunikasi%2Bdan%2Binformatika%2Bnomor%2B5%2Btahun%2B2020>
- UU No. 27 Tahun 2022, BPK: <https://peraturan.bpk.go.id/Details/229798/uu-no-27->
