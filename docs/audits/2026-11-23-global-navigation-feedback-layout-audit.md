# GrowPOS navigation, feedback, and form-layout audit

## Scope and evidence

This source audit covers authenticated owner/admin/cashier navigation, success and error feedback, and spacing patterns in operational and account forms. Evidence was gathered from the supplied admin `/orders` screenshot and current Blade, JavaScript, route, and controller source. The source confirms what is wired; actual mobile rendering, live login/logout, and screen-reader behavior were not exercised.

## Findings

### NAV-01 — Sidebar destinations are duplicated in a second page-level navigation row

- **Dimension:** Information architecture & navigation
- **Severity:** Medium
- **Confidence:** Observed (screenshot and source)
- **Location:** `resources/views/layouts/sidebar.blade.php:8-32`; `resources/views/components/workspace-tabs.blade.php:5-9`
- **Evidence:** The screenshot shows Transactions, Customers, Promo, and Shift in the sidebar while the transactions row repeats Riwayat, Pelanggan & bon, Promo, and Sesi kasir. Catalog, stock, and reports also use a second navigation row.
- **Why it matters:** Users encounter repeated routes and two navigation layers for one task area; page-level tabs also consume content height.
- **Recommendation:** Establish the approved sidebar tree as the single navigation system. Keep subfeatures in expandable groups within the sidebar and remove the duplicate workspace-tab row.
- **Effort:** M

### MSG-01 — Success and failure notifications are inconsistent, and login/logout have no confirmation

- **Dimension:** System status & feedback
- **Severity:** Medium
- **Confidence:** Observed (source)
- **Location:** `resources/views/layouts/app.blade.php:96-145`; `resources/views/auth/login.blade.php:9`; `app/Http/Controllers/Auth/AuthenticatedSessionController.php:24-47`; `resources/views/finance/index.blade.php:367-420`
- **Evidence:** The app shell renders only `success`, `error`, and validation messages, while status/warning notices are duplicated in page templates such as `employees/index`, `settings/index`, `tenants/index`, and `profile` partials. Finance and billing flows use native `alert()`. Login redirects without a success flash; logout redirects to `/` without a flash.
- **Why it matters:** The same action can produce a toast, an inline banner, or a blocking browser dialog. Edit/delete and authentication outcomes are not consistently confirmed, and duplicate page banners can repeat the same message.
- **Recommendation:** Use one shared toast stack in the app, guest/auth, and landing shells. Support success, error, warning, and status messages with an explicit close button, auto-dismiss, pause on hover/focus, and screen-reader roles. Add login/logout flashes and route existing session notices and asynchronous feedback through the same stack. Keep field-level validation errors persistent beside the form.
- **Effort:** M

### LAY-01 — Form and section spacing use several unrelated scales

- **Dimension:** Visual hierarchy & layout; design-system consistency
- **Severity:** Low
- **Confidence:** Observed (source), visual consequence inferred
- **Location:** Operational Blade views under `resources/views/`
- **Evidence:** Page wrappers mix `p-7`, `p-6`, `px-4 py-6`, `py-8`, and `py-12`; page modules also vary between `space-y-5`, `space-y-6`, and `space-y-8`. The shared page-header card adds its own bottom margin, while some page roots already use `space-y-6`.
- **Why it matters:** The same task type gets different density, and a page-header margin can compound with a parent stack gap, leaving more blank space than intended.
- **Recommendation:** Define a compact shared spacing rhythm for app page gutters, header-to-content distance, form cards, field groups, and action rows. Reduce only the redundant/oversized gaps; retain intentional empty-state breathing room and POS workspace density.
- **Effort:** M

## Existing strengths

- The shell already has lightweight CSS view transitions and respects reduced-motion preferences.
- Main forms commonly show field-level errors, and the authenticated shell has a persistent validation summary.
- Existing session flash messages cover many create, update, and delete actions; a shared renderer can make those consistent without adding controller-specific UI.

## Not audited

- Runtime screenshots or keyboard/screen-reader interaction across roles and viewport sizes.
- Whether every controller action that mutates data currently returns a success/error flash; this pass found common CRUD paths and visible notification gaps, but did not inspect every controller method end to end.
- Whether any third-party payment/provider result should use a persistent dialog instead of a transient toast; payment confirmation copy must preserve current safety guidance.

## Implemented changes and verification

- The duplicate workspace tab row was removed; the main sidebar now exposes grouped operational links, including stock movements, and uses collapsible Katalog/Persediaan submenus. Cashier navigation remains role-specific.
- A shared transient toast stack now handles session success/error/warning/info/status across app, guest, landing, and POS shells. Login/logout receive confirmation; native browser alerts were removed from the finance/payment flows. Toasts include close buttons, timed dismissal, hover/focus pause, and polite/error live-region roles.
- Shared page headers now use a tighter card rhythm. Selected page/form wrappers with compounded or oversized gaps were reduced, while payment recovery guidance, field errors, empty states, and POS layout remain persistent/intentionally spaced.
- Verification completed: `npm run build:icons`, `npm run build`, `npm run format:check`, static search for `alert(`, and `git diff --check`. Automated tests and live route rendering were not run. PHP route-list/browser verification was unavailable because this worktree has no `vendor` directory.
