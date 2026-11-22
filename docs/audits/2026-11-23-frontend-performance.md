# GrowPOS Frontend Performance Audit

## Scope and evidence

Targeted source audit of the authenticated web app and public landing page, focused on transfer, parse/execute work, media loading, and perceived performance. Evidence comes from the current Blade/JS/CSS source and the most recent Vite production build before this optimization pass. No live Lighthouse run, field data, slow-network profile, or Laravel runtime is available in this worktree, so user-facing speed gains are recommendations until measured in a deployed environment.

## Findings

### PERF-01 — POS-only application logic is in the shared page entry

- **Dimension:** Perceived performance; system feedback
- **Severity:** Medium
- **Confidence:** Observed (source)
- **Location:** `resources/js/app.js:4`; `resources/js/grow-pos.js`; `resources/views/pos/index.blade.php:94`
- **Evidence:** `app.js` statically imports `grow-pos.js` and registers it for every authenticated app route. Source search finds `x-data="growPos"` only on the POS page. The POS module is 33,933 bytes before minification. The most recent production output reports the shared app JS at 129.66 kB raw / 45.27 kB gzip.
- **Why it matters:** Inventory, reports, profile, and other non-POS pages download and parse transaction logic they never use, adding cost to every entry and navigation.
- **Recommendation:** Load and register the POS component only on pages that declare `x-data="growPos"`, keeping the normal app startup synchronous for all other routes.
- **Effort:** S

### PERF-02 — Axios is initialized globally without application use

- **Dimension:** Perceived performance; bundle hygiene
- **Severity:** Low
- **Confidence:** Observed (source)
- **Location:** `resources/js/app.js:1`, `resources/js/bootstrap.js`
- **Evidence:** The shared entry imports `bootstrap.js`, which imports Axios and assigns `window.axios`. Repository-wide source search found no Axios use beyond that bootstrap assignment; existing requests use `fetch`.
- **Why it matters:** The app entry includes a request library and its initialization for pages that do not use it.
- **Recommendation:** Remove the unused bootstrap import from the shared entry. Leave the legacy file and dependency available until their owners are separately removed.
- **Effort:** S

### PERF-03 — Small table logos are eager-loaded with every row

- **Dimension:** Perceived performance; data display
- **Severity:** Low
- **Confidence:** Observed (source)
- **Location:** `resources/views/products/index.blade.php:164`, `resources/views/tenants/index.blade.php:120`
- **Evidence:** Product and tenant logos are rendered at 36×36 CSS pixels inside rows, with no `loading="lazy"` or `decoding="async"` attributes. Their dimensions are reserved by CSS classes.
- **Why it matters:** Lists with many rows can request images below the viewport during initial navigation, competing with visible content.
- **Recommendation:** Lazy-load and asynchronously decode row thumbnails; preserve the existing fixed dimensions so rows do not shift.
- **Effort:** S

### PERF-04 — The landing video requests metadata before the user plays it

- **Dimension:** Perceived performance; media loading
- **Severity:** Low
- **Confidence:** Observed (source)
- **Location:** `resources/views/landing.blade.php:56`
- **Evidence:** The below-hero demo `<video>` uses `preload="metadata"`. It has a poster image, so the user already sees a preview before pressing play.
- **Why it matters:** The browser may request video metadata on landing-page load even when the visitor never opens the demo.
- **Recommendation:** Set video preload to `none`; retain the poster and native controls so playback remains discoverable.
- **Effort:** S

## Existing strengths

- Chart.js is dynamically imported only on the reports screen.
- The landing hero image is WebP and explicitly has `fetchpriority="high"`; lower landing feature images are lazy-loaded.
- App and landing font links already use `display=swap` and preconnect hints.
- Card reveal motion is intersection-based and respects reduced motion, avoiding a continuous scroll animation workload.

## Not observed

- No deployed browser performance trace, Lighthouse score, real-user metrics, PHP response time, database query timing, or slow-network test is available. This audit covers source-level frontend costs only; backend/database and field performance remain unmeasured.

## Implementation and verification

- The shared entry now dynamically imports the POS component only when the POS root exists, and no longer imports the unused Axios bootstrap. Production build output confirms a separate `grow-pos` chunk.
- Product and tenant row logos now use lazy loading and async decoding. The landing demo video now uses `preload="none"` while retaining its poster and controls.
- Production build before the change: shared app JS 129.66 kB raw / 45.27 kB gzip. After the change: shared app JS 60.28 kB / 21.39 kB gzip, plus a POS-only chunk of 19.27 kB / 6.12 kB gzip. This reduces the shared entry by about 53% raw and 53% gzip; the POS route still receives its separately loaded logic.
- `npm run build:icons`, `npm run build`, `npm run format:check`, and `git diff --check` completed successfully. Vite reports the existing Browserslist `caniuse-lite` data is outdated; it does not block the build.
- This is a bundle-level measurement, not a deployed field-performance claim. Browser/Lighthouse profiling and backend/database timing remain unmeasured because this worktree has no Laravel runtime configuration or installed PHP dependencies.
