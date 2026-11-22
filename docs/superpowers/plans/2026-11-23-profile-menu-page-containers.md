# Profile Menu and Page Containers Implementation Plan

> **For agentic workers:** Execute this plan task-by-task. Automated tests are omitted per developer instruction unless the user asks for them.

**Goal:** Move utility/account links out of sidebars into the header profile dropdown, give standard authenticated pages consistent content boundaries, and remove avoidable frontend loading cost.

**Architecture:** Extract the Alpine profile dropdown into one shared Blade partial used by owner/admin and cashier headers. Add a reusable Blade page-heading surface for standard app page titles and adjacent actions, then apply it to source-audited views while retaining existing content cards and task-specific POS, auth, marketing, and print layouts. Optimize frontend loading at the entry and media levels: defer POS-only logic to the POS route, remove unused Axios initialization from the shared entry, lazy-load row thumbnails, and avoid preloading video metadata.

**Tech Stack:** Laravel Blade, Alpine.js, Tailwind CSS, local Font Awesome SVG components.

**Spec:** `docs/audits/2026-11-23-page-containment.md`, `docs/audits/2026-11-23-frontend-performance.md`, and the user-provided screenshots.

## Global Constraints

- Sidebars contain operational navigation only; account, help, business settings, connectivity status, and sign-out live in the header profile dropdown.
- The profile trigger is an icon-only native button with an accessible name and visible keyboard focus.
- Dropdown links remain reachable by Tab; Escape closes the disclosure and restores focus to the trigger.
- Standard page headings use one shared surface using existing semantic surface and border tokens.
- Do not add an outer card around content that already has its own card; keep dense tables and task-specific surfaces distinct.
- Preserve specialized POS, authentication, marketing, email, and print document layouts.
- Keep the reports' lazy Chart.js import, high-priority WebP landing hero, visible poster, and font `display=swap` behavior.
- Do not claim field speed gains without deployed measurement.
- Do not add or run automated tests unless the user requests them.

## Review Focus

- Admin and cashier both receive the same profile dropdown, with business settings shown only to admins.
- Sidebar collapse and mobile navigation remain independent of profile menu state.
- Long page descriptions and multiple header actions wrap without horizontal overflow.
- Existing section cards do not become visually nested inside redundant page cards.
- Form, POS, account, login, invoice, and print workflows keep their current task-specific surfaces.

---

### Task 1: Shared account dropdown and operational-only sidebars

**Files:**
- Create `resources/views/layouts/user-menu.blade.php`
- Modify `resources/views/layouts/navbar.blade.php`
- Modify `resources/views/layouts/app.blade.php`
- Modify `resources/views/layouts/sidebar.blade.php`
- Modify `resources/views/layouts/cashier-sidebar.blade.php`

- [x] Extract the profile dropdown into a shared partial with profile identity, role-aware settings, help, account, online/offline status, and logout.
- [x] Use the shared profile icon trigger in both headers with accessible disclosure semantics, focus styling, click-away, and Escape focus restoration.
- [x] Remove the settings/help/account/connectivity group from the business sidebar.
- [x] Remove help/account/logout from the cashier sidebar; keep operational links in place.
- [x] Review permission conditions and route references in source.

### Task 2: Shared page-heading surface

**Files:**
- Create `resources/views/components/page-header.blade.php`
- Modify `resources/css/app.css` only if the reusable component needs a shared class.
- Modify standard authenticated page views listed in the audit report.

- [x] Implement one responsive semantic page-header container with consistent padding, surface, border, radius, and shadow tokens.
- [x] Apply it to standard app index, detail, and configuration views whose title/action block currently floats above the task content.
- [x] Preserve tabs as navigation and do not wrap already-carded page introductions (for example, invoice, profile summary, POS, and first-workspace setup).
- [x] Apply the component to headings in long-form settings/help and forms while retaining each form's existing card.

### Task 3: Page content boundary sweep

**Files:** source-audited application views under `resources/views/`.

- [x] Review each authenticated app page for a functional form, filter, status, or summary block that lacks a visual boundary.
- [x] Add a surface only where the block is visually detached; leave existing table/form/metric cards unchanged.
- [x] Record intentional unboxed surfaces and special page types in the audit report.

### Task 4: Verification

- [x] Run `npm run build` and `npm run format:check`.
- [x] Run `git diff --check` and search for remaining sidebar utility links/status labels and unresolved container gaps from the audit inventory.
- [x] Record that browser viewport review and automated tests were not run if the local Laravel runtime remains unavailable.

### Task 5: Frontend loading costs

**Files:**
- Modify `resources/js/app.js`
- Modify `resources/views/products/index.blade.php`
- Modify `resources/views/tenants/index.blade.php`
- Modify `resources/views/landing.blade.php`

- [x] Dynamically import and register `growPos` only when the current document contains its Alpine root, before Alpine starts.
- [x] Remove the unused Axios bootstrap import from the shared entry.
- [x] Lazy-load and asynchronously decode product/tenant row logos while retaining their fixed CSS dimensions.
- [x] Change the below-fold demo video to `preload="none"` and retain its poster and controls.
- [x] Compare production bundle output before/after and record the effect; run format and diff checks.

## Completion status

All planned source changes are implemented. Production build, icon build, formatting check, and diff whitespace check passed. No automated tests were run, as instructed. Live browser review and deployed performance profiling remain unavailable in this worktree because `.env` and `vendor/autoload.php` are absent.
