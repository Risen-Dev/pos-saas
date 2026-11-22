# GrowPOS Frontend Performance Audit

## Scope and evidence

Source-level review of shared app startup, navigation, motion, page polling, media loading, and representative high-traffic controllers. Evidence includes the production Vite build run during this pass and the current Blade/JavaScript source. No deployed browser trace, Lighthouse/field metrics, production PHP timing, database query timing, or slow-network profile was available, so the asset sizes below describe build output only.

The reviewed dashboard, order, product, and report paths eager-load the main relations rendered by their list/detail views. No direct, behavior-preserving N+1 fix was confirmed from this source review.

## Findings and status

### PERF-01 — POS-only logic was loaded on every authenticated page

- **Status:** Fixed in the existing app entry.
- **Evidence:** `grow-pos.js` is dynamically imported only when the document contains the POS root. Current build emits a separate 19.40 kB raw / 6.16 kB gzip POS chunk.
- **Impact:** Non-POS routes avoid downloading the transaction UI code.

### PERF-02 — Unused Axios initialization was in the shared entry

- **Status:** Fixed in the existing app entry.
- **Evidence:** Repository search found no Axios use outside the old global bootstrap; app requests use `fetch`.
- **Impact:** Avoids loading and initializing an unused request dependency.

### PERF-03 — Small table logos were fetched eagerly

- **Status:** Fixed in the existing list views.
- **Evidence:** Product and tenant row images use `loading="lazy"` and `decoding="async"` while keeping their fixed display sizes.
- **Impact:** Offscreen thumbnails do not compete with content at the top of the list.

### PERF-04 — The landing video requested metadata before playback

- **Status:** Fixed in the existing landing page.
- **Evidence:** The demo video uses `preload="none"` and retains its poster and controls.

### PERF-05 — Card reveal motion could hide server-rendered content

- **Status:** Fixed in this pass.
- **Evidence:** CSS hid every `data-motion-state="pending"` card until an IntersectionObserver callback. The app scrolls inside `#main-content`, while the observer previously used the browser viewport as its root.
- **Change:** Pending cards remain visible. The observer now uses the app scroll container, and entrance motion only shifts content without fading it from zero opacity.
- **Impact:** Cards render on the first response even if JavaScript or observation is delayed or unavailable.

### PERF-06 — Page polling could overlap requests or outlive a replaced frame

- **Status:** Fixed in this pass for report export and invoice status pages.
- **Evidence:** Export status used a 3-second `setInterval` and searched the whole document. Invoice status used a 5-second interval; an in-flight response could still trigger a navigation after leaving the invoice frame.
- **Change:** The cleanup registry is initialized synchronously in the shared layout so page scripts on a direct load can register before the deferred app module runs. Polls schedule the next request after the prior request completes, scope export updates to the app frame, abort pending fetches, and stop on frame cleanup.
- **Impact:** Avoids overlapping polling traffic and stale updates to newly rendered content.

### PERF-07 — Persistent shell navigation adds shared JavaScript weight

- **Status:** Accepted tradeoff for the requested behavior.
- **Evidence:** Current build: shared app entry 158.17 kB raw / 47.70 kB gzip, including Turbo; the previous optimized shared entry before Turbo measured 60.28 kB raw / 21.39 kB gzip. The Turbo integration keeps the Blade sidebar and navbar mounted while replacing eligible page content. Chart.js remains a separate lazy chunk at 203.67 kB raw / 69.78 kB gzip and is imported only on the report page.
- **Assessment:** The navigation behavior costs about 26.31 kB additional compressed JavaScript versus the prior entry. Removing Turbo would restore full-document navigation, which conflicts with the user's shell-persistence request. No further reduction was justified without changing navigation behavior or testing an alternative implementation.

## Measurement-dependent follow-up

- `PosController::index` loads the tenant's full active product catalog with discounts, variants, add-ons, and units, then calculates prices for the returned products. Measure catalog response size and POS load time before considering search-backed or incremental loading, because it changes the cashier interaction model.
- `KitchenController::index` loads every queued, preparing, and ready order with its items. Measure kitchen backlog sizes and query time before introducing pagination or limits, because every active ticket must remain visible to staff.
- Dashboard and report controllers run several independent aggregates. Production query timing and tenant data volumes are needed before combining, caching, or changing those calculations.

## Current build output

- App CSS: 99.88 kB raw / 18.47 kB gzip.
- Shared app JavaScript (including Turbo): 158.17 kB raw / 47.70 kB gzip.
- POS-only JavaScript: 19.40 kB raw / 6.16 kB gzip.
- Chart.js auto bundle, loaded on reports only: 203.67 kB raw / 69.78 kB gzip.
- Loyalty card JavaScript: 4.39 kB raw / 2.02 kB gzip.

These values are Vite bundle sizes, not page-level transfer totals or field performance. Browser/runtime, backend, database, and production-network measurements remain outstanding.

## Verification

- `npm run build` completed successfully; Vite reported the existing outdated Browserslist `caniuse-lite` advisory.
- `php artisan view:cache` completed successfully.
- `git diff --check` completed successfully.
- Automated test suites were not run under the active session instruction.
