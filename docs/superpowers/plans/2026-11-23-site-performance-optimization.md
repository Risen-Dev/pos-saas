# Site Performance Optimization Implementation Plan

> **For agentic workers:** Use this plan task-by-task and preserve the user’s existing Blade behavior.

**Goal:** Make GrowPOS content render reliably on first load, keep the authenticated shell mounted during eligible navigation, and remove additional proven frontend costs without changing business behavior.

**Architecture:** Keep Laravel Blade as the server-rendered source of truth and use the Turbo Frames navigation already present in the latest base commit. Harden progressive enhancement and frame lifecycle handling, then make only small app-wide optimizations supported by source/build evidence.

**Tech Stack:** Laravel 13, Blade, Alpine.js, Hotwired Turbo, Vite, Tailwind CSS.

**Spec:** `docs/superpowers/specs/2026-11-23-app-performance-navigation-design.md`

## Global Constraints

- Existing Blade routes, authorization, forms, and role-specific navigation remain the source of truth.
- Server-rendered content stays visible when JavaScript, motion, or an observer is delayed or unavailable.
- Authenticated operational navigation replaces the content region while preserving the shared sidebar and navbar.
- Do not change business rules or perform speculative database/hosting changes without measurements.
- Do not claim deployed performance improvements without deployed measurements.
- Per session instructions, do not add or run automated tests; use production asset build and static diff checks only.

## Review Focus

- Initial or frame-loaded cards when `IntersectionObserver` is absent, delayed, or does not intersect immediately: ensure the content remains visible.
- Turbo frame transitions between routes that have different Alpine data and inline scripts: ensure controls initialize once and stale listeners/timers are cleaned up.
- Direct links, redirects, and frame-missing responses: ensure full-page fallback still reaches the requested destination.
- Download, print, payment, and POS routes: ensure routes requiring document-level behavior retain their full-page path.
- Slow or failed optional chart loading: ensure the numeric report remains usable and no stale frame receives chart events.

---

### Task 1: Make card motion non-blocking

**Files:**
- Modify: `resources/js/motion.js`
- Modify: `resources/css/app.css`

**Interfaces:**
- Consumes: Existing `[data-motion-stagger]` groups and `initPageMotion(root)` lifecycle.
- Produces: Existing cleanup function contract; motion may decorate visible content but cannot hide it while waiting for observer callbacks.

- [ ] Remove hidden pending styling so server-rendered cards are visible before JavaScript and remain visible if observation never fires.
- [ ] Preserve short entrance motion only after an observed intersection, with reduced-motion and missing-observer behavior unchanged or improved.
- [ ] Run `npm run build` and `git diff --check`; confirm no build errors and no whitespace errors.

### Task 2: Harden Turbo frame page lifecycle

**Files:**
- Review and modify as needed: `resources/js/app.js`
- Review and modify as needed: `resources/js/navigation.js`
- Review and modify as needed: `resources/js/dialogs.js`
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `resources/js/app.js`
- Review and modify as needed: frame-loaded pages with page-scoped scripts, starting with `resources/views/reports/index.blade.php`, `resources/views/reports/exports-list.blade.php`, `resources/views/billing/invoice.blade.php`, `resources/views/kitchen/index.blade.php`, `resources/views/discounts/create.blade.php`, `resources/views/settings/index.blade.php`, and `resources/views/products/edit.blade.php`

**Interfaces:**
- Consumes: `<turbo-frame id="app-content">`, `turbo:frame-load`, `window.registerGrowposPageCleanup`, `window.growposPageCleanup`.
- Produces: Page-scoped initialization/cleanup that runs once per frame render; shell-level listeners remain registered once.

- [ ] Trace startup and teardown for dialogs, currency inputs, motion observers, charts, page listeners, and timers across frame navigation.
- [ ] Fix only lifecycle paths that fail on a frame visit or retain work after the frame is replaced.
- [ ] Keep direct document loads and `_top` / `data-turbo="false"` exceptions working.
- [ ] Run `npm run build` and `git diff --check`; confirm no build errors and no whitespace errors.

### Task 3: Complete a bounded source-level performance pass

**Files:**
- Review: shared `resources/js`, `resources/css`, `resources/views/layouts`, page templates, and the highest-traffic dashboard, POS, product, customer, inventory, and report controllers.
- Modify: only files with a specific measured or directly observable low-risk issue.
- Update: `docs/audits/2026-11-23-frontend-performance.md` with completed work and remaining items requiring runtime measurement.

**Interfaces:**
- Consumes: Existing Blade/Alpine/Vite patterns and source audit.
- Produces: No changes to output, authorization, or data semantics; any retained change must have a clear source/build rationale.

- [ ] Review for repeated bundles, eager offscreen media, unnecessary repeated event/poll work, and obvious query repetition/N+1 patterns.
- [ ] Implement only low-risk findings with behavior-preserving changes; leave measurement-dependent database/hosting tuning documented.
- [ ] Run `npm run build` and `git diff --check`; report asset sizes and build output without presenting bundle sizes as field performance.

### Final pass

- [ ] Review the complete diff against the design, especially full-page route exceptions and script cleanup.
- [ ] Do not run automated test suites under the active session constraint.
- [ ] Summarize changed files, build verification, limitations, and any deferred measurement-dependent findings.
